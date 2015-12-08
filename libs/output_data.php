<?php

printf(
    "\n%' 8d:%f:%d",
    memory_get_peak_usage(),
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
    count(get_included_files()) - 1
);
