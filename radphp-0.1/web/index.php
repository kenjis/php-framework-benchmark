<?php

use Rad\Application;

require __DIR__ . '/../vendor/autoload.php';
require dirname(__DIR__) . '/src/App/Resource/config/paths.php';

Application::getInstance()->runWeb();
