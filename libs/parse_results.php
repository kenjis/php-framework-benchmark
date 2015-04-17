<?php

function parse_results($file)
{
    $lines = file($file);
    
    $results = [];
    $min_rps    = 1000000;
    $min_memory = 1000000;
    
    foreach ($lines as $line) {
        $column = explode(':', $line);
        $fw = $column[0];
        $rps    = (float) trim($column[1]);
        $memory = (float) trim($column[2])/1024/1024;
        
        $min_rps    = min($min_rps, $rps);
        $min_memory = min($min_memory, $memory);
        
        $results[$fw] = [
            'rps'    => $rps,
            'memory' => $memory,
        ];
    }
    
    foreach ($results as $fw => $data) {
        $results[$fw]['rps_relative']    = $data['rps'] / $min_rps;
        $results[$fw]['memory_relative'] = $data['memory'] / $min_memory;
    }
//    var_dump($results);
    
    return $results;
}
