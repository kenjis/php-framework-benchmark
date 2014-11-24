<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\I18n;
use Cake\Log\Log;
use Cake\Routing\DispatcherFactory;

require_once 'vendor/autoload.php';

// Path constants to a few helpful things.
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__) . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . 'vendor' . DS . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', ROOT . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('TESTS', ROOT . 'tests');
define('APP', ROOT . 'tests' . DS . 'test_app' . DS);
define('APP_DIR', 'test_app');
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', APP . 'webroot' . DS);
define('TMP', sys_get_temp_dir() . DS);
define('CONFIG', APP . 'config' . DS);
define('CACHE', TMP);
define('LOGS', TMP);

$loader = new \Cake\Core\ClassLoader;
$loader->register();

$loader->addNamespace('TestApp', APP);
$loader->addNamespace('DebugkitTestPlugin', APP . 'Plugin' . DS . 'TestPlugin' . DS . 'src');

require_once CORE_PATH . 'config/bootstrap.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

Configure::write('debug', true);
Configure::write('App', [
	'namespace' => 'App',
	'encoding' => 'UTF-8',
	'base' => false,
	'baseUrl' => false,
	'dir' => 'src',
	'webroot' => 'webroot',
	'www_root' => APP . 'webroot',
	'fullBaseUrl' => 'http://localhost',
	'imageBaseUrl' => 'img/',
	'jsBaseUrl' => 'js/',
	'cssBaseUrl' => 'css/',
	'paths' => [
		'plugins' => [APP . 'Plugin' . DS],
		'templates' => [APP . 'Template' . DS]
	]
]);
Configure::write('Session', [
	'defaults' => 'php'
]);

Cache::config([
	'_cake_core_' => [
		'engine' => 'File',
		'prefix' => 'cake_core_',
		'serialize' => true
	],
	'_cake_model_' => [
		'engine' => 'File',
		'prefix' => 'cake_model_',
		'serialize' => true
	],
	'default' => [
		'engine' => 'File',
		'prefix' => 'default_',
		'serialize' => true
	]
]);

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
	putenv('db_dsn=sqlite://' . TMP . 'debug_kit_test.sqlite');
}

$config = [
	'url' => getenv('db_dsn'),
	'timezone' => 'UTC',
];

// Use the test connection for 'debug_kit' as well.
ConnectionManager::config('test', $config);
ConnectionManager::config('test_debug_kit', $config);


Log::config([
	'debug' => [
		'engine' => 'Cake\Log\Engine\FileLog',
		'levels' => ['notice', 'info', 'debug'],
		'file' => 'debug',
	],
	'error' => [
		'engine' => 'Cake\Log\Engine\FileLog',
		'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
		'file' => 'error',
	]
]);

Plugin::load('DebugKit', ['path' => ROOT, 'bootstrap' => true]);

DispatcherFactory::add('Routing');
DispatcherFactory::add('ControllerFactory');
