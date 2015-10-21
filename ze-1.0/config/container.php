<?php

use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

// Load configuration
$config = require 'config.php';

// Build container
$container = new ServiceManager(new Config($config['dependencies']));

// Inject config
$container->setService('config', $config);

return $container;
