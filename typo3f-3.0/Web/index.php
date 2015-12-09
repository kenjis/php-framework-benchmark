<?php

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

putenv("FLOW_CONTEXT=Production");

$rootPath = isset($_SERVER['FLOW_ROOTPATH']) ? $_SERVER['FLOW_ROOTPATH'] : FALSE;
if ($rootPath === FALSE && isset($_SERVER['REDIRECT_FLOW_ROOTPATH'])) {
	$rootPath = $_SERVER['REDIRECT_FLOW_ROOTPATH'];
}
if ($rootPath === FALSE) {
	$rootPath = dirname(__FILE__) . '/../';
} elseif (substr($rootPath, -1) !== '/') {
	$rootPath .= '/';
}

require($rootPath . 'Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Core/Bootstrap.php');

define('BENCHMARK_ROOT_PATH', dirname($rootPath) . '/../..');
$context = \TYPO3\Flow\Core\Bootstrap::getEnvironmentConfigurationSetting('FLOW_CONTEXT') ?: 'Development';
$bootstrap = new \TYPO3\Flow\Core\Bootstrap($context);

$bootstrap->run();
