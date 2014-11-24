<?php

function parse_results($file)
{
    $lines = file($file);
    $results = [];
    foreach ($lines as $line) {
        $column = explode(':', $line);
        $results[$column[0]] = [
            'rps' => (float) trim($column[1]),
            'memory' => (float) trim($column[2]),
        ];
    }
    //var_dump($results);
    return $results;
}
