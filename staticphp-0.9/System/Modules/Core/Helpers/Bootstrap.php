<?php

use \Core\Models\Load;
use \Core\Models\Router;
use \Core\Models\RouterException;


// Set microtime
$microtime = microtime(true);

// Re-Define DS as DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);

// Load all core clases
require SYS_PATH.'Modules/Core/Models/Load.php'; // Load


// Load default config file and routing
Load::config(['Config', 'Routing']);

// Set debug
Load::$config['debug'] = (Load::$config['debug'] || in_array(Load::$config['client_ip'], (array) Load::$config['debug_ips']));
ini_set('error_reporting', (!empty(Load::$config['debug']) ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT));
ini_set('display_errors', (int) Load::$config['debug']);

// Autoload additional config files
if (!empty(Load::$config['autoload_configs'])) {
    foreach (Load::$config['autoload_configs'] as $item) {
        $tmp = explode('/', $item);
        $count = count($tmp);
        if ($count == 3) {
            Load::config($tmp[2], $tmp[1], $tmp[0]);
        } elseif ($count == 2) {
            Load::config($tmp[1], $tmp[0]);
        } elseif ($count == 1) {
            Load::config($tmp[0]);
        }
    }
}

/**
 * StaticPHP's error handler. Turns errors into exceptions and passes on to sp_exception_handler().
 *
 * Stops on @ suppressed errors.
 *
 * @see sp_exception_handler()
 * @access public
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @return bool Returns whether the error was handled or not.
 */
function sp_error_handler($errno, $errstr, $errfile, $errline)
{
    if (error_reporting() === 0) {
        return false;
    }

    $e = new ErrorException($errstr, 0, $errno, $errfile, $errline);
    sp_exception_handler($e);

    return true;
}

/**
 * StaticPHP's script shutdown handler to find out whether shutdown was because of any fatal error.
 *
 * If the shutdown was caused by an error, the error is passed on to the sp_exception_handler().
 *
 * @see sp_exception_handler()
 * @access public
 * @return void
 */
function sp_error_shutdown_handler()
{
    $last_error = error_get_last();

    if ($last_error['type'] === E_ERROR || $last_error['type'] === E_PARSE) {
        $e = new ErrorException($last_error['message'], 0, 0, $last_error['file'], $last_error['line']);
        sp_exception_handler($e);
    }
}

/**
 * StaticPHP's exception handler.
 *
 * If debug mode is on, sends formatted error to browser, otherwise sends error email, if debug email is provided in <i>Config/Config.php</i> file.
 *
 * @access public
 * @param Exception|ErrorException|mixed $exception
 * @return void
 */
function sp_exception_handler($exception)
{
    if ($exception instanceof RouterException) {
        if (!empty(Load::$config['debug'])) {
            Router::error('500', 'Internal Server Error', $exception->getMessage());
        }
        else {
            Router::error('404', 'Not Found');
        }
    }

    if (function_exists('http_response_code') && headers_sent() === false) {
        http_response_code(500);
    }

    if (!empty(Load::$config['debug'])) {
        echo sp_format_exception($exception);
    } else {
        sp_send_error_email($exception);
    }
}

/**
 * Sends error messages.
 *
 * @see sp_format_exception()
 * @access public
 * @param Exception|ErrorException|mixed $e
 * @return void
 */
function sp_send_error_email($e)
{
    if (!empty(Load::$config['debug_email'])) {
        mail(Load::$config['debug_email'], 'PHP ERROR: "'.$_SERVER['HTTP_HOST'].'"', sp_format_exception($e, true), "Content-Type: text/html; charset=utf-8");
    }
}

/**
 * Format exception and add session, server and post information for easier debugging.
 *
 * If $full is set to false, only string containing formatted message is returned.
 *
 * @access public
 * @param Exception|ErrorException|mixed $e
 * @param bool $full (default: false)
 * @return string Returns formatted string of the $e exception
 */
function sp_format_exception($e, $full = false)
{
    $session = (isset($_SESSION) ? $_SESSION : []);
    $post = $_POST;

    $message = str_replace("\n", "<br />", $e->getMessage());
    $message .= '<br /><br /><strong>Trace:</strong><br /><table border="0" cellspacing="0" cellpadding="5" style="border: 1px #DADADA solid;"><tr><td style="border-bottom: 1px #DADADA solid;">';
    $message .= str_replace("\n", '</td></tr><tr><td style="border-bottom: 1px #DADADA solid;">', $e->getTraceAsString()).'</td></tr></table>';

    $session = str_replace(array(" ", "\n"), array('&nbsp;', '<br />'), json_encode($session, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null)));
    $server = str_replace(array(" ", "\n"), array('&nbsp;', '<br />'), json_encode($_SERVER, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null)));
    $post = str_replace(array(" ", "\n"), array('&nbsp;', '<br />'), json_encode($post, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null)));

    if (!empty($full)) {
        return "<strong>Error:</strong><br />{$message}<br /><br /><strong>Sesssion Info:</strong><br />{$session}<br /><br /><strong>Post Info:</strong><br />{$post}<br /><br /><strong>Server:</strong><br />{$server}";
    } else {
        return "<pre><strong>Error:</strong><br />{$message}<br /></pre>";
    }
}

// Register error handlers
set_error_handler('sp_error_handler', (!empty(Load::$config['debug']) ? E_ALL : E_ALL & ~E_DEPRECATED & ~E_STRICT));
set_exception_handler('sp_exception_handler');
register_shutdown_function('sp_error_shutdown_handler');

// Load twig
if (empty(Load::$config['disable_twig'])) {
    if (is_file(BASE_PATH.'Vendor/twig/twig/lib/Twig/Autoloader.php') !== true) {
        throw new Exception('Twig Not Found! If you installed StaticPHP manually, not using composer, please see README.md to where to place the twig library.');
    }

    require BASE_PATH.'Vendor/twig/twig/lib/Twig/Autoloader.php';
    Twig_Autoloader::register();

    Load::$config['view_loader'] = new Twig_Loader_Filesystem([APP_MODULES_PATH, SYS_MODULES_PATH.'Core/Views']);
    Load::$config['view_engine'] = new Twig_Environment(Load::$config['view_loader'], array(
        'cache' => APP_PATH.'Cache/Views/',
        'debug' => Load::$config['debug'],
    ));

    // Register default filters and functions
    // Site url filter
    $filter = new Twig_SimpleFilter('siteUrl', function ($url = null, $prefix = null, $current_prefix = true) {
        return Router::siteUrl($url, $prefix, $current_prefix);
    });
    Load::$config['view_engine']->addFilter($filter);

    // Site url function
    $function = new Twig_SimpleFunction('siteUrl', function ($url = null, $prefix = null, $current_prefix = true) {
        return Router::siteUrl($url, $prefix, $current_prefix);
    });
    Load::$config['view_engine']->addFunction($function);

    // Start timer function
    $function = new Twig_SimpleFunction('startTimer', function () {
        Load::startTimer();
    });
    Load::$config['view_engine']->addFunction($function);

    // Stop timer function
    $function = new Twig_SimpleFunction('stopTimer', function ($name) {
        Load::stopTimer($name);
    });
    Load::$config['view_engine']->addFunction($function);

    // Mark time function
    $function = new Twig_SimpleFunction('markTime', function ($name) {
        Load::markTime($name);
    });
    Load::$config['view_engine']->addFunction($function);

    // Debug output function
    $function = new Twig_SimpleFunction('debugOutput', function () {
        return Load::debugOutput();
    });
    Load::$config['view_engine']->addFunction($function);
}

// Autoload helpers
if (!empty(Load::$config['autoload_helpers'])) {
    foreach (Load::$config['autoload_helpers'] as $item) {
        $tmp = explode('/', $item);
        $count = count($tmp);
        if ($count == 3) {
            Load::helper($tmp[2], $tmp[1], $tmp[0]);
        } elseif ($count == 2) {
            Load::helper($tmp[1], $tmp[0]);
        }
    }
}

// Init router
Router::init();
