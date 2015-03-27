<?php

require 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/', function () {
    echo 'Hello World!';
});

$app->run();

echo "\n" . (memory_get_peak_usage(true)/1024/1024);
