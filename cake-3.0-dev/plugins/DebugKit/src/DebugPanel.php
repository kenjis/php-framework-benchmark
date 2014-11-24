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
 * @since         DebugKit 0.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace DebugKit;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Utility\Inflector;

/**
 * Base class for debug panels.
 *
 * @since         DebugKit 0.1
 */
class DebugPanel implements EventListenerInterface {

/**
 * Defines which plugin this panel is from so the element can be located.
 *
 * @var string
 */
	public $plugin = 'DebugKit';

/**
 * The data collected about a given request.
 *
 * @var array
 */
	protected $_data = [];

/**
 * Get the title for the panel.
 *
 * @return string
 */
	public function title() {
		list($ns, $name) = namespaceSplit(get_class($this));
		$name = substr($name, 0, strlen('Panel') * -1);
		return Inflector::humanize(Inflector::underscore($name));
	}

/**
 * Get the element name for the panel.
 *
 * @return string
 */
	public function elementName() {
		list($ns, $name) = namespaceSplit(get_class($this));
		return $this->plugin . '.' . Inflector::underscore($name);
	}

/**
 * Get the data a panel has collected.
 *
 * @return array
 */
	public function data() {
		return $this->_data;
	}

/**
 * Get the summary data for a panel.
 *
 * This data is displayed in the toolbar even when the panel is collapsed.
 *
 * @return string
 */
	public function summary() {
		return '';
	}

/**
 * Initialize hook method.
 *
 * @return void
 */
	public function initialize() {
	}

/**
 * Shutdown callback
 *
 * @param \Cake\Event\Event $event The event.
 * @return void
 */
	public function shutdown(Event $event) {
	}

/**
 * Get the events this panels supports.
 *
 * @return array
 */
	public function implementedEvents() {
		return [
			'Controller.shutdown' => 'shutdown',
		];
	}
}
