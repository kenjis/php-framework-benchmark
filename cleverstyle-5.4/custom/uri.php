<?php
/**
 * Framework doesn't support working under sub-directory, so let's hack URI for the purpose of testing here
 */
if (strpos($_SERVER['REQUEST_URI'], '/php-framework-benchmark') === 0) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], strlen('/php-framework-benchmark'));
	$_SERVER['REQUEST_URI']	= explode('/', $_SERVER['REQUEST_URI']);
	// Remove framework name and version from URI
	unset($_SERVER['REQUEST_URI'][1]);
	$_SERVER['REQUEST_URI']	= implode('/', $_SERVER['REQUEST_URI']);
}
