<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Shell;

use Boris\Boris;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Log\Log;

/**
 * Simple console wrapper around Boris.
 */
class ConsoleShell extends Shell {

/**
 * Start the shell and interactive console.
 *
 * @return void
 */
	public function main() {
		if (!class_exists('Boris\Boris')) {
			$this->err('<error>Unable to load Boris\Boris.</error>');
			$this->err('');
			$this->err('Make sure you have installed boris as a dependency,');
			$this->err('and that Boris\Boris is registered in your autoloader.');
			$this->err('');
			$this->err('If you are using composer run');
			$this->err('');
			$this->err('<info>$ php composer.phar require d11wtq/boris</info>');
			$this->err('');
			return 1;
		}
		if (!function_exists('pcntl_signal')) {
			$this->err('<error>No process control functions.</error>');
			$this->err('');
			$this->err('You are missing the pcntl extension, the interactive console requires this extension.');
			return 2;
		}
		$this->out('You can exit with <info>CTRL-D</info>');

		Log::drop('debug');
		Log::drop('error');
		$this->_io->setLoggers(false);
		restore_error_handler();
		restore_exception_handler();

		$boris = new Boris('app > ');
		$boris->start();
	}

/**
 * Display help for this console.
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = new ConsoleOptionParser('console', false);
		$parser->description(
			'This shell provides a REPL that you can use to interact ' .
			'with your application in an interactive fashion. You can use ' .
			'it to run adhoc queries with your models, or experiment ' .
			'and explore the features of CakePHP and your application.' .
			"\n\n" .
			'You will need to have boris installed for this Shell to work. ' .
			'Boris is known to not work well on windows due to dependencies on ' .
			'readline and posix.'
		);
		return $parser;
	}

}
