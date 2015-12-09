<?php

$real_usage = null;
// On HHVM, using $real_usage = false ends up always returning 2097152 bytes - https://github.com/facebook/hhvm/issues/2257
if (defined('HHVM_VERSION')) {
    $real_usage = true;
}
// On PHP 7.0, using $real_usage = true ends up always returning 2097152 bytes
if (PHP_MAJOR_VERSION === 7) {
    $real_usage = false;
}
return sprintf(
    "\n%' 8d:%f:%d",
    memory_get_peak_usage($real_usage), // Using $real_usage due to
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
    count(get_included_files()) - 1
);
