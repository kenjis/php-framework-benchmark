<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Adhoc fix for benchmarking
// Remove sub directories from URI
$_SERVER['REQUEST_URI'] = preg_replace(
    ['!/php-framework-benchmark/zf-2.5/public/index.php!', '!/zf-2.5/public/index.php!'],
    '',
    $_SERVER['REQUEST_URI']
);

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();

require $_SERVER['DOCUMENT_ROOT'].'/php-framework-benchmark/libs/output_data.php';
