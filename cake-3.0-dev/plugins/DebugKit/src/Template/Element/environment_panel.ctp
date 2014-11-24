<?php
/**
 * Environment Panel Element
 *
 * Shows information about the current app environment
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Utility\Inflector;
?>
<?php
	if (!empty($app)) {
		$cakeRows = array();
		foreach ($app as $key => $val) {
			$cakeRows[] = array(
				h($key),
				h($val)
			);
		}
		$headers = array('Constant', 'Value');
		echo $this->Toolbar->table($cakeRows, $headers);
	} else {
		echo "No application environment available.";
	} ?>

<h2><?= __d('debug_kit', 'CakePHP Constants') ?></h2>
<?php
	if (!empty($cake)) {
		$cakeRows = array();
		foreach ($cake as $key => $val) {
			$cakeRows[] = array(
				h($key),
				h($val)
			);
		}
		$headers = array('Constant', 'Value');
		echo $this->Toolbar->table($cakeRows, $headers);
	} else {
		echo "CakePHP environment unavailable.";
	} ?>

<h2><?= __d('debug_kit', 'PHP Environment') ?></h2>
<?php
	$headers = array('Environment Variable', 'Value');

	if (!empty($php)) {
		$phpRows = array();
		foreach ($php as $key => $val) {
			$phpRows[] = array(
				h($key),
				h($val)
			);
		}
		echo $this->Toolbar->table($phpRows, $headers);
	} else {
		echo "PHP environment unavailable.";
	}

	if (isset($hidef)) {
		echo '<h2>' . __d('debug_kit', 'Hidef Environment') . '</h2>';
		if (!empty($hidef)) {
			$cakeRows = array();
			foreach ($hidef as $key => $val) {
				$cakeRows[] = array(
					h($key),
					h($val)
				);
			}
			$headers = array('Constant', 'Value');
			echo $this->Toolbar->table($cakeRows, $headers);
		} else {
			echo "No Hidef environment available.";
		}
	}
