<?php

require 'vendor/autoload.php';

Siler\Route\get('/hello/index', function () {
    echo 'Hello World!';
})

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
