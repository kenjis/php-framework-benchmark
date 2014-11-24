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
?>
<section>
	<h3><?= __d('debug_kit', 'Memory') ?></h3>
	<div class="peak-mem-use">
		<strong><?= __d('debug_kit', 'Peak Memory Use:') ?></strong>
		<?= $this->Number->toReadableSize($peakMemory) ?>
	</div>

	<table cellspacing="0" cellpadding="0">
		<thead>
			<tr><th><?= __d('debug_kit', 'Message') ?></th><th><?= __d('debug_kit', 'Memory Use') ?></th></tr>
		</thead>
		<tbody>
		<?php foreach ($memory as $key => $value): ?>
		<tr>
			<td><?= h($key) ?></td>
			<td class="right-text"><?= $this->Number->toReadableSize($value) ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</section>

<section>
	<h3><?= __d('debug_kit', 'Timers') ?></h3>
	<div class="request-time">
		<strong><?= __d('debug_kit', 'Total Request Time:') ?></strong>
		<?= $this->Number->precision($requestTime * 1000, 0) ?> ms
	</div>

	<table cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th><?= __d('debug_kit', 'Event') ?></th>
			<th><?= __d('debug_kit', 'Time in ms') ?></th>
			<th><?= __d('debug_kit', 'Timeline') ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$rows = [];
		$end = end($timers);
		$maxTime = $end['end'];

		$i = 0;
		$values = array_values($timers);

		foreach ($timers as $timerName => $timeInfo):
			$indent = 0;
			for ($j = 0; $j < $i; $j++):
				if (($values[$j]['end'] > $timeInfo['start']) && ($values[$j]['end']) > ($timeInfo['end'])):
					$indent++;
				endif;
			endfor;
			$indent = str_repeat("\xC2\xA0\xC2\xA0", $indent);
		?>
		<tr>
			<td>
			<?= h($indent . $timeInfo['message']) ?>
			</td>
			<td class="right-text"><?= $this->Number->precision($timeInfo['time'] * 1000, 2) ?></td>
			<td><?= $this->SimpleGraph->bar(
				$timeInfo['time'] * 1000,
				$timeInfo['start'] * 1000,
				array(
					'max' => $maxTime * 1000,
					'requestTime' => $requestTime * 1000,
				)
			) ?></td>
			<?php
			$i++;
		endforeach;
		?>
		</tbody>
	</table>
</section>
