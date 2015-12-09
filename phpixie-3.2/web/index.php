<?php

require_once(__DIR__.'/../vendor/autoload.php');

$framework = new Project\Framework();
$framework->registerDebugHandlers();
$framework->processHttpSapiRequest();

echo require dirname(__FILE__).'/../../libs/output_data.php';
