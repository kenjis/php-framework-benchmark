<!DOCTYPE html>
<html>
	<head>
		<title>Debug Kit Toolbar</title>
		<?= $this->Html->css('DebugKit.reset') ?>
		<?= $this->Html->css('DebugKit.toolbar') ?>
	</head>
	<body>
	<?= $this->fetch('content') ?>
	<div id="loader">
		<?= $this->Html->image('DebugKit.cake.icon.png', ['class' => 'indicator'])?>
	</div>
	</body>
	<?= $this->Html->script('DebugKit.jquery') ?>
	<?= $this->Html->script('DebugKit.toolbar-app') ?>
	<?= $this->fetch('scripts') ?>
</html>
