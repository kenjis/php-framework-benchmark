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
use DebugKit\DebugPanel;

/**
 * Provides debug information on the Session contents.
 */
class SessionPanel extends DebugPanel {

/**
 * shutdown callback
 *
 * @param \Cake\Event\Event $event The event
 * @return array
 */
	public function shutdown(Event $event) {
		$request = $event->subject()->request;
		if ($request) {
			$this->_data = ['content' => $request->session()->read()];
		}
	}
}
