<?php

function recalc_relative($results)
{
    $min_rps    = INF;
    $min_memory = INF;
    $min_time   = INF;
    
    foreach ($results as $fw) {
        $min_rps    = min($min_rps,    $fw['rps']);
        $min_memory = min($min_memory, $fw['memory']);
        $min_time   = min($min_time,   $fw['time']);
    }
    
    foreach ($results as $fw => $data) {
        $results[$fw]['rps_relative']    = $data['rps']    / $min_rps;
        $results[$fw]['memory_relative'] = $data['memory'] / $min_memory;
        $results[$fw]['time_relative']   = $data['time']   / $min_time;
    }
    
    return $results;
}
