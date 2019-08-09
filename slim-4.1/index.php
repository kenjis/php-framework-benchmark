<?php

use Slim\Factory\AppFactory;

$_SERVER['REQUEST_URI'] = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);

require 'vendor/autoload.php';

$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$app->get('/hello/index', function ($request, $response, $args) {
    $response->getBody()->write('Hello World!');

    return $response;
});

$app->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
