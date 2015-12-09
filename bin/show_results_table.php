<?php

require __DIR__ . '/../libs/parse_results.php';
require __DIR__ . '/../libs/build_table.php';

$stack = getenv('stack') ? getenv('stack') : 'local';
$output_dir = __DIR__ . '/../output/' . $stack;

$results = parse_results($output_dir . '/results.hello_world.log');
//var_dump($results);

echo build_table($results);
