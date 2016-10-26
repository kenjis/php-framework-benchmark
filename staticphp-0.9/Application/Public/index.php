<?php

// Define paths
define('PUBLIC_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('APP_PATH', dirname(PUBLIC_PATH).DIRECTORY_SEPARATOR);
define('APP_MODULES_PATH', APP_PATH.'Modules'.DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(APP_PATH).DIRECTORY_SEPARATOR);
define('SYS_PATH', BASE_PATH.'System'.DIRECTORY_SEPARATOR);
define('SYS_MODULES_PATH', SYS_PATH.'Modules'.DIRECTORY_SEPARATOR);

// Load core class
require SYS_PATH.'Modules/Core/Helpers/Bootstrap.php'; // Load
