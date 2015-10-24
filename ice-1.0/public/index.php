<?php

error_reporting(E_ALL);

try {
    // Load the bootstrap which return the MVC application
    $app = require_once __DIR__ . '/../App/Bootstrap.php';

    // Handle a MVC request and display the HTTP response body
    echo $app->handle();
} catch (Exception $e) {
    // Dispaly the excepton's message
    echo $e->getMessage();
}

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
