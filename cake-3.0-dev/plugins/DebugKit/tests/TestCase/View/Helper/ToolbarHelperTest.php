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
namespace DebugKit\Test\TestCase\View\Helper;

use Cake\Core\App;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\Helper\FormHelper;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use DebugKit\View\Helper\HtmlToolbarHelper;
use DebugKit\View\Helper\ToolbarHelper;
use StdClass;

/**
 * Class ToolbarHelperTestCase
 */
class ToolbarHelperTestCase extends TestCase {

/**
 * Setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		Router::connect('/:controller/:action');

		$request = new Request();
		$request->addParams(array('controller' => 'pages', 'action' => 'display'));

		$this->View = new View($request);
		$this->Toolbar = new ToolbarHelper($this->View);
	}

/**
 * Tear Down
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Toolbar);
	}

/**
 * Test makeNeatArray with basic types.
 *
 * @return void
 */
	public function testMakeNeatArrayBasic() {
		$in = false;
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', '0', '/strong', '(false)', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = null;
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', '0', '/strong', '(null)', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = true;
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', '0', '/strong', '(true)', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = array();
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', '0', '/strong', '(empty)', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);
	}

/**
 * Test that cyclic references can be printed.
 *
 * @return void
 */
	public function testMakeNeatArrayCyclicObjects() {
		$a = new StdClass;
		$b = new StdClass;
		$a->child = $b;
		$b->parent = $a;

		$in = array('obj' => $a);
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			array('ul' => array('class' => 'neat-array depth-0')),
			'<li', '<strong', 'obj', '/strong', '(object)',
			array('ul' => array('class' => 'neat-array depth-1')),
			'<li', '<strong', 'child', '/strong', '(object)',
			array('ul' => array('class' => 'neat-array depth-2')),
			'<li', '<strong', 'parent', '/strong',
			'(object) - recursion',
			'/li',
			'/ul',
			'/li',
			'/ul',
			'/li',
			'/ul'
		);
		$this->assertTags($result, $expected);
	}

/**
 * Test Neat Array formatting
 *
 * @return void
 */
	public function testMakeNeatArray() {
		$in = array('key' => 'value');
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', 'key', '/strong', 'value', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = array('key' => null);
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', 'key', '/strong', '(null)', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = array('key' => 'value', 'foo' => 'bar');
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', 'key', '/strong', 'value', '/li',
			'<li', '<strong', 'foo', '/strong', 'bar', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = array(
			'key' => 'value',
			'foo' => array(
				'this' => 'deep',
				'another' => 'value'
			)
		);
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', 'key', '/strong', 'value', '/li',
			'<li', '<strong', 'foo', '/strong',
				'(array)',
				array('ul' => array('class' => 'neat-array depth-1')),
				'<li', '<strong', 'this', '/strong', 'deep', '/li',
				'<li', '<strong', 'another', '/strong', 'value', '/li',
				'/ul',
			'/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = array(
			'key' => 'value',
			'foo' => array(
				'this' => 'deep',
				'another' => 'value'
			),
			'lotr' => array(
				'gandalf' => 'wizard',
				'bilbo' => 'hobbit'
			)
		);
		$result = $this->Toolbar->makeNeatArray($in, 1);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0 expanded'),
			'<li', '<strong', 'key', '/strong', 'value', '/li',
			'<li', '<strong', 'foo', '/strong',
				'(array)',
				array('ul' => array('class' => 'neat-array depth-1')),
				'<li', '<strong', 'this', '/strong', 'deep', '/li',
				'<li', '<strong', 'another', '/strong', 'value', '/li',
				'/ul',
			'/li',
			'<li', '<strong', 'lotr', '/strong',
				'(array)',
				array('ul' => array('class' => 'neat-array depth-1')),
				'<li', '<strong', 'gandalf', '/strong', 'wizard', '/li',
				'<li', '<strong', 'bilbo', '/strong', 'hobbit', '/li',
				'/ul',
			'/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$result = $this->Toolbar->makeNeatArray($in, 2);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0 expanded'),
			'<li', '<strong', 'key', '/strong', 'value', '/li',
			'<li', '<strong', 'foo', '/strong',
				'(array)',
				array('ul' => array('class' => 'neat-array depth-1 expanded')),
				'<li', '<strong', 'this', '/strong', 'deep', '/li',
				'<li', '<strong', 'another', '/strong', 'value', '/li',
				'/ul',
			'/li',
			'<li', '<strong', 'lotr', '/strong',
				'(array)',
				array('ul' => array('class' => 'neat-array depth-1 expanded')),
				'<li', '<strong', 'gandalf', '/strong', 'wizard', '/li',
				'<li', '<strong', 'bilbo', '/strong', 'hobbit', '/li',
				'/ul',
			'/li',
			'/ul'
		);
		$this->assertTags($result, $expected);

		$in = array('key' => 'value', 'array' => array());
		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			'ul' => array('class' => 'neat-array depth-0'),
			'<li', '<strong', 'key', '/strong', 'value', '/li',
			'<li', '<strong', 'array', '/strong', '(empty)', '/li',
			'/ul'
		);
		$this->assertTags($result, $expected);
	}

/**
 * Test makeNeatArray with object inputs.
 *
 * @return void
 */
	public function testMakeNeatArrayObjects() {
		$in = new StdClass();
		$in->key = 'value';
		$in->nested = new StdClass();
		$in->nested->name = 'mark';

		$result = $this->Toolbar->makeNeatArray($in);
		$expected = array(
			array('ul' => array('class' => 'neat-array depth-0')),
			'<li', '<strong', 'key', '/strong', 'value', '/li',
			'<li', '<strong', 'nested', '/strong',
			'(object)',
			array('ul' => array('class' => 'neat-array depth-1')),
			'<li', '<strong', 'name', '/strong', 'mark', '/li',
			'/ul',
			'/li',
			'/ul'
		);
		$this->assertTags($result, $expected);
	}

/**
 * Test Table generation
 *
 * @return void
 */
	public function testTable() {
		$rows = array(
			array(1, 2),
			array(3, 4),
		);
		$result = $this->Toolbar->table($rows);
		$expected = array(
			'table' => array('class' => 'debug-table'),
			'<tr',
			'<td', '1', '/td',
			'<td', '2', '/td', '/tr',
			'<tr',
			'<td', '3', '/td',
			'<td', '4', '/td', '/tr',
			'/table'
		);
		$this->assertTags($result, $expected);
	}
}
