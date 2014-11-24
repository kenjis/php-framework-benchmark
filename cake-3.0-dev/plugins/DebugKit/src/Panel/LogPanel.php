<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace DebugKit\Panel;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Log\Log;
use DebugKit\DebugPanel;

/**
 * Log Panel - Reads log entries made this request.
 *
 */
class LogPanel extends DebugPanel {

/**
 * Initialize hook - sets up the log listener.
 *
 * @return \LogPanel
 */
	public function initialize() {
		if (Log::config('debug_kit_log_panel')) {
			return;
		}
		Log::config('debug_kit_log_panel', array(
			'engine' => 'DebugKit.DebugKit',
		));
	}

/**
 * Get the panel data
 *
 * @return void
 */
	public function data() {
		return [
			'logger' => Log::engine('debug_kit_log_panel')
		];
	}

/**
 * Get the summary data.
 *
 * @return string
 */
	public function summary() {
		$logger = Log::engine('debug_kit_log_panel');
		if (!$logger) {
			return 0;
		}
		return $logger->count();
	}

}
