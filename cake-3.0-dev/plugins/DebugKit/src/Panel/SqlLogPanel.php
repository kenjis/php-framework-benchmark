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
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use DebugKit\Database\Log\DebugLog;
use DebugKit\DebugPanel;

/**
 * Provides debug information on the SQL logs and provides links to an ajax explain interface.
 *
 */
class SqlLogPanel extends DebugPanel {

/**
 * Loggers connected
 *
 * @var array
 */
	protected $_loggers = [];

/**
 * Initialize hook - configures logger.
 *
 * This will unfortunately build all the connections, but they
 * won't connect until used.
 *
 * @return array
 */
	public function initialize() {
		$configs = ConnectionManager::configured();
		foreach ($configs as $name) {
			$connection = ConnectionManager::get($name);
			if ($connection->configName() === 'debug_kit') {
				continue;
			}
			$logger = null;
			if ($connection->logQueries()) {
				$logger = $connection->logger();
			}

			if ($logger instanceof DebugLog) {
				continue;
			}
			$logger = new DebugLog($logger, $name);

			$connection->logQueries(true);
			$connection->logger($logger);
			$this->_loggers[] = $logger;
		}
	}

/**
 * Get the data this panel wants to store.
 *
 * @return array
 */
	public function data() {
		return [
			'tables' => array_map(function ($table) {
				return $table->alias();
			}, TableRegistry::genericInstances()),
			'loggers' => $this->_loggers,
		];
	}

/**
 * Get summary data from the queries run.
 *
 * @return string
 */
	public function summary() {
		$count = $time = 0;
		foreach ($this->_loggers as $logger) {
			$count += count($logger->queries());
			$time += $logger->totalTime();
		}
		return "{$count} - $time ms";
	}
}
