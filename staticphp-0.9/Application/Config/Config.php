<?php

/**
 * Default config array
 *
 * (default value: [])
 *
 * @var mixed[]
 * @access public
 */
$config = [];

/*
|--------------------------------------------------------------------------
| General
|--------------------------------------------------------------------------
*/

$config['base_url'] = null; // NULL for auto detect
$config['disable_twig'] = true; // Option to disable twig template engine


/*
|--------------------------------------------------------------------------
| Debug
|--------------------------------------------------------------------------
*/

// Set environment
$config['env'] = (
    (empty($_SERVER['app_env']) || $_SERVER['app_env'] !== 'dev') &&
        php_sapi_name() != 'cli-server' ? 'live' : 'dev'
);
$config['debug']       = ($config['env'] !== 'dev' ? false : true);
$config['debug_ips']   = ['::1', '127.0.0.1'];

/*
| Send errors to this email address.
|
| * Core will only send error emails when debug is turned off.
| * Emails are sent using php's mail function, if you intend to use this feature,
|   make sure your system is configured to be able to send emails.
*/
$config['debug_email'] = null;

/*
|--------------------------------------------------------------------------
| Web server variables
|
| Set where various variables will be taken from
| In most cases these should work by default
|--------------------------------------------------------------------------
*/

$config['request_uri']  = & $_SERVER['REQUEST_URI'];
$config['query_string'] = & $_SERVER['QUERY_STRING'];
$config['script_name']  = & $_SERVER['SCRIPT_NAME'];
$config['client_ip']    = & $_SERVER['REMOTE_ADDR'];

/*
|--------------------------------------------------------------------------
| Uris
|
| URL prefixes can be useful when for example identifying ajax requests,
| they will be stored in config variable and can be checked with Router::hasPrefix('ajax') === true
| they must be in correct sequence. For example, if url is /ajax/test/en/open/29, where ajax and test is prefixes,
| then array should look something like this - ['ajax', 'test']. In this case /test/en/open/29 will also work.
|--------------------------------------------------------------------------
*/
$config['url_prefixes'] = [];

/*
|--------------------------------------------------------------------------
| Autoload
|
| Place filenames without ".php" extension here to autoload various files and classes
| Possible formats: Application/Module/Filename, Module/Filename, Filename (only to load global config)
|--------------------------------------------------------------------------
*/

$config['autoload_configs'] = [];
$config['autoload_helpers'] = ['Defaults/Bootstrap'];

/*
|--------------------------------------------------------------------------
| Hooks
|
| Currently only "before controller" hook is supported and will be called right
| before including controller file. It passes three parametrs as references - $file,
| $class and $method, meaning callback can override current controller.
|--------------------------------------------------------------------------
*/

$config['before_controller'] = [];
