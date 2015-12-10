<?php

// Get benchmark output data
$real_usage = null;
if (defined('HHVM_VERSION')) {
    // On HHVM, using $real_usage = false ends up always returning 2097152 bytes - https://github.com/facebook/hhvm/issues/2257
    $real_usage = true;
}
$results = sprintf(
    "%' 8d:%f:%d",
    memory_get_peak_usage($real_usage), // Using $real_usage due to
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
    count(get_included_files()) - 1
);

// Respond benchmark output data in header
header('X-Benchmark-Output-Data: ' . $results);
