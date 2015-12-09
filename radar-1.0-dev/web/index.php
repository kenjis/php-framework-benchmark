<?php
use josegonzalez\Dotenv\Loader as Dotenv;
use Radar\Adr\Boot;
use Relay\Middleware\ExceptionHandler;
use Relay\Middleware\ResponseSender;
use Zend\Diactoros\Response as Response;
use Zend\Diactoros\ServerRequestFactory as ServerRequestFactory;

/**
 * Bootstrapping
 */
require '../vendor/autoload.php';

Dotenv::load([
    'filepath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env',
    'toEnv' => true,
]);

$boot = new Boot();
$adr = $boot->adr();

/**
 * Middleware
 */
$adr->middle(new ResponseSender());
$adr->middle(new ExceptionHandler(new Response()));
$adr->middle('Radar\Adr\Handler\RoutingHandler');
$adr->middle('Radar\Adr\Handler\ActionHandler');

/**
 * Routes
 */
$adr->get(
    'Hello.Subdirectory',
    '/php-framework-benchmark/radar-1.0-dev/web/index.php/hello/index',
     'Domain\Hello\HelloApplicationService'
);
$adr->get(
    'Hello',
    '/radar-1.0-dev/web/index.php/hello/index',
     'Domain\Hello\HelloApplicationService'
);

/**
 * Run
 */
$adr->run(ServerRequestFactory::fromGlobals(), new Response());

echo require dirname(__FILE__).'/../../libs/output_data.php';
