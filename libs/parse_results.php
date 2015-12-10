<?php

require( dirname(__FILE__) . '/../list.php');

function parse_results($file)
{
    $lines = file($file);
    
    $results = [];
    $min_rps    = INF;
    $min_memory = INF;
    $min_time   = INF;
    $min_file   = INF;
    
    foreach ($lines as $line) {
        $column = explode(':', $line);
        $fw = $column[0];
        $rps    = (float) trim($column[1]);
        $memory = (float) trim($column[2])/1024/1024;
        $time   = (float) trim($column[3])*1000;
        $file   = (int) trim($column[4]);
        
        $min_rps    = $rps > 0 ? min($min_rps, $rps) : $min_rps;
        $min_memory = $memory > 0 ? min($min_memory, $memory) : $min_memory;
        $min_time   = $time > 0 ? min($min_time, $time) : $min_time;
        $min_file   = $file > 0 ? min($min_file, $file) : $min_file;
        
        $results[$fw] = [
            'rps'    => $rps,
            'memory' => round($memory, 2),
            'time'   => $time,
            'file'   => $file,
        ];
    }

    $frameworks = frameworks();

    $ordered_results = [];
    foreach ($frameworks as $fw) {
        if (isset($results[$fw])) {
            $data = $results[$fw];
            $ordered_results[$fw] = $data;
            $ordered_results[$fw]['rps_relative']    = $data['rps'] / $min_rps;
            $ordered_results[$fw]['memory_relative'] = $data['memory'] / $min_memory;
            $ordered_results[$fw]['time_relative'] = $data['time'] / $min_time;
            $ordered_results[$fw]['file_relative'] = $data['file'] / $min_file;
        } else {
            $ordered_results[$fw] = [
                'rps' => 0,
                'memory' => 0,
                'time' => 0,
                'file' => 0,
                'rps_relative' => 0,
                'memory_relative' => 0,
                'time_relative' => 0,
                'file_relative' => 0,
            ];
        }
    }
//    var_dump($ordered_results);
    
    return $ordered_results;
}
