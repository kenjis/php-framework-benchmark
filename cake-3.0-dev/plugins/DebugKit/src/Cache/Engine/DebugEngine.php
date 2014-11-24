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
namespace DebugKit\Cache\Engine;

use Cake\Cache\CacheEngine;
use Cake\Cache\CacheRegistry;
use Cake\Core\App;
use DebugKit\DebugTimer;

/**
 * A spying proxy for cache engines.
 *
 * Used by the CachePanel to wrap and track metrics related to caching.
 */
class DebugEngine extends CacheEngine {

/**
 * Proxied cache engine config.
 *
 * @var mixed
 */
	protected $_config;

/**
 * Proxied engine
 *
 * @var mixed
 */
	protected $_engine;

/**
 * Hit/miss metrics.
 *
 * @var mixed
 */
	protected $_metrics = [
		'write' => 0,
		'delete' => 0,
		'read' => 0,
		'hit' => 0,
		'miss' => 0,
	];

/**
 * Constructor
 *
 * @param mixed $config Config data or the proxied adapter.
 */
	public function __construct($config) {
		$this->_config = $config;
	}

/**
 * Initialize the proxied Cache Engine
 *
 * @param array $config Array of setting for the engine.
 * @return bool True, this engine cannot fail to initialize.
 */
	public function init(array $config = []) {
		if (is_object($this->_config)) {
			$this->_engine = $this->_config;
			return true;
		}
		$registry = new CacheRegistry;
		$this->_engine = $registry->load('spies', $this->_config);
		unset($registry);
		return true;
	}

/**
 * Get the internal engine
 *
 * @return \Cake\Cache\CacheEngine
 */
	public function engine() {
		return $this->_engine;
	}

/**
 * Get the metrics for this object.
 *
 * @return array
 */
	public function metrics() {
		return $this->_metrics;
	}

/**
 * Track a metric.
 *
 * @param string $metric The metric to increment.
 * @return void
 */
	protected function _track($metric) {
		$this->_metrics[$metric]++;
	}

/**
 * {@inheritDoc}
 */
	public function write($key, $value) {
		$this->_track('write');
		DebugTimer::start('Cache.write ' . $key);
		$result = $this->_engine->write($key, $value);
		DebugTimer::stop('Cache.write ' . $key);
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function writeMany($data) {
		$this->_track('write');
		DebugTimer::start('Cache.writeMany');
		$result = $this->_engine->writeMany($data);
		DebugTimer::stop('Cache.writeMany');
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function read($key) {
		$this->_track('read');
		DebugTimer::start('Cache.read ' . $key);
		$result = $this->_engine->read($key);
		DebugTimer::stop('Cache.read ' . $key);
		$metric = 'hit';
		if ($result === false) {
			$metric = 'miss';
		}
		$this->_track($metric);
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function readMany($data) {
		$this->_track('read');
		DebugTimer::start('Cache.readMany');
		$result = $this->_engine->readMany($data);
		DebugTimer::stop('Cache.readMany');
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function increment($key, $offset = 1) {
		$this->_track('write');
		DebugTimer::start('Cache.increment ' . $key);
		$result = $this->_engine->increment($key, $offset);
		DebugTimer::stop('Cache.increment ' . $key);
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function decrement($key, $offset = 1) {
		$this->_track('write');
		DebugTimer::start('Cache.decrement ' . $key);
		$result = $this->_engine->decrement($key, $offset);
		DebugTimer::stop('Cache.decrement ' . $key);
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function delete($key) {
		$this->_track('delete');
		DebugTimer::start('Cache.delete ' . $key);
		$result = $this->_engine->delete($key);
		DebugTimer::stop('Cache.delete ' . $key);
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function deleteMany($data) {
		$this->_track('delete');
		DebugTimer::start('Cache.deleteMany');
		$result = $this->_engine->deleteMany($data);
		DebugTimer::stop('Cache.deleteMany');
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function clear($check) {
		$this->_track('delete');
		DebugTimer::start('Cache.clear');
		$result = $this->_engine->clear($check);
		DebugTimer::stop('Cache.clear');
		return $result;
	}

/**
 * {@inheritDoc}
 */
	public function groups() {
		return $this->_engine->groups();
	}

/**
 * {@inheritDoc}
 */
	public function clearGroup($group) {
		$this->_track('delete');
		DebugTimer::start('Cache.clearGroup ' . $group);
		$result = $this->_engine->clearGroup($group);
		DebugTimer::stop('Cache.clearGroup ' . $group);
		return $result;
	}

/**
 * Magic __toString() method to get the CacheEngine's name
 *
 * @return string Returns the CacheEngine's name
 */
	public function __toString() {
		if (!empty($this->_engine)) {
			list($ns, $class) = namespaceSplit(get_class($this->_engine));
			return str_replace('Engine', '', $class);
		}
		return $this->_config['className'];
	}

}
