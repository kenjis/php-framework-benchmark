<?php

use Chubbyphp\Framework\Application;
use Chubbyphp\Framework\ErrorHandler;
use Chubbyphp\Framework\ExceptionHandler;
use Chubbyphp\Framework\Middleware\MiddlewareDispatcher;
use Chubbyphp\Framework\RequestHandler\CallbackRequestHandler;
use Chubbyphp\Framework\Router\FastRouteRouter;
use Chubbyphp\Framework\Router\Route;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

$_SERVER['REQUEST_URI'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);

require 'vendor/autoload.php';

set_error_handler([ErrorHandler::class, 'handle']);

$responseFactory = new ResponseFactory();

$route = Route::get('/hello/index', 'hello', new CallbackRequestHandler(
    function () use ($responseFactory) {
        $response = $responseFactory->createResponse();
        $response->getBody()->write('Hello World!');

        return $response;
    }
));

$app = new Application(
    new FastRouteRouter([$route]),
    new MiddlewareDispatcher(),
    new ExceptionHandler($responseFactory, true)
);

$app->send($app->handle(ServerRequestFactory::createFromGlobals()));

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
