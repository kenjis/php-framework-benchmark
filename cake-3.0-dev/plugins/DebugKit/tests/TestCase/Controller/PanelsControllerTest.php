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

use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;

/**
 * Panel controller test.
 */
class PanelsControllerTestCase extends IntegrationTestCase {

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
			$routes->connect('/panels/:action/*', ['controller' => 'Panels']);
		});
	}

/**
 * Test getting a panel that exists.
 *
 * @return void
 */
	public function testView() {
		$this->configRequest([
			'headers' => ['Accept' => 'application/json']
		]);
		$this->get('/debug_kit/panels/view/aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');

		$this->assertResponseOk();
		$this->assertResponseContains('Request</h2>');
		$this->assertResponseContains('Routing Params</h4>');
	}

/**
 * Test getting a panel that does notexists.
 *
 * @return void
 */
	public function testViewNotExists() {
		$this->configRequest([
			'headers' => ['Accept' => 'application/json']
		]);
		$this->get('/debug_kit/panels/view/aaaaaaaa-ffff-ffff-ffff-aaaaaaaaaaaa');
		$this->assertResponseError();
		$this->assertResponseContains('Error page');
	}

}
