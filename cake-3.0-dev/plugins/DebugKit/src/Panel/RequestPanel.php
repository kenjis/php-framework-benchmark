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
use Cake\Routing\Router;
use DebugKit\DebugPanel;

/**
 * Provides debug information on the Current request params.
 *
 */
class RequestPanel extends DebugPanel {

/**
 * Data collection callback.
 *
 * @param \Cake\Event\Event $event The shutdown event.
 * @return void
 */
	public function shutdown(Event $event) {
		$controller = $event->subject();
		$request = $controller->request;
		$this->_data = [
			'params' => $request->params,
			'query' => $request->query,
			'data' => $request->data,
			'cookie' => $request->cookies,
			'get' => $_GET,
			'headers' => ['response' => headers_sent($file, $line), 'file' => $file, 'line' => $line],
		];
	}
}
