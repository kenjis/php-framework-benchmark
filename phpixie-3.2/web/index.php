<?php

require_once(__DIR__.'/../vendor/autoload.php');

$framework = new Project\Framework();
$framework->registerDebugHandlers();
$framework->processHttpSapiRequest();

printf(
    "\n%' 8d:%f",
    memory_get_peak_usage(true),
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
);
