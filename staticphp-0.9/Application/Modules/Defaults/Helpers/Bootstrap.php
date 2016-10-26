<?php

// This is the right place to set headers, start a session, etc.

use \Core\Models\Load;

// Send content type and charset header
header('Content-type: text/html; charset=utf-8');


// CLI Access
if (!empty($GLOBALS['argv'][1])) {
    Load::$config['request_uri'] =& $GLOBALS['argv'][1];
}


// Set locales
// setlocale(LC_TIME, 'lv_LV.utf8', 'lv_LV.UTF-8');
// setlocale(LC_NUMERIC, 'lv_LV.utf8', 'lv_LV.UTF-8');
// setlocale(LC_CTYPE, 'lv_LV.utf8', 'lv_LV.UTF-8');
// date_default_timezone_set('Europe/Riga');


// Init db - Before uncommenting add at the use secion: "use \Core\Models\Db;"
// Db::init();


// Start session
/*
session_set_cookie_params(0);
session_name('MY_SESSION_NAME');
session_start();
*/


// Load js and css versions. These can be loaded from database or some cache.
load::$config['view_data']['css_version'] = 1;
load::$config['view_data']['js_version'] = 1;
