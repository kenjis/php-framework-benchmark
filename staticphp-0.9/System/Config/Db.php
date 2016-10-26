<?php

// Database configuration, See PDO documentation for connection string: http://php.net/manual/en/pdo.construct.php for more information

$config['db']['pdo']['default'] = [
    'string' => 'mysql:host=localhost;dbname=',
    'username' => '',
    'password' => '',
    'charset' => 'UTF8',
    'persistent' => true,
    'wrap_column' => '`', // ` - for mysql, " - for postgresql
    'fetch_mode_objects' => false,
    'debug' => $config['debug'],
];
