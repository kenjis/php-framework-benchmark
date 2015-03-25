<?php

use BEAR\Resource\ResourceInterface;
use My\Hello;
use My\Hello\Module\AppModule;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Injector;

error_reporting(E_ALL);

// load
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('My\Hello\\', __DIR__);
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

// set the application path into the globals so we can access it in the tests.
$_ENV['TEST_DIR'] = __DIR__;
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';

// set the resource client
$GLOBALS['RESOURCE'] = (new Injector(new AppModule, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
