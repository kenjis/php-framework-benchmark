<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/hello/index', function () {
    echo 'Hello World!';
});

$app->run();

require getenv('php_framework_benchmark_path').'/libs/output_data.php';
