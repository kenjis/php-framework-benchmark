<?php
namespace Aura\Web_Project\_Config;

use Aura\Di\Config;
use Aura\Di\Container;

class Dev extends Config
{
    public function define(Container $di)
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', true);
    }

    public function modify(Container $di)
    {
    }
}
