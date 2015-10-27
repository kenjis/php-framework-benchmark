<?php

require 'vendor/autoload.php';

$app = \Base::instance();

$app->route('GET /index.php/hello/index', function() {
    echo 'Hello World!';
});

$app->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
