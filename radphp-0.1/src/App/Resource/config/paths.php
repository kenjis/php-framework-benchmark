<?php

if (!defined('DS')) {
    // Define shorter directory separator
    define('DS', DIRECTORY_SEPARATOR);
}

// Define root path
define('ROOT_DIR', dirname(dirname(dirname(dirname(__DIR__)))));

// Define bundles path
define('SRC', ROOT_DIR . DS . 'src');

// Define app path
define('APP_DIR', dirname(dirname(__DIR__)));

// Define config path
define('CONFIG_DIR', APP_DIR . DS . 'Resource' . DS . 'config');

// Define config path
define('VAR_DIR', ROOT_DIR . DS . 'var');

// Define log path
define('LOG_DIR', VAR_DIR . DS . 'log');

// Define tmp path
define('TMP_DIR', VAR_DIR . DS . 'tmp');

// Define cache path
define('CACHE_DIR', VAR_DIR . DS . 'cache');
