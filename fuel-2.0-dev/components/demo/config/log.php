<?php
/**
 * @package    Fuel
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 *
 * @return  array
 */

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Fuel\Foundation\Application;

/**
 * Variables passed:
 * $app - This applications instance
 * $log - The Monolog Logger instance for this application
 *
 * If you return an object that implements Psr\Log\LoggerInterface, it will
 * replace the default Logger instance setup by the application
 */

return array(
	/**
	 * Customize the log handler
	 *
	 * @param   Fuel\Foundation\Application  $app
	 * @param   Monolog\Logger               $log
	 * @return  void|\Closure
	 */
	'customize' => function(Application $app, Logger $log)
	{
		/**
		 * step 1: make sure the log directories and current log file exist
		 */
		try
		{
			// set the paths and filenames
			$path = realpath(__DIR__.DS.'..'.DS.'logs').DS;
			$rootpath = $path.date('Y').DS;
			$filepath = $path.date('Y/m').DS;
			$filename = $filepath.date('d').'.php';

			if ( ! file_exists($filename) or ! filesize($filename))
			{
				// get the required folder permissions
				$permission = $app->getConfig()->get('file.chmod.folders', 0777);

				if ( ! is_dir($rootpath))
				{
					mkdir($rootpath, $permission, true);
					chmod($rootpath, $permission);
				}

				if ( ! is_dir($filepath))
				{
					mkdir($filepath, $permission, true);
					chmod($filepath, $permission);
				}

				$handle = fopen($filename, 'a');

				fwrite($handle, "<?php defined('Fuel::VERSION') or exit('No direct script access allowed'); ?>".PHP_EOL.PHP_EOL);
				chmod($filename, $app->getConfig()->get('file.chmod.files', 0666));

				fclose($handle);
			}
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException('Unable to create or write to the log file. Please check the permissions on '.$path);
		}

		/**
		 * step 2: create the default streamhandler, and activate the handler
		 */

		// determine the log level needed
		$level = $app->Environment() == 'production' ? Logger::ERROR : Logger::DEBUG;

		// define the default streamhandler and formatter, and push them on the log instance
		$stream = new StreamHandler($filename, $level);
		$formatter = new LineFormatter("%level_name% - %datetime% --> %message%".PHP_EOL, "Y-m-d H:i:s");
		$stream->setFormatter($formatter);
		$log->pushHandler($stream);
	},
);
