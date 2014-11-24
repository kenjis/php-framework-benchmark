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
namespace DebugKit\View\Helper;

use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\View\Helper;
use DebugKit\DebugKitDebugger;

/**
 * Provides Base methods for content specific debug toolbar helpers.
 * Acts as a facade for other toolbars helpers as well.
 *
 * @since         DebugKit 0.1
 */
class ToolbarHelper extends Helper {

/**
 * helpers property
 *
 * @var array
 */
	public $helpers = array('Html', 'Form', 'Url');

/**
 * Recursively goes through an array and makes neat HTML out of it.
 *
 * @param mixed $values Array to make pretty.
 * @param int $openDepth Depth to add open class
 * @param int $currentDepth current depth.
 * @param bool $doubleEncode Whether or not to double encode.
 * @return string
 */
	public function makeNeatArray($values, $openDepth = 0, $currentDepth = 0, $doubleEncode = false) {
		static $printedObjects = null;
		if ($currentDepth === 0) {
			$printedObjects = new \SplObjectStorage();
		}
		$className = "neat-array depth-$currentDepth";
		if ($openDepth > $currentDepth) {
			$className .= ' expanded';
		}
		$nextDepth = $currentDepth + 1;
		$out = "<ul class=\"$className\">";
		if (!is_array($values)) {
			if (is_bool($values)) {
				$values = array($values);
			}
			if ($values === null) {
				$values = array(null);
			}
			if (is_object($values) && method_exists($values, 'toArray')) {
				$values = $values->toArray();
			}
		}
		if (empty($values)) {
			$values[] = '(empty)';
		}
		foreach ($values as $key => $value) {
			$out .= '<li><strong>' . $key . '</strong>';
			if (is_array($value) && count($value) > 0) {
				$out .= '(array)';
			} elseif (is_object($value)) {
				$out .= '(object)';
			}
			if ($value === null) {
				$value = '(null)';
			}
			if ($value === false) {
				$value = '(false)';
			}
			if ($value === true) {
				$value = '(true)';
			}
			if (empty($value) && $value != 0) {
				$value = '(empty)';
			}
			if ($value instanceof Closure) {
				$value = 'function';
			}

			$isObject = is_object($value);
			if ($isObject && $printedObjects->contains($value)) {
				$isObject = false;
				$value = ' - recursion';
			}

			if ($isObject) {
				$printedObjects->attach($value);
			}

			if (
				(
				$value instanceof ArrayAccess ||
				$value instanceof Iterator ||
				is_array($value) ||
				$isObject
				) && !empty($value)
			) {
				$out .= $this->makeNeatArray($value, $openDepth, $nextDepth, $doubleEncode);
			} else {
				$out .= h($value, $doubleEncode);
			}
			$out .= '</li>';
		}
		$out .= '</ul>';
		return $out;
	}

/**
 * Create a table.
 *
 * @param array $rows Rows to make.
 * @param array $headers Optional header row.
 * @return string
 */
	public function table($rows, $headers = array()) {
		$out = '<table class="debug-table">';
		if (!empty($headers)) {
			$out .= $this->Html->tableHeaders($headers);
		}
		$out .= $this->Html->tableCells($rows);
		$out .= '</table>';
		return $out;
	}

}
