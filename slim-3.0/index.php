<?php

require 'vendor/autoload.php';

$app = new \Slim\App();

$app->get('/hello/index', function ($request, $response, $args) {
    $response = $response->write('Hello World! sdfsdf');
    $output_data = require dirname(__FILE__).'/../libs/output_data.php';
    $response .= $response->write($output_data);
    return $response;
});

$app->run();
