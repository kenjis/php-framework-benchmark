<?php
/**
 * SQL Log Panel Element
 *
 * PHP 5
 *
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
$noOutput = true;
?>

<?php if (!empty($tables)): ?>
<h4>Generated Models</h4>
<p class="warning">The following Table objects used <code>Cake\ORM\Table</code> instead of a concrete class:</p>
<ul class="list">
<?php foreach ($tables as $table): ?>
	<li><?= h($table) ?></li>
<?php endforeach ?>
</ul>
<hr />
<?php endif; ?>

<?php if (!empty($loggers)): ?>
	<?php foreach ($loggers as $logger): ?>
	<?php
	$queries = $logger->queries();
	if (empty($queries)):
		continue;
	endif;

	$noOutput = false;
	?>
	<div class="sql-log-panel-query-log">
		<h4><?= h($logger->name()) ?></h4>
		<h5>
		<?= __d(
			'debug_kit',
			'Total Time: {0} ms &mdash; Total Queries: {1} &mdash; Total Rows: {2}',
			$logger->totalTime(),
			count($queries),
			$logger->totalRows()
			);
		?>
		</h5>

		<?php $sqlLogRows = [];
			foreach ($queries as $query) {
				$sqlLogRows[] = [
					h($query['query']),
					h($query['rows']),
					h($query['took']),
				];
			}
			$headers = [
				__d('debug_kit', 'Query'),
				__d('debug_kit', 'Num rows'),
				__d('debug_kit', 'Took (ms)'),
			];
			echo $this->Toolbar->table($sqlLogRows, $headers);
		?>
	</div>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ($noOutput): ?>
<div class="warning"><?= __d('debug_kit', 'No active database connections') ?></div>
<?php endif ?>
