<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->get('/hello/index', 'App\\Controller\\Hello::index');

$app->run();

echo require dirname(__FILE__).'/../../libs/output_data.php';
