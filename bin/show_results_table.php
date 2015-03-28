<?php

require __DIR__ . '/../libs/parse_results.php';

$results = parse_results(__DIR__ . '/../output/results.hello_world.log');
//var_dump($results);

echo '|framework          |requests per second|relative|peak memory|relative|' . "\n";
echo '|-------------------|------------------:|-------:|----------:|-------:|' . "\n";

foreach ($results as $fw => $result) {
    printf(
        "|%-19s|%19s|%8s|%11s|%8s|\n",
        $fw,
        number_format($result['rps'], 2),
        number_format($result['rps_relative'], 1),
        number_format($result['memory'], 2),
        number_format($result['memory_relative'], 1)
    );
}
