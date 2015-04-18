<?php

function parse_results($file)
{
    $lines = file($file);
    
    $results = [];
    $min_rps    = INF;
    $min_memory = INF;
    $min_time   = INF;
    
    foreach ($lines as $line) {
        $column = explode(':', $line);
        $fw = $column[0];
        $rps    = (float) trim($column[1]);
        $memory = (float) trim($column[2])/1024/1024;
        $time   = (float) trim($column[3])*1000;
        
        $min_rps    = min($min_rps, $rps);
        $min_memory = min($min_memory, $memory);
        $min_time   = min($min_time, $time);
        
        $results[$fw] = [
            'rps'    => $rps,
            'memory' => $memory,
            'time'   => $time,
        ];
    }
    
    foreach ($results as $fw => $data) {
        $results[$fw]['rps_relative']    = $data['rps'] / $min_rps;
        $results[$fw]['memory_relative'] = $data['memory'] / $min_memory;
        $results[$fw]['time_relative'] = $data['time'] / $min_time;
    }
//    var_dump($results);
    
    return $results;
}
