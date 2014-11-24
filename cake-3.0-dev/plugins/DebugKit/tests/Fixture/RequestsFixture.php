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
 * Requests fixture.
 *
 * Used to create schema for tests and at runtime.
 */
class RequestsFixture extends TestFixture {

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => ['type' => 'uuid', 'null' => false],
		'url' => ['type' => 'string', 'null' => false],
		'content_type' => ['type' => 'string'],
		'status_code' => ['type' => 'integer'],
		'method' => ['type' => 'string'],
		'requested_at' => ['type' => 'datetime', 'null' => false],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id']],
		]
	);

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
			'url' => '/tasks/add',
			'content_type' => 'text/html',
			'status_code' => 200,
			'requested_at' => '2014-08-21 7:41:12'
		]
	];
}

