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
 * @since         DebugKit 1.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Routing\Router;
?>
<?php if (empty($requests)): ?>
	<p class="warning"><?= __d('debug_kit', 'No previous requests logged.') ?></p>
<?php else: ?>
	<p><?= count($requests) ?> <?= __d('debug_kit', 'previous requests available') ?></p>
	<ul class="history-list">
		<li>
			<?= $this->Html->link(
				__d('debug_kit', 'Back to current request'),
				['plugin' => 'DebugKit', 'controller' => 'Panels', 'action' => 'index', $panel->request_id],
				['class' => 'history-link', 'data-request' => $panel->request_id]
			) ?>
		</li>
		<?php foreach ($requests as $request): ?>
			<?php $url = ['plugin' => 'DebugKit', 'controller' => 'Panels', 'action' => 'index', $request->id] ?>
			<li>
				<a class="history-link" data-request="<?= $request->id ?>" href="<?= $this->Url->build($url) ?>">
					<span class="history-time"><?= h($request->requested_at) ?></span>
					<span class="history-bubble"><?= h($request->method) ?></span>
					<span class="history-bubble"><?= h($request->status_code) ?></span>
					<span class="history-bubble"><?= h($request->content_type) ?></span>
					<span class="history-url"><?= h($request->url) ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
<script>
$(document).ready(function() {
	var panelButtons = $('.panel');
	var thisPanel = '<?= h($panel->id) ?>';
	var buttons = $('.history-link');

	// Highlight the active request.
	buttons.filter('[data-request=' + window.toolbar.currentRequest + ']').addClass('active');

	buttons.on('click', function(e) {
		var el = $(this);
		e.preventDefault();
		buttons.removeClass('active');
		el.addClass('active');

		window.toolbar.currentRequest = el.data('request');

		$.getJSON(el.attr('href'), function(response) {
			if (response.panels[0].request_id == window.toolbar.originalRequest) {
				$('#panel-content-container').removeClass('history-mode');
				$('#toolbar').removeClass('history-mode');
			} else {
				$('#panel-content-container').addClass('history-mode');
				$('#toolbar').addClass('history-mode');
			}

			for (var i = 0, len = response.panels.length; i < len; i++) {
				var panel = response.panels[i];
				var button = panelButtons.eq(i);
				var summary = button.find('.panel-summary');

				// Don't overwrite the history panel.
				if (button.data('id') == thisPanel) {
					continue;
				}
				button.attr('data-id', panel.id);
				summary.text(panel.summary);
			}
		});
	});
});
</script>
