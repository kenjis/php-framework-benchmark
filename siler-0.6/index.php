<?php

require __DIR__.'/vendor/autoload.php';

Siler\Route\get('/hello/index', './pages/hello.php');

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
