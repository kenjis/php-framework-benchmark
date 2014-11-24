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
 * @since         DebugKit 3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace DebugKit\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Toolbar controller test.
 */
class ToolbarControllerTestCase extends IntegrationTestCase {

/**
 * Fixtures.
 *
 * @var array
 */
	public $fixtures = [
		'plugin.debug_kit.requests',
		'plugin.debug_kit.panels'
	];

/**
 * Setup method.
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		Router::plugin('DebugKit', function ($routes) {
			$routes->connect(
				'/toolbar/clear_cache/*',
				['plugin' => 'DebugKit', 'controller' => 'Toolbar', 'action' => 'clearCache']);
		});
	}

/**
 * Test clearing the cache does not work with GET
 *
 * @return void
 */
	public function testClearCacheNoGet() {
		$this->get('/debug_kit/toolbar/clear_cache?name=testing');

		$this->assertEquals(405, $this->_response->statusCode());
	}

/**
 * Test clearing the cache.
 *
 * @return void
 */
	public function testClearCache() {
		$mock = $this->getMock('Cake\Cache\CacheEngine');
		$mock->expects($this->once())
			->method('init')
			->will($this->returnValue(true));
		$mock->expects($this->once())
			->method('clear')
			->will($this->returnValue(true));
		Cache::config('testing', $mock);

		$this->configRequest(['headers' => ['Accept' => 'application/json']]);
		$this->post('/debug_kit/toolbar/clear_cache', ['name' => 'testing']);
		$this->assertResponseOk();
		$this->assertResponseContains('success');
	}

}
