<?php

namespace My\Hello;

use BEAR\Package\Bootstrap\Bootstrap;

$appDir = dirname(__DIR__);
$packageDir = dirname(dirname(dirname(__DIR__)));
$baseDir = file_exists($appDir . '/vendor/autoload.php') ? $appDir : $packageDir;
$loader = require $baseDir . '/vendor/autoload.php';

Bootstrap::registerLoader(
    $loader,
    __NAMESPACE__,
    dirname(__DIR__)
);
