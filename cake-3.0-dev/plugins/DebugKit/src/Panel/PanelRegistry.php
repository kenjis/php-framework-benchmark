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

use Cake\Core\App;
use Cake\Core\ObjectRegistry;
use Cake\Event\EventManager;
use Cake\Event\EventManagerTrait;

/**
 * Registry object for panels.
 */
class PanelRegistry extends ObjectRegistry {

	use EventManagerTrait;

/**
 * Constructor
 *
 * @param \Cake\Event\EventManager $events Event Manager that panels should bind to.
 *   Typically this is the global manager.
 */
	public function __construct(EventManager $events) {
		$this->eventManager($events);
	}

/**
 * Resolve a panel classname.
 *
 * Part of the template method for Cake\Utility\ObjectRegistry::load()
 *
 * @param string $class Partial classname to resolve.
 * @return string|false Either the correct classname or false.
 */
	protected function _resolveClassName($class) {
		return App::className($class, 'Panel', 'Panel');
	}

/**
 * Throws an exception when a component is missing.
 *
 * Part of the template method for Cake\Utility\ObjectRegistry::load()
 *
 * @param string $class The classname that is missing.
 * @param string $plugin The plugin the component is missing in.
 * @return void
 * @throws \RuntimeException
 */
	protected function _throwMissingClassError($class, $plugin) {
		throw new \RuntimeException("Unable to find '$class' panel.");
	}

/**
 * Create the panels instance.
 *
 * Part of the template method for Cake\Utility\ObjectRegistry::load()
 *
 * @param string $class The classname to create.
 * @param string $alias The alias of the panel.
 * @param array $config An array of config to use for the panel.
 * @return DebugKit\DebugPanel The constructed panel class.
 */
	protected function _create($class, $alias, $config) {
		$instance = new $class($this, $config);
		$this->eventManager()->attach($instance);
		return $instance;
	}

}
