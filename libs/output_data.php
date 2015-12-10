<?php

$real_usage = null;
// On HHVM, using $real_usage = false ends up always returning 2097152 bytes - https://github.com/facebook/hhvm/issues/2257
if (defined('HHVM_VERSION')) {
    $real_usage = true;
}
return sprintf(
    "\n%' 8d:%f:%d",
    memory_get_peak_usage($real_usage), // Using $real_usage due to
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
    count(get_included_files()) - 1
);
