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

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use DebugKit\DebugPanel;

/**
 * Provides debug information on previous requests.
 *
 */
class HistoryPanel extends DebugPanel {

/**
 * Get the data for the panel.
 *
 * @return array
 */
	public function data() {
		$table = TableRegistry::get('DebugKit.Requests');
		$recent = $table->find('recent');
		return [
			'requests' => $recent->toArray(),
		];
	}
}
