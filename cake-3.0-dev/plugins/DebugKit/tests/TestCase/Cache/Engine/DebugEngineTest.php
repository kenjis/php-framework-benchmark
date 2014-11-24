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
namespace DebugKit\Test\TestCase\Cache\Engine;

use Cake\TestSuite\TestCase;
use DebugKit\Cache\Engine\DebugEngine;
use DebugKit\DebugTimer;

/**
 * Class DebugEngine
 */
class DebugEngineTest extends TestCase {

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$mock = $this->getMock('Cake\Cache\CacheEngine');
		$this->mock = $mock;
		$this->engine = new DebugEngine($mock);
		$this->engine->init();
		DebugTimer::clear();
	}

/**
 * Test that init() builds engines based on config.
 *
 * @return void
 */
	public function testInitEngineBasedOnConfig() {
		$engine = new DebugEngine([
			'className' => 'File',
			'path' => TMP
		]);
		$this->assertTrue($engine->init());
		$this->assertInstanceOf('Cake\Cache\Engine\FileEngine', $engine->engine());
	}

/**
 * Test that the normal errors bubble up still.
 *
 * @expectedException BadMethodCallException
 * @return void
 */
	public function testInitErrorOnInvalidConfig() {
		$engine = new DebugEngine([
			'className' => 'Derpy',
			'path' => TMP
		]);
		$engine->init();
	}

/**
 * Test that methods are proxied.
 *
 * @return void
 */
	public function testProxyMethodsTracksMetrics() {
		$this->mock->expects($this->at(0))
			->method('read');
		$this->mock->expects($this->at(1))
			->method('write');
		$this->mock->expects($this->at(2))
			->method('delete');
		$this->mock->expects($this->at(3))
			->method('increment');
		$this->mock->expects($this->at(4))
			->method('decrement');

		$this->engine->read('key');
		$this->engine->write('key', 'value');
		$this->engine->delete('key');
		$this->engine->increment('key');
		$this->engine->decrement('key');

		$result = $this->engine->metrics();
		$this->assertEquals(3, $result['write']);
		$this->assertEquals(1, $result['delete']);
		$this->assertEquals(1, $result['read']);
	}

/**
 * Test that methods are proxied.
 *
 * @return void
 */
	public function testProxyMethodsTimers() {
		$this->engine->read('key');
		$this->engine->write('key', 'value');
		$this->engine->delete('key');
		$this->engine->increment('key');
		$this->engine->decrement('key');

		$result = DebugTimer::getAll();
		$this->assertCount(6, $result);
		$this->assertArrayHasKey('Cache.read key', $result);
		$this->assertArrayHasKey('Cache.write key', $result);
		$this->assertArrayHasKey('Cache.delete key', $result);
		$this->assertArrayHasKey('Cache.increment key', $result);
		$this->assertArrayHasKey('Cache.decrement key', $result);
	}

}
