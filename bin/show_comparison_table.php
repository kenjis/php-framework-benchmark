<?php

require __DIR__ . '/../libs/parse_results.php';
require __DIR__ . '/../libs/build_table.php';
require __DIR__ . '/../libs/recalc_relative.php';

$stack = getenv('stack') ? getenv('stack') : 'local';
$output_dir = __DIR__ . '/../output/' . $stack;

$list = [
    'cake-3.0',
    'symfony-2.6',
    'zf-2.4',
];

system('git checkout master');
$results_master = parse_results($output_dir . '/results.hello_world.log');
system('git checkout optimize');
$results_optimize = parse_results($output_dir . '/results.hello_world.log');
//var_dump($results_master, $results_optimize);

$is_fisrt = true;
foreach ($list as $fw) {
    $results = [];
    $results[$fw] = $results_master[$fw];
    $results[$fw . ' (*)'] = $results_optimize[$fw];
    
    $results = recalc_relative($results);
    echo build_table($results, $is_fisrt);
    $is_fisrt = false;
}
