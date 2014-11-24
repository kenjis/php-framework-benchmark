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
namespace DebugKit\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;

/**
 * Provides access to panel data.
 */
class RequestsController extends Controller {

	public $layout = 'DebugKit.toolbar';

/**
 * Before filter handler.
 *
 * @param \Cake\Event\Event $event The event.
 * @return void
 * @throws \Cake\Network\Exception\NotFoundException
 */
	public function beforeFilter(Event $event) {
		// TODO add config override
		if (!Configure::read('debug')) {
			throw new NotFoundException();
		}
	}

/**
 * View a request's data.
 *
 * @param string $id The id.
 * @return void
 */
	public function view($id = null) {
		$toolbar = $this->Requests->get($id, ['contain' => 'Panels']);
		$this->set('toolbar', $toolbar);
	}

}
