<?php

error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors', true);

require_once __DIR__ . '/../vendor/autoload.php';

$t = new \Tipsy\Tipsy;

$t->router()
	->when('hello/index', function() {
		//sleep(1);
		echo 'Hello World!';
	});

$t->start();

require getenv('php_framework_benchmark_path').'/libs/output_data.php';
