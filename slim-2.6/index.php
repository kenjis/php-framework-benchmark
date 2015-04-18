<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/hello/index', function () {
    echo 'Hello World!';
});

$app->run();

printf(
    "\n%' 8d:%f",
    memory_get_peak_usage(true),
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
);
