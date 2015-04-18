<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->get('/hello/index', 'App\\Controller\\Hello::index');

$app->run();

printf("\n%' 8d", memory_get_peak_usage(true));
