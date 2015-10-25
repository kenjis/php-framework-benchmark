<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/hello/index', function () {
    echo 'Hello World!';
});

$app->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
