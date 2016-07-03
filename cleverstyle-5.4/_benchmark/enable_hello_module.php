<?php
/**
 * There is no CLI interface for module enabling yet, so lets do this once here
 */
cs\Event::instance()->on(
	'System/Config/init/after',
	function () {
		$Config                                 = cs\Config::instance();
		$Config->components['modules']['Hello'] = [
			'active'  => cs\Config\Module_Properties::ENABLED,
			'db'      => [],
			'storage' => []
		];
		$Config->save();
		unlink(__FILE__);
	}
);
