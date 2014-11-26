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

use Fuel\Foundation\Environment;

/**
 * Here you setup your different environments for this application
 * Put all defaults into '__default', and don't do anything global,
 * it will mess up a multiple application environment!
 */
return array(
	/**
	 * Default settings, these are always run first
	 *
	 * @param   Fuel\Foundation\Environment $env
	 * @return  void|\Closure
	 */
	'default' => function(Environment $env)
	{
		// Switch off error display to allow Fuel to handle them
		// ini_set('display_errors', 'Off');

		/**
		 * Localization & internationalization settings
		 */
		$env->locale = null;
		$env->language = 'en';
		$env->timezone = 'UTC';

		/**
		 * Internal string encoding charset
		 */
		$env->encoding = 'UTF-8';

		return function (Environment $env)
		{
			// Include any additional init stuff in here
		};
	},

	/**
	 * Development environment
	 *
	 * @param   Fuel\Foundation\Environment $env
	 * @return  void|\Closure
	 */
	'development' => function(Environment $env)
	{
		$env->locale = 'en_US';
	},

	/**
	 * Production environment
	 *
	 * @param   Fuel\Foundation\Environment $env
	 * @return  void|\Closure
	 */
	'production' => function(Environment $env)
	{
		$env->locale = 'nl_NL';
	},
);
