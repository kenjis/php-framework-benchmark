<?php

use BEAR\Package\Dev\Dev;
use Ray\Di\Injector;
use My\Hello\Module\AppModule;

error_reporting(E_ALL);
ini_set('xdebug.max_nesting_level', 300);

// load
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('My\Hello\\', __DIR__);

// set the application path into the globals so we can access it in the tests.
$_ENV['APP_DIR'] = dirname(__DIR__);

// clear cache files
require $_ENV['APP_DIR'] . '/bin/clear.php';

// set the resource client
$GLOBALS['RESOURCE'] = Injector::create([new AppModule('test')])->getInstance('\BEAR\Resource\ResourceInterface');
