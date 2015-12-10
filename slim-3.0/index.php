<?php

require 'vendor/autoload.php';

$app = new \Slim\App();

$app->get('/hello/index', function ($request, $response, $args) {
    return $response->write('Hello World!');
});

$app->run();

require getenv('php_framework_benchmark_path').'/libs/output_data.php';
