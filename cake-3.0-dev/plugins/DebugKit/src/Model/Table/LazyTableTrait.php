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
namespace DebugKit\Model\Table;

use Cake\Core\App;

/**
 * A set of methods for building a database table when it is missing.
 *
 * Because the debugkit doesn't come with a pre-built SQLite database,
 * we'll need to make it as we need it.
 *
 * This trait lets us dump fixture schema into a given database at runtime.
 */
trait LazyTableTrait {

/**
 * Ensures the tables for the given fixtures exist in the schema.
 *
 * If the tables do not exist, they will be created on the current model's connection.
 *
 * @param array $fixtures The fixture names to check and/or insert.
 * @return void
 * @throws \RuntimeException When fixtures are missing/unknown/fail.
 */
	public function ensureTables(array $fixtures) {
		$connection = $this->connection();
		$schema = $connection->schemaCollection();
		$existing = $schema->listTables();

		foreach ($fixtures as $name) {
			$class = App::className($name, 'Test/Fixture', 'Fixture');
			if ($class === false) {
				throw new \RuntimeException("Unknown fixture '$name'.");
			}
			$fixture = new $class();
			$table = $fixture->table;
			if (in_array($table, $existing)) {
				continue;
			}
			$fixture->create($connection);
		}
	}

}
