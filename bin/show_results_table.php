<?php

require __DIR__ . '/../libs/parse_results.php';

$results = parse_results(__DIR__ . '/../output/results.hello_world.log');
//var_dump($results);

echo '|framework          |requests per second|peak memory|' . "\n";
echo '|-------------------|------------------:|----------:|' . "\n";

foreach ($results as $fw => $result) {
    printf("|%-19s|%19s|%11s|\n", $fw, $result['rps'], $result['memory']);
}
