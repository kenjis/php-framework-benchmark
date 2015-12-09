<?php

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
        
        $min_rps    = min($min_rps, $rps);
        $min_memory = min($min_memory, $memory);
        $min_time   = min($min_time, $time);
        $min_file   = min($min_file, $file);
        
        $results[$fw] = [
            'rps'    => $rps,
            'memory' => round($memory, 2),
            'time'   => $time,
            'file'   => $file,
        ];
    }

    $frameworks = [
        "no-framework",
        //"phalcon-1.3",
        "phalcon-2.0",
        "ice-1.0",
        "tipsy-0.10",
        "fatfree-3.5",
        "slim-2.6",
        "ci-3.0",
        "nofuss-1.2",
        "slim-3.0",
        "bear-1.0",
        "lumen-5.1",
        "ze-1.0",
        "radar-1.0-dev",
        "yii-2.0",
        //"lumen-5.0",
        //"silex-1.2",
        "silex-1.3",
        "cygnite-1.3",
        //"fuel-1.8-dev",
        "fuel-2.0-dev",
        "phpixie-3.2",
        //"cake-3.0",
        "aura-2.0",
        "cake-3.1",
        "bear-0.10",
        //"symfony-2.5",
        //"symfony-2.6",
        "symfony-2.7",
        //"laravel-4.2",
        //"laravel-5.0",
        "laravel-5.1",
        //"zf-2.4",
        "zf-2.5",
        //"typo3f-2.3",
        "typo3f-3.0",
    ];

    $ordered_results = [];
    foreach ($frameworks as $fw) {
        if (isset($results[$fw])) {
            $data = $results[$fw];
            $ordered_results[$fw] = $data;
            $results[$fw]['rps_relative']    = $data['rps'] / $min_rps;
            $results[$fw]['memory_relative'] = $data['memory'] / $min_memory;
            $results[$fw]['time_relative'] = $data['time'] / $min_time;
            $results[$fw]['file_relative'] = $data['file'] / $min_file;
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
