<?php

global $appDir;

$sqlite = [
    'driver' => 'pdo_sqlite',
    'path' =>  $appDir . '/var/db/posts.sq3'
];

return $sqlite;
