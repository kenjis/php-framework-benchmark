<?php

namespace Core\Controllers;

use Core\Models\Router;
use Core\Models\Load;


/**
 * StaticPHP's base controller, sets various class variables and offers additional methods.
 */
class Controller
{
    public static $module_url = null;
    public static $controller_url = null;
    public static $method_url = null;


    /**
     *  Constructor - Called on each request.
     */
    public static function construct($class = null, $method = null)
    {
        // Get full urls to current controller and its method
        $site_url = Router::siteUrl();
        self::$method_url = $site_url.Router::$method_url.'/';
        self::$controller_url = dirname(self::$method_url).'/';
        self::$module_url = dirname(self::$controller_url).'/';

        // Pass these to the view, too
        Load::$config['view_data']['module_url'] = self::$module_url;
        Load::$config['view_data']['controller_url'] = self::$controller_url;
        Load::$config['view_data']['method_url'] = self::$method_url;

        // Add Router's preferences
        Load::$config['view_data']['module'] = Router::$module;
        Load::$config['view_data']['controller'] = Router::$controller;
        Load::$config['view_data']['class'] = Router::$class;
        Load::$config['view_data']['method'] = Router::$method;
    }


    /**
     *  Destructor - Called on each request after data is sent to browser.
     */
    public static function destruct()
    {
        // Not implemented
    }


    /**
     *  Render a view. This method instead of Load::view() prefixes paths with current module directory.
     */
    public static function render($views, $data = [])
    {
        $views = (array)$views;
        foreach ($views as $key => $item) {
            $views[$key] = Router::$module.DS.'Views'.DS.$item;
        }
        Load::view($views, $data);
    }


    /**
     *  Write $contents to the output. Arrays are jsonified.
     */
    public static function write($contents)
    {
        if (is_array($contents)) {
            echo json_encode($contents);
        } else {
            echo $contents;
        }
    }
}
