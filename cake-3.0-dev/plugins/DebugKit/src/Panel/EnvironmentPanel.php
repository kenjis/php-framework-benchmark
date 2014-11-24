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
 *
 */
namespace DebugKit\Panel;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use DebugKit\DebugPanel;

/**
 * Provides information about your PHP and CakePHP environment to assist with debugging.
 *
 */
class EnvironmentPanel extends DebugPanel {

/**
 * Get necessary data about environment to pass back to controller
 *
 * @param \Cake\Controller\Controller $controller The controller.
 * @return array
 */
	protected function _prepare(Controller $controller) {
		$return = [];

		// PHP Data
		$phpVer = phpversion();
		$return['php'] = array_merge(
			['PHP_VERSION' => $phpVer],
			$_SERVER
		);
		unset($return['php']['argv']);

		// CakePHP Data
		$return['cake'] = array(
			'APP' => APP,
			'APP_DIR' => APP_DIR,
			'CACHE' => CACHE,
			'CAKE' => CAKE,
			'CAKE_CORE_INCLUDE_PATH' => CAKE_CORE_INCLUDE_PATH,
			'CORE_PATH' => CORE_PATH,
			'CAKE_VERSION' => Configure::version(),
			'DS' => DS,
			'LOGS' => LOGS,
			'ROOT' => ROOT,
			'TESTS' => TESTS,
			'TMP' => TMP,
			'WWW_ROOT' => WWW_ROOT
		);

		$cakeConstants = array_fill_keys(
			array(
				'DS', 'ROOT', 'TIME_START', 'SECOND', 'MINUTE', 'HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR',
			), ''
		);
		$var = get_defined_constants(true);
		$return['app'] = array_diff_key($var['user'], $return['cake'], $cakeConstants);

		if (isset($var['hidef'])) {
			$return['hidef'] = $var['hidef'];
		}

		return $return;
	}

/**
 * Shutdown callback
 *
 * @param \Cake\Event\Event $event Event
 * @return void
 */
	public function shutdown(Event $event) {
		$this->_data = $this->_prepare($event->subject());
	}

}
