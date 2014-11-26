<?php
/**
 * @package    Fuel
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

use Fuel\Foundation\Fuel;

/**
 * Set error reporting and display errors settings.
 * You may want to change these when in production.
 */
error_reporting(-1);
ini_set('display_errors', 1);

// Get the start time and memory for use later
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

/**
 * Application document root
 */
define('DOCROOT', __DIR__.DIRECTORY_SEPARATOR);

/**
 * Path to the vendor directory
 */
define('VENDORPATH', realpath(__DIR__.'/../vendor/').DIRECTORY_SEPARATOR);

/**
 * Fire up the Composer autoloader
 */
require VENDORPATH.'autoload.php';

/**
 * Forge the FuelPHP Demo application, and fetch it's main component
 */
$component = Fuel::forge(
	'Demo Application',                                                 // name to idenfity this application
	'Demo',                                                             // namespace that defines the main application component
	isset($_SERVER['FUEL_ENV']) ? $_SERVER['FUEL_ENV'] : 'development'  // default environment for all components
);

/**
 * Using the main application component, we add a few test components manually
 * which are included in the demo applications components directory. Since the
 * are not composer installed, we need to specify the path to them. For composer
 * installed components, the namespace is enough to identify the component.
 */
$component->newComponent('moda', 'Moda', true,  $component->getPath().DS.'components'.DS.'moda'.DS.'classes');
$component->newComponent('modb', 'Modb', false, $component->getPath().DS.'components'.DS.'modb'.DS.'classes');

/**
 * Get the demo application, fire the main request on it, and get the response
 */
try
{
	$response = $component->getRequest()->execute()->getResponse();
}
catch (\Fuel\Foundation\Exception\BadRequest $e)
{
	// check if a 400 route is defined
	if ( ! $route = $component->getRouter()->getRoute('400'))
	{
		// rethrow the BadRequest exception
		throw $e;
	}
}
catch (\Fuel\Foundation\Exception\NotAuthorized $e)
{
	// check if a 401 route is defined
	if ( ! $route = $component->getRouter()->getRoute('401'))
	{
		// rethrow the NotAuthorized exception
		throw $e;
	}
}
catch (\Fuel\Foundation\Exception\Forbidden $e)
{
	// check if a 403 route is defined
	if ( ! $route = $component->getRouter()->getRoute('403'))
	{
		// rethrow the Forbidden exception
		throw $e;
	}
}
catch (\Fuel\Foundation\Exception\NotFound $e)
{
	// check if a 404 route is defined
	if ( ! $route = $component->getRouter()->getRoute('404'))
	{
		// rethrow the NotFound exception
		throw $e;
	}

}
catch (\Fuel\Foundation\Exception\ServerError $e)
{
	// check if a 500 route is defined
	if ( ! $route = $component->getRouter()->getRoute('500'))
	{
		// rethrow the ServerError exception
		throw $e;
	}
}

// check if a new route is defined
if (isset($route))
{
	// call it
	$response = $component->getRequest($route->translation)->execute()->getResponse();
}

/**
 * send the response headers out
 */
if ( ! $component->getApplication()->getEnvironment()->isCli())
{
	$response->sendHeaders();
}

/**
 * Render the output
 */
$response->setContent((string) $response);

/**
 * Output the response body and replace the profiling values. You can remove this
 * if you don't use it, to speed up the output
 */
if (strpos($response, '{exec_time}') !== false or strpos($response, '{mem_usage}') !== false or strpos($response, '{mem_peak_usage}') !== false)
{
	/**
	 * Compile profiling data, add it to the response, and send it on it's way
	 */
	echo str_replace(
		array(
			'{exec_time}',
			'{mem_usage}',
			'{mem_peak_usage}'
		),
		array(
			round(microtime(true)-FUEL_START_TIME, 4),
			round((memory_get_usage()-FUEL_START_MEM)/1024/1024, 4),
			round((memory_get_peak_usage()-FUEL_START_MEM)/1024/1024, 4)
		),
		$response
	);
}
else
{
	/**
	 * Just send out the response
	 */
	echo $response;
}
