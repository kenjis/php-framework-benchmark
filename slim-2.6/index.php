<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/hello/index', function () {
    echo 'Hello World!';
});

$app->run();

echo require dirname(__FILE__).'/../libs/output_data.php';
