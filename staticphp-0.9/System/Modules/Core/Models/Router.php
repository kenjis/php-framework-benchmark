<?php

namespace Core\Models;

use \Core\Models\Load;

/**
 * Router exception class.
 *
 * Custom exception class for router exceptions.
 * Allows our exception handler to give specific output for router exceptions.
 */
class RouterException extends \Exception
{
}


/**
 * Router class.
 *
 * Handles url parsing, routing and controller loading.
 */

class Router
{
    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Variables
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Url of protocol, hostname, domain name and port number (if its not 80 or 443 for https).
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $domain_url = null;

    /**
     * Variable that holds reference to base url.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $base_url = null;

    /**
     * Original url that is being requested.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $requested_url = null;

    /**
     * String containing full url to the final request.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $parsed_url = null;

    /**
     * Query string.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $query_string = null;

    /**
     * Array of prefixes for current request.
     *
     * (default value: [])
     *
     * @var string[]
     * @access public
     * @static
     */
    public static $prefixes = [];

    /**
     * Url containing all prefixes for current request.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $prefixes_url = null;

    /**
     * Original request segments, before processing Config/Routing.php.
     *
     * (default value: [])
     *
     * @var string[]
     * @access public
     * @static
     */
    public static $initial_segments = [];

    /**
     * Original request url, before processing Config/Routing.php.
     *
     * (default value: [])
     *
     * @var string
     * @access public
     * @static
     */
    public static $initial_segments_url = null;

    /**
     * Array of final url segments, i.e. everything after slash after domain name, except prefixes.
     *
     * (default value: [])
     *
     * @var string[]
     * @access public
     * @static
     */
    public static $segments = [];

    /**
     * String of url segments.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $segments_url = null;

    /**
     * Module responsible for current request handling.
     *
     * (default value: null)
     *
     * @var strig
     * @access public
     * @static
     */
    public static $module = null;

    /**
     * Path to controller file to be loaded.
     *
     * (default value: null)
     *
     * @var strig
     * @access public
     * @static
     */
    public static $file = null;

    /**
     * Namespace to load controller class from.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $namespace = null;

    /**
     * Path to controller without module.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $controller = null;

    /**
     * Class name to call controller methods from.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $class = null;

    /**
     * Controller class method to be called to handle this request.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $method = null;

    /**
     * Url to a method.
     *
     * (default value: null)
     *
     * @var string
     * @access public
     * @static
     */
    public static $method_url = null;


    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Helper methods
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Get base url of the website.
     *
     * Appends $url if provided.
     *
     * @access public
     * @static
     * @param  string $url (default: '')
     * @return string
     */
    public static function baseUrl($url = '')
    {
        return self::$base_url.$url;
    }

    /**
     * Get site url of the website.
     *
     * Returns baseurl + optional prefixes + original prefixes
     * (if $current_prefix is set to true) and appends $url if provided.
     *
     * @access public
     * @static
     * @param  string $url            (default: '')
     * @param  mixed  $prefix         (default: null)
     * @param  bool   $current_prefix (default: true)
     * @return string
     */
    public static function siteUrl($url = '', $prefix = null, $current_prefix = true)
    {
        $url002  = !empty($prefix) ? trim($prefix, '/').'/' : '';
        $url002 .= !empty($current_prefix) && !empty(self::$prefixes_url) ? self::$prefixes_url.'/' : '';

        return self::$base_url.$url002.$url;
    }

    /**
     * Redirect browser to another $url.
     *
     * If $site_uri is provided, $url will first be passed to Load::siteUrl.
     * If $e301 is set to true, "301 Moved Permanently" header will be sent too.
     * There are two types of redirects available:
     *      + http redirect - by using http headers
     *      + js redirect - by outputing location.href = $url
     *
     * @see Router::siteUrl()
     * @access public
     * @static
     * @param  string $url      (default: '')
     * @param  bool   $site_uri (default: true)
     * @param  bool   $e301     (default: false)
     * @param  string $type     (default: 'http')
     * @return void
     */
    public static function redirect($url = '', $site_uri = true, $e301 = false, $type = 'http')
    {
        switch ($type) {
            case 'js':
                echo '<script type="text/javascript"> window.location.href = \'',
                    ($site_uri === false ? $url : self::siteUrl($url)),
                    '\'; </script>';
                break;

            default:
                if ($e301 === true) {
                    header("HTTP/1.1 301 Moved Permanently");
                }

                header("Location: ".(empty($site_uri) ? $url : self::siteUrl($url)));
                header("Connection: close");
                break;
        }
        exit;
    }

    /**
     * Check if current request url has a prefix.
     *
     * @access public
     * @static
     * @param  string $prefix
     * @return bool
     */
    public static function hasPrefix($prefix)
    {
        return (isset(self::$prefixes[$prefix]));
    }

    /**
     * Error proof method for getting segment value by segment index.
     *
     * @example Instead of getting second index of segments like this:
     *          <code>$segment = (isset(Router::$segments[1])) ? Router::$segments[1] : false)</code>,
     *          you can use this method like this: <code>$segment = Router::segment(1);</code>.
     * @access public
     * @static
     * @param  int    $index
     * @return string
     */
    public static function segment($index)
    {
        return (empty(self::$segments[$index]) ? null : self::$segments[$index]);
    }

    /**
     * Output an error to the browser and stop script execution.
     *
     * @access public
     * @static
     * @param  int    $error_code
     * @param  string $error_string (default: '')
     * @param  string $description  (default: '')
     * @return void
     */
    public static function error($error_code, $error_string = '', $description = '')
    {
        header('HTTP/1.0 '.$error_code.' '.$error_string);
        $data = ['description' => $description];
        Load::view("Errors/E{$error_code}.html", $data);
        exit;
    }

    /**
     * Ease sending JSON response back to browser.
     *
     * @example Call function: <code>Router::jsonResponse($json_data);</code> add some data:
     *          <code>$json_data['xx'] = 1;</code> and on the end of script execution the $json_data
     *          array will be sent to client along with
     *          content-type:text/javascript header.
     * @access public
     * @param mixed &$json_data
     * @return void
     */
    public static function jsonResponse(&$json_data)
    {
        static $json_request = false;

        if (isset($GLOBALS['json_response_data']) && !empty($json_data) && is_array($json_data)) {
            $json_data = array_merge($GLOBALS['json_response_data'], $json_data);
            $GLOBALS['json_response_data'] = & $json_data;
        } elseif (isset($GLOBALS['json_response_data'])) {
            $json_data = $GLOBALS['json_response_data'];
            $GLOBALS['json_response_data'] = & $json_data;
        } elseif (empty($json_data) || is_array($json_data) == false) {
            $json_data = [];
            $GLOBALS['json_response_data'] = & $json_data;
        } else {
            $GLOBALS['json_response_data'] = & $json_data;
        }

        // Register shutdown function once
        if (empty($json_request)) {
            header('Content-Type:text/javascript; charset=utf-8');
            register_shutdown_function(function () {
                $data = $GLOBALS['json_response_data'];
                if (is_array($data) == false) {
                    $data = [];
                }
                echo json_encode($data);
            });

            $json_request = true;
        }
    }


    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Class helper methods
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Prints debug information.
     *
     * @access public
     * @static
     * @return void
     */
    public static function debug()
    {
        echo "Router::\$domain_url: ";
        print_r(Router::$domain_url);
        echo "\n";

        echo "Router::\$base_url: ";
        print_r(Router::$base_url);
        echo "\n";

        echo "Router::\$requested_url: ";
        print_r(Router::$requested_url);
        echo "\n";

        echo "Router::\$parsed_url: ";
        print_r(Router::$parsed_url);
        echo "\n";

        echo "Router::\$query_string: ";
        print_r(Router::$query_string);
        echo "\n";

        echo "Router::\$prefixes: ";
        print_r(Router::$prefixes);
        echo "\n";

        echo "Router::\$prefixes_url: ";
        print_r(Router::$prefixes_url);
        echo "\n";

        echo "Router::\$initial_segments: ";
        print_r(Router::$initial_segments);
        echo "\n";

        echo "Router::\$initial_segments_url: ";
        print_r(Router::$initial_segments_url);
        echo "\n";

        echo "Router::\$segments: ";
        print_r(Router::$segments);
        echo "\n";

        echo "Router::\$segments_url: ";
        print_r(Router::$segments_url);
        echo "\n";

        echo "Router::\$module: ";
        print_r(Router::$module);
        echo "\n";

        echo "Router::\$file: ";
        print_r(Router::$file);
        echo "\n";

        echo "Router::\$namespace: ";
        print_r(Router::$namespace);
        echo "\n";

        echo "Router::\$controller: ";
        print_r(Router::$controller);
        echo "\n";

        echo "Router::\$class: ";
        print_r(Router::$class);
        echo "\n";

        echo "Router::\$method: ";
        print_r(Router::$method);
        echo "\n";

        echo "Router::\$method_url: ";
        print_r(Router::$method_url);
        echo "\n";
    }

    /**
     * Convert / and \ to host system's directory separator.
     *
     * @access public
     * @static
     * @param  string $path
     * @return string
     */
    public static function makePathString($path)
    {
        return str_replace(['/', '\\'], DS, $path);
    }

    /**
     * Parse url to find file, class and method to be loaded as controller.
     *
     * @access public
     * @static
     * @param  string $url
     * @return array
     *                    An array of string objects:
     *                    <ul>
     *                    <li>'method' - method to be called</li>
     *                    <li>'module' - module where class resides</li>
     *                    <li>'class' - class where to call this method from</li>
     *                    <li>'file' - file where this class is from</li>
     *                    </ul>
     */
    public static function urlToFile($url)
    {
        // Explode $url
        $tmp = explode('/', $url);

        if (count($tmp) < 3) {
            return false;
        }

        // Get class, method and file from $url
        $data['module']     = array_shift($tmp);
        $data['method']     = array_pop($tmp);
        $data['class']      = end($tmp);
        $data['controller'] = implode('/', $tmp);
        $data['file']       = $data['module'] . '/Controllers/' . $data['controller'];
        $data['namespace']  = $data['module'] . '\\Controllers\\';

        return $data;
    }

    /**
     * Turn urls into namespace compatible strings.
     * Example: module/controller/method-name -> Module/Controller/MethodName.
     *
     * @access public
     * @static
     * @param  string $method
     * @return string
     */
    public static function urlToNamespace($url)
    {
        return implode('', array_map('ucfirst', explode('-', $url)));
    }

    /**
     * Reverse namespace compatible name to url.
     * Example: Module/Controller/MethodName -> module/controller/method-name.
     * TODO: Figure out how to do this without regex
     *
     * @access public
     * @static
     * @param  string $method
     * @return string
     */
    public static function namespaceToUrl($namespace)
    {
        $url = preg_replace('/(?<!\/|^)([A-Z])/', '-$1', $namespace);
        $url = strtolower($url);

        return $url;
    }


    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Router initialization methods
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Main router initialization method.
     *
     * This method calls <code>Router::splitSegments();</code>, <code>Router::findController()</code> and
     *      <code>Router::loadController()</code> methods.
     *
     * @access public
     * @static
     * @return void
     */
    public static function init()
    {
        self::splitSegments();
        self::findController();
        self::loadController();
    }

    /**
     * Splits request url into segments.
     *
     * @access public
     * @static
     * @param  bool $force (default: false)
     * @return void
     */
    public static function splitSegments($force = false)
    {
        if (empty($force) && !empty(self::$domain_url)) {
            return;
        }

        // Get some config variables
        $uri                 = Load::$config['request_uri'];
        $script_name         = Load::$config['script_name'];
        $script_path         = trim(dirname($script_name), '/');
        self::$base_url      = Load::$config['base_url'];
        self::$requested_url = $uri;

        // Set some variables
        if (empty(self::$base_url) && !empty($_SERVER['HTTP_HOST'])) {
            $https = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on');
            self::$domain_url = 'http'.(empty($https) ? '' : 's').'://'.$_SERVER['HTTP_HOST'];
            if (strpos($_SERVER['HTTP_HOST'], ':'.$_SERVER['SERVER_PORT']) === false) {
                if (
                    (empty($https) && $_SERVER['SERVER_PORT'] != 80) ||
                    (!empty($https) && $_SERVER['SERVER_PORT'] != 443)
                ) {
                    self::$domain_url .= ':'.$_SERVER['SERVER_PORT'];
                }
            }
            self::$domain_url .= '/';
            self::$base_url = self::$domain_url.(!empty($script_path) ? $script_path.'/' : '');
        }

        // Replace script_path in uri and remove query string
        $uri = trim(empty($script_name) ? $uri : str_replace($script_name, '', $uri), '/');

        // Extract url without query string
        $tmp = explode('?', $uri);
        $uri = trim($tmp[0], '/');

        // Clear query string
        if (!empty($tmp[1])) {
            self::$query_string = $tmp[1];
            self::$query_string = trim(self::$query_string, '/&?');
        }

        // Check url against our routing array from configuration
        $uri_tmp = $uri;
        foreach (Load::$config['routing'] as $key => &$item) {
            if (!empty($key) && !empty($item)) {
                $key = str_replace('#', '\\#', $key);
                $tmp = preg_replace('#'.$key.'#', $item, $uri);
                if ($tmp !== $uri) {
                    self::$initial_segments_url = $uri;
                    self::$initial_segments = explode('/', $uri);
                    $uri_tmp = $tmp;
                }
            }
        }

        // Set segments_full_url
        $uri = $uri_tmp;
        self::$parsed_url = $uri;

        // Explode segments
        if (!empty($uri)) {
            self::$segments = explode('/', $uri);
            self::$segments = array_map('rawurldecode', self::$segments);
        }

        // Get URL prefixes
        foreach (Load::$config['url_prefixes'] as &$item) {
            if (isset(self::$segments[0]) && self::$segments[0] == $item) {
                array_shift(self::$segments);
                self::$prefixes[$item] = $item;
            }

            if (isset(self::$initial_segments[0]) && self::$initial_segments[0] == $item) {
                array_shift(self::$initial_segments);
            }
        }

        // Set URL prefixes url
        self::$prefixes_url = implode('/', self::$prefixes);

        // Set URL
        self::$segments_url = implode('/', self::$segments);

        // Define base_url
        define('BASE_URL', self::$base_url);
    }


    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Controller loading
    |-------------------------------------------------------------------------------------------------------------------
    */

    /**
     * Looks in segments array for module/controller/method.
     *
     * @access public
     * @static
     * @return void
     */
    public static function findControllerInSegments()
    {
        // Fix segment names to translate "-" in url's to camelCase
        $segments = array_map(['\\Core\\Models\\Router', 'urlToNamespace'], self::$segments);

        // First one in segments is always a module
        $module = array_shift($segments);

        // Controller and method count, this number is needed because of subdirectory controllers and
        // possibility to have and have not method provided
        $count = count($segments);

        // Namespace always starts with a module
        self::$namespace = '\\'.$module.'\\Controllers\\';

        // Look for controller, class and method in segments
        foreach ($segments as $one) {
            if (preg_match('/^[a-zA-Z][a-zA-Z0-9-_]*$/', $segments[$count - 1]) == false) {
                $count -= 1;
                continue;
            }
            $slice        = array_slice($segments, 0, $count);
            $filename     = implode(DS, $slice);
            $path_to_file = APP_MODULES_PATH.$module.'/Controllers'.DS.$filename.'.php';

            if (is_file($path_to_file)) {
                $namespace = array_slice($segments, 0, $count - 1);
                if (!empty($namespace)) {
                    self::$namespace .= implode('\\', $namespace) . '\\';
                }

                self::$module = $module;
                self::$controller = implode(DS, $slice);
                self::$class = $segments[$count - 1];
                self::$file = $module.'/Controllers/'.self::$controller;

                if (count($segments) > $count) {
                    self::$method = lcfirst($segments[$count]);
                }

                break;
            }

            $count -= 1;
        }

        if ($count > 0) {
            // Module and Method also must be removed from the segments array
            $count += 2;

            // Remove controller and method from segments
            array_splice(self::$segments, 0, $count);
            self::$segments_url = implode('/', self::$segments);

            // Set requested segments
            if (empty(self::$initial_segments)) {
                self::$initial_segments = self::$segments;
                self::$initial_segments_url = self::$segments_url;
            } else {
                array_splice(self::$initial_segments, 0, $count);
                self::$initial_segments_url = implode('/', self::$initial_segments);
            }
        }
    }

    /**
     * Finds controller for current request, by segments and Config/Routing.php.
     *
     * @access public
     * @static
     * @return void
     */
    public static function findController()
    {
        // Get default controller, class and method
        if (!isset(Load::$config['routing'][''])) {
            throw new RouterException("Missing default routing configuration: \$config['routing'][''].");
        }

        $tmp = self::urlToFile(Load::$config['routing']['']);
        if ($tmp === false) {
            throw new RouterException(
                "Error in default routing configuration. Should be: module/class/method, instead found: ".
                Load::$config['routing']['']
            );
        }

        // Set default class and method
        self::$namespace  = $tmp['namespace'];
        self::$module     = $tmp['module'];
        self::$controller = $tmp['controller'];
        self::$class      = $tmp['class'];
        self::$method     = $tmp['method'];

        if (count(self::$segments) === 0) {
            // Defaults
            self::$file = $tmp['file'];
        } else {
            // Look for controller, class and method in segments
            self::findControllerInSegments();

            if (empty(self::$file)) {
                // Add default controller to see whether last argument is a folder and we should load default controller
                // from this folder
                self::$segments[] = self::$class;

                self::findControllerInSegments();
            }
        }

        // Set url to the method
        self::$method_url = self::$module.'/'.str_replace(self::$module.'/Controllers/', '', self::$file).'/'.self::$method;
        self::$method_url = self::namespaceToUrl(self::$method_url);
    }

    /**
     * Loads controller found in current request sesison or by passed in parameters.
     *
     * This method also calls pre-controller hook.
     *
     * @access public
     * @static
     * @param  string $file    (default: null)
     * @param  string $class   (default: null)
     * @param  string &$method (default: null)
     * @return void
     */
    public static function loadController(
        $file = null, $module = null, $namespace = null, $class = null, &$method = null
    )
    {
        // Load current file if $file parameter is empty
        if (empty($file)) {
            $file = APP_MODULES_PATH.self::$file.'.php';
        }

        // Load current module if $module parameter is empty
        if (empty($namespace)) {
            $namespace = self::$namespace;
        }

        // Load current namespace if $namespace parameter is empty
        if (empty($namespace)) {
            $namespace = self::$namespace;
        }

        // Load current class if $class parameter is empty
        if (empty($class)) {
            $class = self::$class;
        }

        // Load current method if $method parameter is empty
        if (empty($method)) {
            $method = self::$method;
        }

        // Load pre controller hook
        if (!empty(Load::$config['before_controller'])) {
            foreach (Load::$config['before_controller'] as $tmp) {
                call_user_func_array($tmp, [&$file, &$module, &$class, &$method]);
            }
        }

        // Check for $file
        if (is_file($file)) {
            // Namespaces support
            $class = $namespace.$class;

            // Create new reflection object from the controller class
            try {
                $ref = new \ReflectionClass($class);
            }
            catch (\Exception $e) {
                throw new RouterException('File "'.$file.'" was loaded, but the class '.$class.' could NOT be found');
            }

            // Call our contructor, if there is any
            $response = null;
            if ($ref->hasMethod('construct') === true) {
                $response = $ref->getMethod('construct')->invokeArgs(null, [&$class, &$method]);
            }

            // Call requested method
            $method_response = null;
            if ($ref->hasMethod($method) === true) {
                $class_method = $ref->getMethod($method);
                $method_response = $class_method->invokeArgs(null, self::$segments);
            }
            // Call __callStatic
            elseif ($ref->hasMethod('__callStatic') === true) {
                // Add method to arguments
                $arguments = self::$segments;
                array_unshift($arguments, $method);

                $pad_args = (int)$ref->getStaticPropertyValue('pad_call_static_parameters', 0);
                if ($pad_args > 0 && count($arguments) < $pad_args) {
                    $arguments = array_pad($arguments, $pad_args, null);
                }

                // Invoke __callStatic
                $method_response = $ref->getMethod('__callStatic')->invoke(null, $method, $arguments);
            }
            // Error - method not found
            else {
                throw new RouterException('Method "'.$method.'" of class "'.$class.'" could not be found');
            }

            // Append method response to construct response
            if ($method_response !== null) {
                if ($response === null) {
                    $response = $method_response;
                } elseif (is_array($response)) {
                    if (is_array($method_response) == false) {
                        throw new RouterException(
                            "Construct method returns <em>\"".gettype($response)."\"</em>, ".
                            "but {$method} returns <em>\"".gettype($method_response)."\"</em>"
                        );
                    }
                    $response = array_merge($response, $method_response);
                } else {
                    $response .= $method_response;
                }
            }

            // Echo response if there was any
            if ($response !== null) {
                if (is_array($response)) {
                    header('Content-Type:text/javascript; charset=utf-8');
                    echo json_encode($response);
                } elseif (is_string($response) || is_numeric($response)) {
                    echo $response;
                }
            }

            // Call desctructor method
            if ($ref->hasMethod('destruct') === true) {
                $response = $ref->getMethod('destruct')->invokeArgs(null, []);
            }
        } else {
            $msg = 'Controller file for path: "'.self::$requested_url.'" was not found';
            if (empty(self::$requested_url)) {
                $msg = 'Default controller was not found: "'.Load::$config['routing'][''].'"';
            }
            throw new RouterException($msg);
        }
    }
}
