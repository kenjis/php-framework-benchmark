<?php
/**
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         DebugKit 2.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace DebugKit\Test\TestCase\Panel;

use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use DebugKit\Panel\SqlLogPanel;

/**
 * Class SqlLogPanelTest
 */
class SqlLogPanelTest extends TestCase {

/**
 * fixtures.
 *
 * @var array
 */
	public $fixtures = ['core.articles'];

/**
 * Setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->panel = new SqlLogPanel();
	}

/**
 * Ensure that subrequests don't double proxy the logger.
 *
 * @return void
 */
	public function testInitializeTwiceNoDoubleProxy() {
		$event = new Event('Sample');

		$this->panel->initialize($event);
		$db = ConnectionManager::get('test');
		$logger = $db->logger();
		$this->assertInstanceOf('DebugKit\Database\Log\DebugLog', $logger);

		$this->panel->initialize($event);
		$second = $db->logger();
		$this->assertSame($second, $logger);
	}

/**
 * test the parsing of source list.
 *
 * @return void
 */
	public function testData() {
		$event = new Event('Sample');
		$this->panel->initialize($event);

		$articles = TableRegistry::get('Articles');
		$articles->findById(1)->first();

		$result = $this->panel->data();
		$this->assertArrayHasKey('loggers', $result);
	}

/**
 * Test getting summary data.
 *
 * @return void
 */
	public function testSummary() {
		$event = new Event('Sample');
		$result = $this->panel->initialize($event);

		$articles = TableRegistry::get('Articles');
		$articles->findById(1)->first();

		$result = $this->panel->summary();
		$this->assertRegExp('/\d+ - \d+ ms/', $result);
	}

}
