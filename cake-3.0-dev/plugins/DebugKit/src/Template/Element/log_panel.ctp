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
use DebugKit\Log\DebugKitLog;
?>
<?php if ($logger->noLogs()): ?>
	<p class="info"><?= __d('debug_kit', 'There were no log entries made this request') ?></p>
<?php else: ?>
	<?php foreach ($logger->all() as $logName => $logs): ?>
		<h3><?= __d('debug_kit', '{0} Messages', h(ucfirst($logName))) ?> </h3>
		<table cellspacing="0" cellpadding="0" class="debug-table">
			<thead>
				<tr><th><?= __d('debug_kit', 'Time') ?></th><th><?= __d('debug_kit', 'Message') ?></th></tr>
			</thead>
			<tbody>
			<?php foreach ($logs as $log): ?>
				<tr>
				<td width="200" class="code"><?= $log[0] ?></td>
				<td><?= h($log[1]) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endforeach; ?>
<?php endif; ?>
