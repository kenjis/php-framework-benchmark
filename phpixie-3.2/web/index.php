<?php

require_once(__DIR__.'/../vendor/autoload.php');

$framework = new Project\Framework();
$framework->registerDebugHandlers();
$framework->processHttpSapiRequest();

require getenv('php_framework_benchmark_path').'/libs/output_data.php';
