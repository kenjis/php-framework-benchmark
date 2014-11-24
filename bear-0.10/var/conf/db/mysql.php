<?php

$mysql = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'dbname' => 'sandbox',
    'charset' => 'UTF8'
];
$master = [
    'user' => $_SERVER['APP_MASTER_ID'],
    'password' => $_SERVER['APP_MASTER_PASSWORD']
];
$slave = [
    'user' => $_SERVER['APP_SLAVE_ID'],
    'password' => $_SERVER['APPs_SLAVE_PASSWORD']
];

return [
    $mysql + $master,
    $mysql + $slave
];
