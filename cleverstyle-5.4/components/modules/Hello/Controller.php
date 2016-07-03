<?php
namespace cs\modules\Hello;
use cs\Page;

class Controller {
	static function index () {
		Page::instance()->interface = false;
		return 'Hello World!';
	}
}
