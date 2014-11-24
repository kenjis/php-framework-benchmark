<?php
/**
 * Application clear script
 *
 */

use BEAR\Package\Bootstrap\Bootstrap;

require dirname(__DIR__) . '/bootstrap/autoload.php';

$clearDirs = [
    dirname(__DIR__) . '/var/tmp'
];

Bootstrap::clearApp($clearDirs);

