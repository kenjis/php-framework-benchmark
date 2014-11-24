<?php
/**
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Debugkit\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Panels fixture.
 *
 * Used to create schema for tests and at runtime.
 */
class PanelsFixture extends TestFixture {

/**
 * fields property
 *
 * @var array
 */
	public $fields = [
		'id' => ['type' => 'uuid'],
		'request_id' => ['type' => 'uuid', 'null' => false],
		'panel' => ['type' => 'string'],
		'title' => ['type' => 'string'],
		'element' => ['type' => 'string'],
		'summary' => ['type' => 'string'],
		'content' => ['type' => 'text'],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
			'unique_panel' => ['type' => 'unique', 'columns' => ['request_id', 'panel']]
		]
	];

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
			'request_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
			'panel' => 'DebugKit.Request',
			'title' => 'Request',
			'element' => 'DebugKit.request_panel',
			'content' => 'a:5:{s:6:"params";a:5:{s:6:"plugin";N;s:10:"controller";s:5:"Tasks";s:6:"action";s:3:"add";s:4:"_ext";N;s:4:"pass";a:0:{}}s:5:"query";a:0:{}s:4:"data";a:0:{}s:6:"cookie";a:2:{s:14:"toolbarDisplay";s:4:"show";s:7:"CAKEPHP";s:26:"9pk8sa2ot6pclki9f4iakio560";}s:3:"get";a:0:{}}'
		]
	];
}

