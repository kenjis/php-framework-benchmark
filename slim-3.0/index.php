<?php

require 'vendor/autoload.php';

$app = new \Slim\App();

$app->get('/hello/index', function ($request, $response, $args) {
    return $response->write('Hello World!' . str_repeat('+', 22));
});

ob_start();
$app->run();
$output = ob_get_clean();

ob_start();
// Doesn't work due to Content-Length header
require __DIR__ . '/../libs/output_data.php';
$benchmarkData = ob_get_clean();

echo str_replace('+', ' ', str_replace(str_repeat('+', min(22, strlen($benchmarkData))), $benchmarkData, $output));
