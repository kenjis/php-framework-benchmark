<?php

// Define path to application directory
defined('APPLICATION_PATH')
|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

// Ensure library/ is on include_path
//set_include_path(implode(PATH_SEPARATOR, array(
//    realpath(APPLICATION_PATH . '/../library'),
//    get_include_path(),
//)));

set_include_path(implode(PATH_SEPARATOR, [
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../vendor/zendframework/zendframework1/library')
]));

require_once realpath(APPLICATION_PATH . '/../vendor/autoload.php');

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
