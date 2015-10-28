<?php

printf(
    "\n%' 8d:%f:%d",
    memory_get_peak_usage(true),
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
    count(get_included_files())
);
