<?php

require __DIR__ . '/../libs/parse_results.php';
require __DIR__ . '/../libs/build_table.php';

$results = parse_results(__DIR__ . '/../output/results.hello_world.log');
//var_dump($results);

echo build_table($results);
