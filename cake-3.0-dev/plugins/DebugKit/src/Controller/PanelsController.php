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
class PanelsController extends Controller {

/**
 * components
 *
 * @var array
 */
	public $components = ['RequestHandler'];

/**
 * Layout property.
 *
 * @var string
 */
	public $layout = 'DebugKit.panel';

/**
 * Before filter handler.
 *
 * @param \Cake\Event\Event $event The event.
 * @return void
 * @throws \Cake\Network\Exception\NotFoundException
 */
	public function beforeFilter(Event $event) {
		// TODO add config override.
		if (!Configure::read('debug')) {
			throw new NotFoundException();
		}
	}

/**
 * Index method that lets you get requests by panelid.
 *
 * @param string $requestId Request id
 * @return void
 * @throws \Cake\Network\Exception\NotFoundException
 */
	public function index($requestId = null) {
		$query = $this->Panels->find('byRequest', ['requestId' => $requestId]);
		$panels = $query->toArray();
		if (empty($panels)) {
			throw new NotFoundException();
		}
		$this->set([
			'_serialize' => ['panels'],
			'panels' => $panels
		]);
	}

/**
 * View a panel's data.
 *
 * @param string $id The id.
 * @return void
 */
	public function view($id = null) {
		$panel = $this->Panels->get($id);
		$this->set('panel', $panel);
		$this->set(unserialize($panel->content));
	}
}
