<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->get('/', function() {
    return 'Hello World!';
});

$app->run();

echo "\n" . (memory_get_peak_usage(true)/1024/1024);
