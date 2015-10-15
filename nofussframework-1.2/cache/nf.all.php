<?php


namespace Nf;

abstract class Session extends Singleton
{

    protected static $_instance = null;

    public static function factory($namespace, $class, $params, $lifetime)
    {
        $className = '\\' . $namespace . '\\' . ucfirst($class);
        return new $className($params, $lifetime);
    }

    public static function start()
    {
        $config = Registry::get('config');
        if (isset($config->session)) {
            // optional parameters sent to the constructor
            if (isset($config->session->params)) {
                $sessionParams = $config->session->params;
            }
            if (is_object($config->session->handler)) {
                $sessionHandler = self::factory($config->session->handler->namespace, $config->session->handler->class, $sessionParams, $config->session->lifetime);
            } else {
                $sessionHandler = self::factory('Nf\Session', $config->session->handler, $sessionParams, $config->session->lifetime);
            }
            
            session_name($config->session->cookie->name);
            session_set_cookie_params(0, $config->session->cookie->path, $config->session->cookie->domain, false, true);
            
            session_set_save_handler(array(
                &$sessionHandler,
                'open'
            ), array(
                &$sessionHandler,
                'close'
            ), array(
                &$sessionHandler,
                'read'
            ), array(
                &$sessionHandler,
                'write'
            ), array(
                &$sessionHandler,
                'destroy'
            ), array(
                &$sessionHandler,
                'gc'
            ));
            register_shutdown_function('session_write_close');
            session_start();
            // session_regenerate_id(true);
            Registry::set('session', $sessionHandler);
            return $sessionHandler;
        } else {
            return false;
        }
        
    }

    public static function getData()
    {
        return $_SESSION;
    }

    public function __get($key)
    {
        return self::get($key);
    }

    public function __set($key, $value)
    {
        return self::set($key, $value);
    }

    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function delete($key)
    {
        unset($_SESSION[$key]);
    }
}



namespace Nf;

abstract class Singleton
{

    protected static $_instance=null;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (static::$_instance===null) {
            $className = get_called_class();
            static::$_instance = new $className;
        }

        return static::$_instance;
    }

    public function __clone()
    {
        throw new Exception('Cloning not allowed on a singleton object', E_USER_ERROR);
    }
}


namespace Nf;

class Make
{

    /**
     *
     * @param string $action
     *            Executes a make action, like "compress" for putting every file into a single .php file or "map" all the classes in a classmap
     *            You can use your method with make if you call something like:
     *            php index.php -m "\App\Mymake\Compressor::compress?type=js", type=js will be accessible with a
     *            $front = \Nf\Front::getInstance();
     *            $params = $front->getRequest()->getParams();
     *            
     */
    
    // merge all the framework files to a single php file, merge all the routes to /cache/allroutes.php
    public static function compress($action = '')
    {
        // merge framework files
        $destFile = Registry::get('applicationPath') . '/cache/nf.all.php';
        if (is_file($destFile)) {
            unlink($destFile);
        }
        // get the actual folder of Nf in the app's settings
        $includedFiles = get_included_files();
        $folder = null;
        
        foreach ($includedFiles as $includedFile) {
            if (preg_match('%Nf\/Autoloader\.php$%', $includedFile, $regs)) {
                $folder = str_replace('/Autoloader.php', '', $includedFile);
                $allFiles = self::getAllFiles($folder);
                // sort by depth for include
                uasort($allFiles, array(
                    'self',
                    'orderFilesByDepth'
                ));
                $bigInclude = '<?' . 'php' . "\n";
                foreach ($allFiles as $file) {
                    if (substr($file, - 4) == '.php') {
                        $bigInclude .= "\n" . str_replace('<?' . 'php', '', file_get_contents($file));
                    }
                }
                file_put_contents($destFile, $bigInclude);
                
                // merge routes files
                $destRoutesFile = Registry::get('applicationPath') . '/cache/routes.all.php';
                if (is_file($destRoutesFile)) {
                    unlink($destRoutesFile);
                }
                $router = \Nf\Router::getInstance();
                $router->setRootRoutes();
                $router->setRoutesFromFiles();
                $router->addAllRoutes();
                $allRoutes = $router->getAllRoutes();
                $bigInclude = '<?' . 'php' . "\n return ";
                $bigInclude .= var_export($allRoutes, true);
                $bigInclude .= ";";
                file_put_contents($destRoutesFile, $bigInclude);
                break;
            }
        }
        
        if ($folder === null) {
            die('The cache already exists, remove the generated files before in /cache (nf.all.php and routes.all.php)' . PHP_EOL);
        }
    }

    private static function getAllFiles($folder)
    {
        $folder = rtrim($folder, '/');
        $root = scandir($folder);
        foreach ($root as $value) {
            if ($value === '.' || $value === '..') {
                continue;
            }
            if (is_file($folder . '/' . $value)) {
                $result[] = $folder . '/' . $value;
                continue;
            }
            foreach (self::getAllFiles($folder . '/' . $value) as $value) {
                $result[] = $value;
            }
        }
        return $result;
    }

    private static function orderFilesByDepth($file1, $file2)
    {
        $t = (substr_count($file1, '/') > substr_count($file2, '/'));
        return $t ? 1 : - 1;
    }
}


namespace Nf;

class Db
{

    const FETCH_ASSOC = 2;

    const FETCH_NUM = 3;

    const FETCH_OBJ = 5;

    const FETCH_COLUMN = 7;

    private static $_connections = array();

    public static function factory($config)
    {
        if (! is_array($config)) {
            // convert to an array
            $conf = array();
            $conf['adapter'] = $config->adapter;
            $conf['params'] = (array) $config->params;
            $conf['profiler'] = (array) $config->profiler;
        } else {
            $conf = $config;
        }
        $adapterName = get_class() . '\\Adapter\\' . $conf['adapter'];
        $dbAdapter = new $adapterName($conf['params']);
        $dbAdapter->setProfilerConfig($conf['profiler']);
        return $dbAdapter;
    }

    public static function getConnection($configName, $alternateHostname = null, $alternateDatabase = null, $storeInInstance = true)
    {
        $config = \Nf\Registry::get('config');
        
        if(!isset($config->db->$configName)) {
            throw new \Exception('The adapter "' . $configName . '" is not defined in the config file');
        }
        
        $defaultHostname = $config->db->$configName->params->hostname;
        $defaultDatabase = $config->db->$configName->params->database;
        $hostname = ($alternateHostname !== null) ? $alternateHostname : $defaultHostname;
        $database = ($alternateDatabase !== null) ? $alternateDatabase : $defaultDatabase;
        
        // if the connection has already been created and if we store the connection in memory for future use
        if (isset(self::$_connections[$configName . '-' . $hostname . '-' . $database]) && $storeInInstance) {
            return self::$_connections[$configName . '-' . $hostname . '-' . $database];
        } else {
            // optional profiler config
            $profilerConfig = isset($config->db->$configName->profiler) ? (array)$config->db->$configName->profiler : null;
            if ($profilerConfig != null) {
                $profilerConfig['name'] = $configName;
            }
            
            // or else we create a new connection
            $dbConfig = array(
                'adapter' => $config->db->$configName->adapter,
                'params' => array(
                    'hostname' => $hostname,
                    'username' => $config->db->$configName->params->username,
                    'password' => $config->db->$configName->params->password,
                    'database' => $database,
                    'charset' => $config->db->$configName->params->charset
                ),
                'profiler' => $profilerConfig
            );
            
            // connection with the factory method
            $dbConnection = self::factory($dbConfig);
            if ($storeInInstance) {
                self::$_connections[$configName . '-' . $hostname . '-' . $database] = $dbConnection;
            }
            return $dbConnection;
        }
    }
}



namespace Nf;

use \IntlDateFormatter;
use \NumberFormatter;

class Localization extends Singleton
{

    protected static $_instance;

    protected $_currentLocale='fr_FR';

    const NONE=IntlDateFormatter::NONE;
    const SHORT=IntlDateFormatter::SHORT;
    const MEDIUM=IntlDateFormatter::MEDIUM;
    const LONG=IntlDateFormatter::LONG;
    const FULL=IntlDateFormatter::FULL;

    public static function normalizeLocale($str)
    {
        $str=str_replace('-', '_', $str);
        $arr=explode('_', $str);
        $out=strtolower($arr[0]) . '_' . strtoupper($arr[1]);
        return $out;
    }

    public static function setLocale($locale)
    {
        $instance=self::$_instance;
        $instance->_currentLocale=$locale;
    }

    public static function getLocale()
    {
        $instance=self::$_instance;
        return $instance->_currentLocale;
    }

    public static function formatDate($timestamp, $formatDate = self::SHORT, $formatTime = self::SHORT)
    {
        $instance=self::$_instance;
        $fmt=new IntlDateFormatter($instance->_currentLocale, $formatDate, $formatTime);
        return $fmt->format($timestamp);
    }

    // syntax can be found on : http://userguide.icu-project.org/formatparse/datetime
    public static function formatOther($timestamp, $format = 'eeee')
    {
        $instance=self::$_instance;
        $fmt=new IntlDateFormatter($instance->_currentLocale, 0, 0);
        $fmt->setPattern($format);
        return $fmt->format($timestamp);
    }

    public static function formatDay($timestamp, $fullName = true)
    {
        return self::formatOther($timestamp, ($fullName?'EEEE':'EEE'));
    }

    public static function formatMonth($timestamp, $fullName = true)
    {
        return self::formatOther($timestamp, ($fullName?'LLLL':'LLL'));
    }

    public static function formatCurrency($amount, $currency)
    {
        $instance=self::$_instance;
        $fmt = new NumberFormatter($instance->_currentLocale, NumberFormatter::CURRENCY);
        return $fmt->formatCurrency($amount, $currency);
    }

    public static function formatNumber($value)
    {
        $instance=self::$_instance;
        $fmt = new NumberFormatter($instance->_currentLocale, NumberFormatter::DECIMAL);
        return $fmt->format($value);
    }

    public static function dateToTimestamp($date, $formatDate = self::SHORT, $formatTime = self::SHORT, $acceptISOFormat = false)
    {
        if (self::isTimestamp($date)) {
            return $date;
        } elseif ($acceptISOFormat && self::isISOFormat($date)) {
            $dt=new \DateTime($date);
            return $dt->getTimestamp();
        } else {
            $instance=self::$_instance;
            $fmt=new IntlDateFormatter($instance->_currentLocale, $formatDate, $formatTime);
            $timestamp=$fmt->parse($date);
            if ($timestamp) {
                return $timestamp;
            } else {
                throw new \Exception('input date is in another format and is not recognized:' . $date);
            }
        }
    }

    public static function isISOFormat($date)
    {
        if (preg_match('/\A(?:^([1-3][0-9]{3,3})-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][1-9]|3[0-1])\s([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])$)\Z/im', $date)) {
            return true;
        } elseif (preg_match('/\A(?:^([1-3][0-9]{3,3})-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][1-9]|3[0-1])$)\Z/im', $date)) {
            return true;
        } else {
            return false;
        }
    }

    public static function isTimestamp($timestamp)
    {
        return ((string) (int) $timestamp === (string) $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
}


namespace Nf;

use Nf\Registry;
use Nf\Front;

class Router extends Singleton
{
    
    // pour le routeur
    private $routingPreferences = array();

    private $routesDirectories = array();

    private $rootRoutesDirectories = array();

    private $allRoutesByVersionAndLocale = array();

    private $activeRoute = array();

    private $allVersionsUrls = array();

    const rootRouteFilename = '_root.php';

    const defaultRequestType = 'default';

    const defaultRouteName = 'default';
    
    // routes
    public function addAllRoutes()
    {
                
        if (Registry::get('environment')!='dev' && file_exists(Registry::get('applicationPath') . '/cache/routes.all.php')) {
            $this->allRoutesByVersionAndLocale = require(Registry::get('applicationPath') . '/cache/routes.all.php');
        } else {
            $routesDirectory = realpath(Registry::get('applicationPath') . '/routes');
            $directory = new \RecursiveDirectoryIterator($routesDirectory);
            $files = new \RecursiveIteratorIterator($directory);
            $allRouteFiles = array();
            foreach ($files as $file) {
                $pathname = ltrim(str_replace($routesDirectory, '', $file->getPathname()), '/');
                // if it's not a folder or anything other than a .php
                if (substr($pathname, - 1, 1) != '.' && substr($pathname, -4)=='.php') {
                    $allRouteFiles[] = $pathname;
                }
            }
            // sort allRouteFiles by their depth to allow inheriting a route from all versions and or locales
            usort($allRouteFiles, function ($a, $b) {
                return substr_count($a, '/') > substr_count($b, '/');
            });
            foreach ($allRouteFiles as $file) {
                $pathname = ltrim(str_replace($routesDirectory, '', $file), '/');
            
                $arrPath = explode('/', $pathname);
            
                // routes are sorted by version and locale
                if (count($arrPath) == 3) {
                    $version = $arrPath[0];
                    $locale = $arrPath[1];
                } elseif (count($arrPath) == 2) {
                    $version = $arrPath[0];
                    $locale = '*';
                } elseif (count($arrPath) == 1) {
                    $version = '*';
                    $locale = '*';
                }
                // add the route to allRoutes, sorted by version and locale
                // all your routes are belong to us
                if (! isset($this->allRoutesByVersionAndLocale[$version])) {
                    $this->allRoutesByVersionAndLocale[$version] = array();
                }
                if (! isset($this->allRoutesByVersionAndLocale[$version][$locale])) {
                    $this->allRoutesByVersionAndLocale[$version][$locale] = array();
                }
                if (basename($file) != self::rootRouteFilename) {
                    $subPath = str_replace('.php', '', basename($file));
                } else {
                    $subPath = '';
                }
                $newRoutes = require $routesDirectory . '/' . $pathname;
                // the file doesn't contain an array, or contains nothing => we ignore it
                if (is_array($newRoutes)) {
                    foreach ($newRoutes as &$newRoute) {
                        if (isset($newRoute['type']) && $newRoute['type']=='inherit') {
                            // go up one level until we find the route to inherit from
                            if (isset($this->allRoutesByVersionAndLocale[$version]['*'][$newRoute['from']])) {
                                $routeToAdd = $this->allRoutesByVersionAndLocale[$version]['*'][$newRoute['from']];
                                $routeToAdd['regexp'] = $routeToAdd['inheritableRegexp'];
                                $routeToAdd['regexp'] = ltrim($routeToAdd['regexp'], '/');
                                $routeToAdd['regexp'] = rtrim(ltrim($subPath . '/' . $routeToAdd['regexp'], '/'), '/');
                            } elseif (isset($this->allRoutesByVersionAndLocale['*']['*'][$newRoute['from']])) {
                                $routeToAdd = $this->allRoutesByVersionAndLocale['*']['*'][$newRoute['from']];
                                $routeToAdd['regexp'] = $routeToAdd['inheritableRegexp'];
                                $routeToAdd['regexp'] = ltrim($routeToAdd['regexp'], '/');
                                $routeToAdd['regexp'] = rtrim(ltrim($subPath . '/' . $routeToAdd['regexp'], '/'), '/');
                            }
                            $this->allRoutesByVersionAndLocale[$version][$locale][$routeToAdd['name']] = $routeToAdd;
                        } else {
                            if (isset($newRoute['regexp'])) {
                                $newRoute['regexp'] = ltrim($newRoute['regexp'], '/');
                                $newRoute['inheritableRegexp'] = $newRoute['regexp'];
                                $newRoute['regexp'] = rtrim(ltrim($subPath . '/' . $newRoute['regexp'], '/'), '/');
                            }
                            if (isset($newRoute['name'])) {
                                $this->allRoutesByVersionAndLocale[$version][$locale][$newRoute['name']] = $newRoute;
                            } else {
                                $this->allRoutesByVersionAndLocale[$version][$locale][] = $newRoute;
                            }
                        }
                    }
                }
            }
        }
    }

    public function setRoutesFromFiles()
    {
        $this->routingPreferences[] = 'files';
    }

    public function setStructuredRoutes()
    {
        $this->routingPreferences[] = 'structured';
    }

    public function setRootRoutes()
    {
        $this->routingPreferences[] = 'root';
    }

    public function findRoute($version, $locale)
    {
        $foundController = null;
        $config = Registry::get('config');
        $front = Front::getInstance();
        $originalUri = $front->getRequest()->getUri();
        
        // remove everything after a '?' which is not used in the routing system
        $uri = preg_replace('/\?.*$/', '', $originalUri);
        
        // strip the trailing slash, also unused
        $uri = rtrim((string) $uri, '/');
        
        foreach ($this->routingPreferences as $routingPref) {
            if ($routingPref == 'files') {
                $foundController = $this->findRouteFromFiles($uri, $version, $locale);
                // search by version only
                if (!$foundController) {
                    $foundController = $this->findRouteFromFiles($uri, $version, '*');
                }
                // search without version nor locale
                if (!$foundController) {
                    $foundController = $this->findRouteFromFiles($uri, '*', '*');
                }
            }
            
            if (! $foundController && $routingPref == 'structured') {
                // l'url doit être de la forme /m/c/a/, ou /m/c/ ou /m/
                if (preg_match('#^(\w+)/?(\w*)/?(\w*)#', $uri, $uriSegments)) {
                    $uriSegments[2] = ! empty($uriSegments[2]) ? $uriSegments[2] : 'index';
                    $uriSegments[3] = ! empty($uriSegments[3]) ? $uriSegments[3] : 'index';
                    
                    // on regarde si on a un fichier et une action pour le même chemin dans les répertoires des modules
                    if ($foundController = $front->checkModuleControllerAction($uriSegments[1], $uriSegments[2], $uriSegments[3])) {
                        $this->activeRoute = array(
                            'type' => self::defaultRequestType,
                            'name' => self::defaultRouteName,
                            'components' => array()
                        );
                        
                        // les éventuels paramètres sont en /variable/value
                        $paramsFromUri = ltrim(preg_replace('#^(\w+)/(\w+)/(\w+)#', '', $uri), '/');
                        
                        // si on envoie des variables avec des /
                        if ($paramsFromUri != '') {
                            if (substr_count($paramsFromUri, '/') % 2 == 1) {
                                preg_match_all('/([\w_]+)\/([^\/]*)/', $paramsFromUri, $arrParams, PREG_SET_ORDER);
                                for ($matchi = 0; $matchi < count($arrParams); $matchi ++) {
                                    $front->getRequest()->setParam($arrParams[$matchi][1], $arrParams[$matchi][2]);
                                }
                            }
                            
                            // si on envoie des variables avec des var1=val1
                            if (substr_count($paramsFromUri, '=') >= 1) {
                                preg_match_all('/([\w_]+)=([^\/&]*)/', $paramsFromUri, $arrParams, PREG_SET_ORDER);
                                for ($matchi = 0; $matchi < count($arrParams); $matchi ++) {
                                    $front->getRequest()->setParam($arrParams[$matchi][1], $arrParams[$matchi][2]);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // si c'est la route par défaut
        if (! $foundController) {
            if (empty($uri)) {
                if ($foundController = $front->checkModuleControllerAction($config->front->default->module, $config->front->default->controller, $config->front->default->action)) {
                    if (isset($route[2]) && isset($result[1])) {
                        $front->associateParams($route[2], $result[1]);
                    }
                }
            }
        }
        
        // reads which middlewares are required and adds them
        if ($foundController) {
            if (isset($this->activeRoute['middlewares'])) {
                $this->activeRoute['middlewaresPre'] = array();
                $this->activeRoute['middlewaresPost'] = array();
                foreach ($this->activeRoute['middlewares'] as $middlewareClass) {
                    if (! class_exists($middlewareClass)) {
                        throw new \Exception('The middleware ' . $middlewareClass . ' cannot be found. Matched route: ' . print_r($this->activeRoute, true));
                    }
                    if (isset(class_uses($middlewareClass)['Nf\Middleware\Pre'])) {
                        $this->activeRoute['middlewaresPre'][] = $middlewareClass;
                    } else {
                        $this->activeRoute['middlewaresPost'][] = $middlewareClass;
                    }
                }
            }
        }
        
        return $foundController;
    }
    
    private function findRouteFromFiles($uri, $version, $locale)
    {
        
        $foundController = null;
        $front = Front::getInstance();
        
        if (isset($this->allRoutesByVersionAndLocale[$version][$locale])) {
            $routes = $this->allRoutesByVersionAndLocale[$version][$locale];
        
            if (! $foundController) {
                $routes = array_reverse($routes);
        
                foreach ($routes as $route) {
                    if (! $foundController) {
                        // default type is "default"
                        $requestType = 'default';
        
                        // if a specific type is requested
                        if (isset($route['type'])) {
                            $requestType = $route['type'];
                        }
        
                        $routeRegexpWithoutNamedParams = preg_replace('/\([\w_]+:/', '(', $route['regexp']);
        
                        $arrRouteModuleControllerAction = explode('/', $route['controller']);
        
                        // make the first slash after our route directory optional
                        $routeTest = rtrim('/' . ltrim($routeRegexpWithoutNamedParams, '/'), '/');
  
                        // check if this is a match, or else continue until we have a match
                        if (preg_match('#^' . $routeRegexpWithoutNamedParams . '#', $uri, $refs)) {
                            // if using a rest request, the user can override the method
                            if ($requestType == 'rest') {
                                // default action
                                if (isset($_SERVER['REQUEST_METHOD'])) {
                                    $action = strtolower($_SERVER['REQUEST_METHOD']);
                                }
                                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                    // overloading the method with the "method" parameter if the request is POST
                                    if (isset($_POST['method'])) {
                                        $action = strtolower($_POST['method']);
                                    }
                                    // overloading the method with http headers
                                    // X-HTTP-Method (Microsoft) or X-HTTP-Method-Override (Google/GData) or X-METHOD-OVERRIDE (IBM)
                                    $acceptableOverridingHeaders = array(
                                        'HTTP_X_HTTP_METHOD',
                                        'HTTP_X_HTTP_METHOD_OVERRIDE',
                                        'HTTP_X_METHOD_OVERRIDE'
                                    );
                                    foreach ($acceptableOverridingHeaders as $overridingHeader) {
                                        if (isset($_SERVER[$overridingHeader])) {
                                            $action = strtolower($_SERVER[$overridingHeader]);
                                        }
                                    }
                                }
                                                                
                                // if overriding the action in the route
                                if (isset($arrRouteModuleControllerAction[2])) {
                                    $action = $arrRouteModuleControllerAction[2];
                                }
                            } else {
                                $action = $arrRouteModuleControllerAction[2];
                            }
                            
                            // on teste la présence du module controller action indiqué dans la route
                            if ($foundController = $front->checkModuleControllerAction($arrRouteModuleControllerAction[0], $arrRouteModuleControllerAction[1], $action)) {
                                $this->activeRoute = $route;
                                $front->setRequestType($requestType);
                                $front->associateParams($route['regexp'], $refs);
                                break;
                            }
                        }
                    }
                }
                unset($route);
            }
        }
        return $foundController;
    }

    public function getAllRoutes()
    {
        return $this->allRoutesByVersionAndLocale;
    }
    
    public function getActiveRoute()
    {
        return $this->activeRoute;
    }

    // returns the url from the defined routes by its name
    public function getNamedUrl($name, $params = array(), $version = null, $locale = null, $getFullUrl = true)
    {
        if ($version == null) {
            $version = Registry::get('version');
        }
        if ($locale == null) {
            $locale = Registry::get('locale');
        }
        // get the actual domain name from the url.ini
        $domainName = '';
        if ($getFullUrl) {
            if (! isset($this->allVersionsUrls[$version][$locale])) {
                $urlIni = Registry::get('urlIni');
                $localeSuffix = $urlIni->suffixes->$locale;
                $versionPrefix = $urlIni->versions->$version;
                if (strpos($versionPrefix, '|') !== false) {
                    $arrVersionPrefix = explode('|', $versionPrefix);
                    $versionPrefix = $arrVersionPrefix[0];
                    if ($versionPrefix == '<>') {
                        $versionPrefix = '';
                    }
                }
                $domainName = str_replace('[version]', $versionPrefix, $localeSuffix);
                if (! isset($this->allVersionsUrls[$version])) {
                    $this->allVersionsUrls[$version] = array();
                    $this->allVersionsUrls[$version][$locale] = $domainName;
                }
            } else {
                $domainName = $this->allVersionsUrls[$version][$locale];
            }
        }
        $foundRoute = false;
        if (isset($this->allRoutesByVersionAndLocale[$version][$locale][$name])) {
            $url = $this->allRoutesByVersionAndLocale[$version][$locale][$name]['regexp'];
            $foundRoute = true;
        } elseif (isset($this->allRoutesByVersionAndLocale[$version]['*'][$name])) {
            $url = $this->allRoutesByVersionAndLocale[$version]['*'][$name]['regexp'];
            $foundRoute = true;
        } elseif (isset($this->allRoutesByVersionAndLocale['*']['*'][$name])) {
            $url = $this->allRoutesByVersionAndLocale['*']['*'][$name]['regexp'];
            $foundRoute = true;
        }
        if ($foundRoute) {
            preg_match_all('/\(([\w_]+):([^)]+)\)/im', $url, $result, PREG_SET_ORDER);
            for ($matchi = 0; $matchi < count($result); $matchi ++) {
                if (isset($params[$result[$matchi][1]])) {
                    $url = str_replace($result[$matchi][0], $params[$result[$matchi][1]], $url);
                }
            }
            if ($getFullUrl) {
                return $domainName . '/' . $url;
            } else {
                return $url;
            }
        } else {
            throw new \Exception('Cannot find route named "' . $name . '" (version=' . $version . ', locale=' . $locale . ')');
        }
    }
}


namespace Nf;

abstract class File
{

    public static function mkdir($pathname, $mode = 0775, $recursive = false)
    {
        if (! is_dir($pathname)) {
            $oldumask = umask(0);
            $ret = @mkdir($pathname, $mode, $recursive);
            umask($oldumask);
            return $ret;
        }
        return true;
    }

    public static function rename($old, $new, $mode = 0775)
    {
        if (is_readable($old)) {
            $pathname = dirname($new);
            if (! is_dir($pathname)) {
                self::mkdir($pathname, $mode, true);
            }
            return rename($old, $new);
        }
        return false;
    }

    public static function uncompress($src, $dest, $unlinkSrc = false)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $src);
        finfo_close($finfo);
        
        switch ($mimeType) {
            case 'application/x-gzip':
                exec('gzip -dcf ' . $src . ($dest !== null ? ' > ' . $dest : ''), $out, $ret);
                break;
            
            default:
                return false;
        }
        
        if (isset($ret) && $ret === 0) {
            if ($unlinkSrc) {
                @unlink($src);
            }
        }
        return ($ret === 0);
    }

    public static function generatePath($input, $hereMark = '@')
    {
        $input = '' . $input;
        // 15567 => /7/15/56/7/@/
        // 6871985 => /5/68/71/98/5/@/
        // 687198565 /5/68/71/98/56/5/@/
        // 68719856 /6/68/71/98/56/@/
        // 21 /1/21/@/
        // 2121 /1/21/21/@/
        // 1 /1/1/@
        // antix /x/an/ti/x/@/
        $len = strlen($input);
        if ($len == 1) {
            $output = $input . '/' . $input;
        } else {
            $output = $input{$len - 1} . '/';
            for ($i = 0; $i < $len - 1; $i ++) {
                $output .= substr($input, $i, 1);
                if ($i % 2) {
                    $trailing = '/';
                } else {
                    $trailing = '';
                }
                $output .= $trailing;
            }
            $output .= $input{$len - 1};
        }
        $output .= '/' . $hereMark . '/';
        return '/' . $output;
    }
}



/**
 * Autoloader is a class loader.
 *
 *     <code>
 *      require($library_path . '/php/classes/Nf/Autoloader.php');
 *      $autoloader=new \Nf\Autoloader();
 *      $autoloader->addMap($applicationPath . '/configs/map.php');
 *      $autoloader->addNamespaceRoot('Nf', $libraryPath . '/Nf');
 *      $autoloader->register();
 *     </code>
 *
 * @package Nf
 * @author Julien Ricard
 * @version 1.0
 **/
namespace Nf;

class Autoloader
{

    protected static $_directories = array();

    protected static $_maps = array();

    protected static $_namespaceSeparator = '\\';

    const defaultSuffix = '.php';

    public function __construct() {
        
    }
    
    public static function load($className)
    {
        if (! class_exists($className)) {
            $foundInMaps = false;
            
            if (count(self::$_maps) != 0) {
                // reads every map for getting class path
                foreach (self::$_maps as $map) {
                    if (isset($map[$className])) {
                        if (self::includeClass($map[$className], $className)) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
                $foundInMaps = false;
            }
            if (! $foundInMaps) {
                $namespaceRoot = '';
                $fileNamePrefix = '';
                
                // reads each directory until it finds the class file
                if (false !== ($lastNsPos = strripos($className, self::$_namespaceSeparator))) {
                    $namespace = substr($className, 0, $lastNsPos);
                    $namespaceRoot = substr($className, 0, strpos($className, self::$_namespaceSeparator));
                    $shortClassName = substr($className, $lastNsPos + 1);
                    $fileNamePrefix = str_replace(self::$_namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
                } else {
                    $shortClassName = $className;
                }
                
                $fileNamePrefix .= str_replace('_', DIRECTORY_SEPARATOR, $shortClassName);
                
                foreach (self::$_directories as $directory) {
                    if ($directory['namespaceRoot'] == $namespaceRoot && $directory['namespaceRoot'] != '') {
                        // use the specified directory with remaining path
                        $fileNamePrefix = str_replace($namespaceRoot . DIRECTORY_SEPARATOR, '', $fileNamePrefix);
                        if (self::includeClass($directory['path'] . $fileNamePrefix . $directory['suffix'], $className)) {
                            return true;
                        } else {
                            // file was not found in the specified directory
                            return false;
                        }
                    } elseif ($directory['namespaceRoot'] == '') {
                        if (self::includeClass($directory['path'] . $fileNamePrefix . $directory['suffix'], $className)) {
                            return true;
                        }
                    }
                }
            }
        } else {
            return true;
        }
        return false;
    }

    public static function includeClass($file, $class_name)
    {
        if (! class_exists($class_name)) {
            if (file_exists($file)) {
                require $file;
                return true;
            } else {
                return false;
            }
        } else {
            // class already exists
        }
    }

    public static function addNamespaceRoot($namespaceRoot, $path, $suffix = self::defaultSuffix)
    {
        if (substr($path, - 1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
        self::$_directories[] = array(
            'namespaceRoot' => $namespaceRoot,
            'path' => $path,
            'suffix' => $suffix
        );
    }

    public function addMap($mapFilePath = null)
    {
        global $applicationPath;
        global $libraryPath;
        
        if($mapFilePath === null) {
            $mapFilePath = $applicationPath . '/cache/autoloader.map.php';    
        }
        
        if (file_exists($mapFilePath)) {
            if (pathinfo($mapFilePath, PATHINFO_EXTENSION) == 'php') {
                $newMap = require($mapFilePath);
                self::$_maps[] = $newMap;
            }
        }
    }

    public function register()
    {
        spl_autoload_register(__NAMESPACE__ . '\Autoloader::load');
    }
}



namespace Nf;

class Registry extends \ArrayObject
{

    private static $_registry = null;

    /**
     * Retrieves the default registry instance.
     *
     * @return Zend_Registry
     */
    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @param Zend_Registry $registry An object instance of type Zend_Registry,
     *   or a subclass.
     * @return void
     * @throws Zend_Exception if registry is already initialized.
     */
    public static function setInstance(\Nf\Registry $registry)
    {
        if (self::$_registry !== null) {
            #require_once 'Zend/Exception.php';
            throw new Exception('Registry is already initialized');
        }

        self::$_registry = $registry;
    }

    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init()
    {
        self::setInstance(new \Nf\Registry());
    }

    /**
     * getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type Nf\Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     * @return mixed
     * @throws Zend_Exception if no entry is registerd for $index.
     */
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new \Exception("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }

    /**
     * setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type Zend_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *   the value.
     * @param mixed $value The object to store in the ArrayObject.
     * @return void
     */
    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    /**
     * Returns TRUE if the $index is a named value in the registry,
     * or FALSE if $index was not found in the registry.
     *
     * @param  string $index
     * @return boolean
     */
    public static function isRegistered($index)
    {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }

    /**
     * Constructs a parent ArrayObject with default
     * ARRAY_AS_PROPS to allow acces as an object
     *
     * @param array $array data array
     * @param integer $flags ArrayObject flags
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $index
     * @returns mixed
     *
     * Workaround for http://bugs.php.net/bug.php?id=40442 (ZF-960).
     */
    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }
}



namespace Nf;

class UserAgent
{

    public $httpUserAgent;
    public $lowerHttpUserAgent;

    public function __construct($httpUserAgent)
    {
        $this->httpUserAgent=$httpUserAgent;
        $this->lowerHttpUserAgent=strtolower($httpUserAgent);
    }

    public function checkClass($class)
    {
        switch ($class) {
            case 'iphone':
                return $this->isIphone();
                break;
            case 'ipad':
                return $this->isIpad();
                break;
            case 'androidmobile':
                return $this->isAndroidMobile();
                break;
            case 'androidtablet':
                return $this->isAndroidTablet();
                break;
            case 'blackberry':
                return $this->isBlackberry();
                break;
        }
    }

    public function isIphone()
    {
        return strstr($this->lowerHttpUserAgent, 'iphone') || strstr($this->lowerHttpUserAgent, 'ipod');
    }

    public function isIpad()
    {
        return strstr($this->lowerHttpUserAgent, 'ipad');
    }

    public function isAndroidMobile()
    {
        return (strstr($this->lowerHttpUserAgent, 'android')!==false) && (strstr($this->lowerHttpUserAgent, 'mobile')===false);
    }

    public function isAndroidTablet()
    {
        return (strstr($this->lowerHttpUserAgent, 'android')!==false) && (strstr($this->lowerHttpUserAgent, 'mobile')===false);
    }

    public function isBlackberry()
    {
        return strstr($this->lowerHttpUserAgent, 'blackberry');
    }
}


namespace Nf;

class Task
{

    protected $pid;

    protected $ppid;

    protected $params = null;

    function __construct($params = null)
    {
        $this->params = $params;
    }

    function fork()
    {
        $pid = pcntl_fork();
        if ($pid == - 1)
            throw new Exception('fork error on Task object');
        elseif ($pid) {
            // we are in the parent class
            $this->pid = $pid;
            // echo "< in parent with pid {$this->pid}\n";
        } else {
            // we are in the child ᶘ ᵒᴥᵒᶅ
            $this->ppid = posix_getppid();
            $this->pid = posix_getpid();
            $this->run();
            exit(0);
        }
    }
    
    // overload this method in your class
    function run()
    {
        // echo "> in child {$this->pid}\n";
    }
    
    // call when a task is finished (in parent)
    function finish()
    {
        // echo "task finished {$this->pid}\n";
    }

    function pid()
    {
        return $this->pid;
    }
}





namespace Nf;

class Front extends Singleton
{

    protected static $_instance;
    
    // les modules
    private $_moduleDirectories = array();

    const controllersDirectory = 'controllers';
    
    // pour instancier le controller, forwarder...
    private $_moduleNamespace;

    private $_moduleName;

    private $_controllerName;

    private $_actionName;
    
    // pour le controller
    private $_request;

    private $_requestType;

    private $_response;

    private $_router;

    private $_session;

    public static $obLevel = 0;
    
    // the instance of the controller that is being dispatched
    private $_controllerInstance;

    private $_applicationNamespace = 'App';

    private $registeredMiddlewares = array();

    const MIDDLEWARE_PRE = 0;

    const MIDDLEWARE_POST = 1;

    public function __get($var)
    {
        $varName = '_' . $var;
        return $this->$varName;
    }

    public function getRequestType()
    {
        return $this->_requestType;
    }

    public function getModuleName()
    {
        return $this->_moduleName;
    }

    public function getControllerName()
    {
        return $this->_controllerName;
    }

    public function getActionName()
    {
        return $this->_actionName;
    }

    public function setRequest($request)
    {
        $this->_request = $request;
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }

    public function setRequestType($requestType)
    {
        $this->_requestType = $requestType;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function setSession($session)
    {
        $this->_session = $session;
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function getRouter()
    {
        return $this->_router;
    }

    public function setRouter($router)
    {
        $this->_router = $router;
    }

    public function setApplicationNamespace($namespace)
    {
        $this->_applicationNamespace = $namespace;
    }

    public function getApplicationNamespace()
    {
        return $this->_applicationNamespace;
    }

    public function getControllerInstance()
    {
        return $this->_controllerInstance;
    }
    
    // cache
    public function getCache($which)
    {
        // do we already have the cache object in the Registry ?
        if (Registry::isRegistered('cache_' . $which)) {
            return Registry::get('cache_' . $which);
        } else {
            // get the config for our cache object
            $config = Registry::get('config');
            if (isset($config->cache->$which->handler)) {
                $cache = Cache::factory($config->cache->$which->handler, (isset($config->cache->$which->params)) ? $config->cache->$which->params : array(), (isset($config->cache->$which->lifetime)) ? $config->cache->$which->lifetime : Cache::DEFAULT_LIFETIME);
                return $cache;
            } else {
                throw new Exception('The cache handler "' . $which . '" is not set in config file');
            }
        }
    }
    
    // modules
    public function addModuleDirectory($namespace, $dir)
    {
        $this->_moduleDirectories[] = array(
            'namespace' => $namespace,
            'directory' => $dir
        );
    }

    private function getControllerFilename($namespace, $directory, $module, $controller)
    {
        $controllerFilename = ucfirst($controller . 'Controller.php');
        return $directory . $module . '/' . self::controllersDirectory . '/' . $controllerFilename;
    }

    public function checkModuleControllerAction($inModule, $inController, $inAction)
    {
        $foundController = null;
        
        foreach ($this->_moduleDirectories as $moduleDirectory => $moduleDirectoryInfos) {
            $controllerFilename = $this->getControllerFilename($moduleDirectoryInfos['namespace'], $moduleDirectoryInfos['directory'], $inModule, $inController);
            
            if (file_exists($controllerFilename)) {
                $this->_moduleNamespace = $moduleDirectoryInfos['namespace'];
                $this->_moduleName = $inModule;
                $this->_controllerName = $inController;
                $this->_actionName = $inAction;
                $foundController = $controllerFilename;
                break;
            }
        }
        
        unset($moduleDirectory);
        unset($moduleDirectoryInfos);
        if (! $foundController) {
            return false;
        }
        return $foundController;
    }

    public function forward($module, $controller, $action)
    {
        if ($foundController = $this->checkModuleControllerAction($module, $controller, $action)) {
            if ($this->checkMethodForAction($foundController)) {
                $this->launchAction();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function associateParams($routeRegexp, $values)
    {
        if (! is_array($values)) {
            $values = array(
                $values
            );
        }
        
        preg_match_all('/\((\w+):/', $routeRegexp, $matches);
        
        for ($i = 0; $i < count($matches[1]); $i ++) {
            $this->_request->setParam($matches[1][$i], $values[$i + 1]);
        }
    }

    public function parseParameters($uri)
    {
        // les éventuels paramètres sont en /variable/value
        $paramsFromUri = ltrim(preg_replace('#^(\w+)/(\w+)/(\w+)#', '', $uri), '/');
        
        // si on envoie des variables avec des /
        if ($paramsFromUri != '') {
            if (substr_count($paramsFromUri, '/') % 2 == 1) {
                preg_match_all('/(\w+)\/([^\/]*)/', $paramsFromUri, $arrParams, PREG_SET_ORDER);
                for ($matchi = 0; $matchi < count($arrParams); $matchi ++) {
                    $this->_request->setParam($arrParams[$matchi][1], $arrParams[$matchi][2]);
                }
            }
            
            // si on envoie des variables avec des var1=val1
            if (substr_count($paramsFromUri, '=') >= 1) {
                preg_match_all('/(\w+)=([^\/&]*)/', $paramsFromUri, $arrParams, PREG_SET_ORDER);
                for ($matchi = 0; $matchi < count($arrParams); $matchi ++) {
                    $this->_request->setParam($arrParams[$matchi][1], $arrParams[$matchi][2]);
                }
            }
        }
    }

    public function getView()
    {
        if (! is_null($this->_controllerInstance->_view)) {
            return $this->_controllerInstance->_view;
        } else {
            $config = Registry::get('config');
            $view = View::factory($config->view->engine);
            $view->setResponse($this->_response);
            return $view;
        }
    }

    public function dispatch()
    {
        // on va regarder le m/c/a concerné par l'url ou les paramètres déjà saisis
        if ($foundController = $this->getRouter()->findRoute(Registry::get('version'), Registry::get('locale'))) {
            return $this->checkMethodForAction($foundController);
        } else {
            return false;
        }
    }

    private function checkMethodForAction($foundController)
    {
        // on lancera dans l'ordre le init, action, postAction
        require_once($foundController);
        $controllerClassName = $this->_moduleNamespace . '\\' . ucfirst($this->_moduleName) . '\\' . ucfirst($this->_controllerName) . 'Controller';
        $this->_controllerInstance = new $controllerClassName($this);
        
        $reflected = new \ReflectionClass($this->_controllerInstance);
        
        if ($reflected->hasMethod($this->_actionName . 'Action')) {
            return true;
        } else {
            return false;
        }
    }
    
    // called after dispatch
    public function init()
    {
        return $this->_controllerInstance->init();
    }
    
    // registers a middleware programmatically and not through a route
    public function registerMiddleware($middlewareInstance)
    {
        if (isset(class_uses($middlewareInstance)['Nf\Middleware\Pre'])) {
            $key = self::MIDDLEWARE_PRE;
        } else {
            $key = self::MIDDLEWARE_POST;
        }
        // adds the middleware
        $this->registeredMiddlewares[$key][] = $middlewareInstance;
    }
    
    // calls the actual action found from the routing system
    public function launchAction()
    {
        self::$obLevel = ob_get_level();
        
        if (php_sapi_name() != 'cli') {
            ob_start();
        }
        
        $router = $this->_router;
        $activeRoute = $router->getActiveRoute();
        
        // optionally sets the content-type
        if (isset($activeRoute['contentType'])) {
            $this->_response->setContentType($activeRoute['contentType']);
        }
        
        // optionally sets the client cache duration
        if (isset($activeRoute['cacheMinutes'])) {
            $this->_response->setCacheable($activeRoute['cacheMinutes']);
        }
        
        // call pre middlewares defined by the active route
        if (isset($activeRoute['middlewaresPre'])) {
            foreach ($activeRoute['middlewaresPre'] as $middleware) {
                $object = new $middleware();
                $object->execute();
            }
            unset($middleware);
        }
        // call pre middlewares defined programatically
        if (isset($this->registeredMiddlewares[self::MIDDLEWARE_PRE])) {
            foreach ($this->registeredMiddlewares[self::MIDDLEWARE_PRE] as $middleware) {
                $middleware->execute();
            }
        }
        
        // call the action
        call_user_func(array(
            $this->_controllerInstance,
            $this->_actionName . 'Action'
        ));
        $content = ob_get_clean();
        $this->_response->addBodyPart($content);
        
        // call post middlewares
        if (isset($activeRoute['middlewaresPost'])) {
            foreach ($activeRoute['middlewaresPost'] as $middleware) {
                $object = new $middleware();
                $object->execute();
            }
            unset($middleware);
        }
        // call post middlewares defined programatically, by instance
        if (isset($this->registeredMiddlewares[self::MIDDLEWARE_POST])) {
            foreach ($this->registeredMiddlewares[self::MIDDLEWARE_POST] as $middleware) {
                $middleware->execute();
            }
        }
    }
    
    // called after action
    public function postLaunchAction()
    {
        $reflected = new \ReflectionClass($this->_controllerInstance);
        if ($reflected->hasMethod('postLaunchAction')) {
            call_user_func(array(
                $this->_controllerInstance,
                'postLaunchAction'
            ), null);
        }
    }
}


namespace Nf;

abstract class Cache
{

    static $instances = array();
    
    // default lifetime for any stored value
    const DEFAULT_LIFETIME = 600;

    public static function getKeyName($keyName, $keyValues = array())
    {
        $config = Registry::get('config');
        if (! isset($config->cachekeys->$keyName)) {
            throw new \Exception('Key ' . $keyName . ' is not set in the config file.');
        } else {
            $configKey = $config->cachekeys->$keyName;
            if (is_array($keyValues)) {
                // if we send an associative array
                if (self::isAssoc($keyValues)) {
                    $result = $configKey;
                    foreach ($keyValues as $k => $v) {
                        $result = str_replace('[' . $k . ']', $v, $result);
                    }
                } else {
                    // if we send an indexed array
                    preg_match_all('/\[([^\]]*)\]/', $configKey, $vars, PREG_PATTERN_ORDER);
                    if (count($vars[0]) != count($keyValues)) {
                        throw new \Exception('Key ' . $keyName . ' contains a different number of values than the keyValues you gave.');
                    } else {
                        $result = $configKey;
                        for ($i = 0; $i < count($vars[0]); $i ++) {
                            $result = str_replace('[' . $vars[0][$i] . ']', $keyValues[$i]);
                        }
                    }
                }
            } else {
                // if we send only one value
                $result = preg_replace('/\[([^\]]*)\]/', $keyValues, $configKey);
            }
        }
        // if we still have [ in the key name, it means that we did not send the right parameters for keyValues
        if (strpos($result, '[')) {
            throw new \Exception('The cache key ' . $keyName . ' cannot be parsed with the given keyValues.');
        } else {
            $keyPrefix = ! empty($config->cache->keyPrefix) ? $config->cache->keyPrefix : '';
            return $keyPrefix . $result;
        }
    }

    public static function isCacheEnabled()
    {
        $config = Registry::get('config');
        return isset($config->cache->enabled) ? (bool) $config->cache->enabled : true;
    }

    private static function isAssoc($array)
    {
        return is_array($array) && array_diff_key($array, array_keys(array_keys($array)));
    }
    
    public static function getStorage($type)
    {
        if (! in_array($type, self::$instances)) {
            $config = Registry::get('config');
            if (isset($config->cache->$type->handler)) {
                $handler = $config->cache->$type->handler;
            } else {
                throw new \Exception('The ' . $type . ' cache storage is not defined in the config file');
            }
            if (isset($config->cache->$type->params)) {
                $params = $config->cache->$type->params;
            } else {
                $params = null;
            }
            if (isset($config->cache->$type->lifetime)) {
                $lifetime = $config->cache->$type->lifetime;
            } else {
                $lifetime = self::DEFAULT_LIFETIME;
            }
            $instance = self::factory($handler, $params, $lifetime);
            self::$instances[$type]=$instance;
        }
        return self::$instances[$type];
    }

    public static function factory($handler, $params, $lifetime = DEFAULT_LIFETIME)
    {
        $className = get_class() . '\\' . ucfirst($handler);
        return new $className($params, $lifetime);
    }
}


namespace Nf;

/**
 * Reads an .
 *
 *
 * ini file, handling sections, overwriting...
 *
 * @author Julien Ricard
 * @package Nf
 */
class Ini
{

    /**
     * Internal storage array
     *
     * @var array
     */
    private static $result = array();

    /**
     * Loads in the ini file specified in filename, and returns the settings in
     * it as an associative multi-dimensional array
     *
     * @param string $filename
     *            The filename of the ini file being parsed
     * @param boolean $process_sections
     *            By setting the process_sections parameter to TRUE,
     *            you get a multidimensional array, with the section
     *            names and settings included. The default for
     *            process_sections is FALSE
     * @param string $section_name
     *            Specific section name to extract upon processing
     * @throws Exception
     * @return array|boolean
     */
    public static function parse($filename, $process_sections = false, $section_name = null, $fallback_section_name = null)
    {
        
        // load the raw ini file
        // automatically caches the ini file if an opcache is present
        ob_start();
        include($filename);
        $str = ob_get_contents();
        ob_end_clean();
        $ini = parse_ini_string($str, $process_sections);
        
        // fail if there was an error while processing the specified ini file
        if ($ini === false) {
            return false;
        }
        
        // reset the result array
        self::$result = array();
        
        if ($process_sections === true) {
            // loop through each section
            foreach ($ini as $section => $contents) {
                // process sections contents
                self::processSection($section, $contents);
            }
        } else {
            // treat the whole ini file as a single section
            self::$result = self::processSectionContents($ini);
        }
        
        // extract the required section if required
        if ($process_sections === true) {
            if ($section_name !== null) {
                // return the specified section contents if it exists
                if (isset(self::$result[$section_name])) {
                    return self::bindArrayToObject(self::$result[$section_name]);
                } else {
                    if ($fallback_section_name !== null) {
                        return self::bindArrayToObject(self::$result[$fallback_section_name]);
                    } else {
                        throw new \Exception('Section ' . $section_name . ' not found in the ini file');
                    }
                }
            }
        }
        
        // if no specific section is required, just return the whole result
        return self::bindArrayToObject(self::$result);
    }

    /**
     * Process contents of the specified section
     *
     * @param string $section
     *            Section name
     * @param array $contents
     *            Section contents
     * @throws Exception
     * @return void
     */
    private static function processSection($section, array $contents)
    {
        // the section does not extend another section
        if (stripos($section, ':') === false) {
            self::$result[$section] = self::processSectionContents($contents);
            
            // section extends another section
        } else {
            // extract section names
            list ($ext_target, $ext_source) = explode(':', $section);
            $ext_target = trim($ext_target);
            $ext_source = trim($ext_source);
            
            // check if the extended section exists
            if (! isset(self::$result[$ext_source])) {
                throw new \Exception('Unable to extend section ' . $ext_source . ', section not found');
            }
            
            // process section contents
            self::$result[$ext_target] = self::processSectionContents($contents);
            
            // merge the new section with the existing section values
            self::$result[$ext_target] = self::arrayMergeRecursive(self::$result[$ext_source], self::$result[$ext_target]);
        }
    }

    /**
     * Process contents of a section
     *
     * @param array $contents
     *            Section contents
     * @return array
     */
    private static function processSectionContents(array $contents)
    {
        $result = array();
        
        // loop through each line and convert it to an array
        foreach ($contents as $path => $value) {
            // convert all a.b.c.d to multi-dimensional arrays
            $process = self::processContentEntry($path, $value);
            
            // merge the current line with all previous ones
            $result = self::arrayMergeRecursive($result, $process);
        }
        
        return $result;
    }

    /**
     * Convert a.b.c.d paths to multi-dimensional arrays
     *
     * @param string $path
     *            Current ini file's line's key
     * @param mixed $value
     *            Current ini file's line's value
     * @return array
     */
    private static function processContentEntry($path, $value)
    {
        $pos = strpos($path, '.');
        
        if ($pos === false) {
            return array(
                $path => $value
            );
        }
        
        $key = substr($path, 0, $pos);
        $path = substr($path, $pos + 1);
        
        $result = array(
            $key => self::processContentEntry($path, $value)
        );
        
        return $result;
    }

    private static function bindArrayToObject($array)
    {
        $return = new \StdClass();
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $return->$k = self::bindArrayToObject($v);
            } else {
                $return->$k = $v;
            }
        }
        return $return;
    }

    /**
     * Merge two arrays recursively overwriting the keys in the first array
     * if such key already exists
     *
     * @param mixed $a
     *            Left array to merge right array into
     * @param mixed $b
     *            Right array to merge over the left array
     * @return mixed
     */
    private static function arrayMergeRecursive($a, $b, $level = 0)
    {
        // merge arrays if both variables are arrays
        if (is_array($a) && is_array($b)) {
            // loop through each right array's entry and merge it into $a
            foreach ($b as $key => $value) {
                if (isset($a[$key])) {
                    if (is_int($key) && ! is_array($value)) {
                        $a[] = $value;
                    } else {
                        $a[$key] = self::arrayMergeRecursive($a[$key], $value, $level + 1);
                    }
                } else {
                    if ($key === 0) {
                        $a = array(
                            0 => self::arrayMergeRecursive($a, $value, $level + 1)
                        );
                    } else {
                        $a[$key] = $value;
                    }
                }
            }
        } else {
            // at least one of values is not an array
            $a = $b;
        }
        return $a;
    }

    /**
     * Replaces text in every value of the config, recursively
     *
     * @param string $search
     *            The string to search for
     * @param string $replace
     *            The string for replace with
     * @throws Exception
     * @return Ini
     */
    public static function deepReplace($ini, $search, $replace)
    {
        if (is_object($ini)) {
            foreach ($ini as $key => &$value) {
                $value = self::deepReplace($value, $search, $replace);
            }
        } else {
            $ini = str_replace($search, $replace, $ini);
            return $ini;
        }
        return $ini;
    }
}


/**
 * Nf Input
 *
 */

namespace Nf;

/**
 * Filtering and validating for forms, input parameters, etc
 *
 * @author Julien Ricard
 * @package Nf
 */
class Input
{

    const F_INTEGER = 'Int';

    const F_NATURAL = 'Natural';

    const F_NATURALNONZERO = 'NaturalNonZero';

    const F_ALPHA = 'Alpha';

    const F_ALPHANUM = 'AlphaNum';

    const F_NUMERIC = 'Numeric';

    const F_BASE64 = 'Base64';

    const F_REGEXP = 'Regexp';

    const F_STRING = 'String';

    const F_TRIM = 'Trim';

    const F_URL = 'Url';

    const F_STRIPTAGS = 'StripTags';

    const F_NULL = 'NullIfEmptyString';

    const F_BOOLEAN = 'Boolean';

    const V_INTEGER = 'Int';

    const V_NATURAL = 'Natural';

    const V_NATURALNONZERO = 'NaturalNonZero';

    const V_ALPHA = 'Alpha';

    const V_ALPHANUM = 'AlphaNum';

    const V_NUMERIC = 'Numeric';

    const V_BASE64 = 'Base64';

    const V_EQUALS = 'Equals';

    const V_REGEXP = 'Regexp';

    const V_REQUIRED = 'Required';

    const V_NOTEMPTY = 'NotEmpty';

    const V_GREATERTHAN = 'GreaterThan';

    const V_LESSTHAN = 'LessThan';

    const V_MINLENGTH = 'MinLength';

    const V_MAXLENGTH = 'MaxLength';

    const V_EXACTLENGTH = 'ExactLength';

    const V_EMAIL = 'Email';

    const V_MATCHES = 'Matches';

    const V_URL = 'Url';

    const V_DEFAULT = 'Default';

    const V_BOOLEAN = 'Boolean';

    /**
     * An array of every input parameter
     */
    private $_params = array();

    /**
     * An array of every filter instantiated for every input parameter
     */
    private $_filters = array();

    /**
     * An array of every validator instantiated for every input parameter
     */
    private $_validators = array();

    /**
     * An array of every parameter after filtering and validating
     */
    private $_fields = array();

    const REGEXP_ALPHA = '/[^a-z]*/i';

    const REGEXP_ALPHANUM = '/[^a-z0-9]*/i';

    const REGEXP_BASE64 = '%[^a-zA-Z0-9/+=]*%i';

    const REGEXP_INT = '/^[\-+]?[0-9]+$/';

    /**
     * The constructor to use while filtering/validating input
     *
     * @param array $params the input paramters to filter and validate
     * @param array $filters the list of filters for each parameter
     * @param array $validators the list of validators for each parameter
     *
     * @return Input
     */
    public function __construct(array $params, array $filters, array $validators)
    {
        $this->_params = $params;
        $this->_filters = $filters;
        $this->_validators = $validators;
        $this->_classMethods = get_class_methods(__CLASS__);
        $refl = new \ReflectionClass(__CLASS__);
        $this->_classConstants = $refl->getConstants();
    }

    /**
     * This method has to be called after specifying the parameters, filters and validators
     *
     * @param void
     *
     * @return bool returns true if every validator is validating, or false if any validator is not
     * This method always filters before validating
     */
    public function isValid()
    {
        // 1) filter
        $this->filter();
        // 2) validate
        return $this->validate();
    }

    /**
     * Filters every input parameter
     *
     * @return void
     */
    public function filter()
    {
        $this->metaFilterAndValidate('filter');
    }

    /**
     * Validates every input parameter
     *
     * @return void
     */
    public function validate()
    {
        return $this->metaFilterAndValidate('validate');
    }

    /**
     * Returns every incorrect field and the corresponding validator
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = array();
        foreach ($this->_fields as $fieldName => $values) {
            if (! $values['isValid']) {
                $invalidators = array();
                foreach ($values['validators'] as $validatorName => $validatorValue) {
                    if (! $validatorValue) {
                        $invalidators[] = $validatorName;
                    }
                }
                $messages[$fieldName] = $invalidators;
                unset($validator);
            }
            unset($fieldName);
            unset($values);
        }
        return $messages;
    }

    /**
     * Returns the original input parameters
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }
    
    /**
     * Returns every input parameter after content filtering
     *
     * @return array
     */
    public function getFilteredFields()
    {
        $filteredFields = array();
        foreach ($this->_fields as $fieldName => $data) {
            $filteredFields[$fieldName] = $data['value'];
        }
        return $filteredFields;
    }
    
    /**
     * Does all the work needed to filter and validate the input parameters
     *
     * @param string $metaAction ("filter" or "validate")
     * @return mixed
     */
    private function metaFilterAndValidate($metaAction)
    {
        if ($metaAction == 'filter') {
            $metaSource = $this->_filters;
        } elseif ($metaAction == 'validate') {
            $metaSource = $this->_validators;
            $isValid = true;
        }
        
        foreach ($metaSource as $paramName => $options) {
            if ($metaAction == 'filter') {
                $this->setField($paramName, (isset($this->_params[$paramName]) ? $this->_params[$paramName] : null));
            }
            
            if ($metaAction == 'validate') {
                if (! isset($this->_fields[$paramName])) {
                    $this->setField($paramName, (isset($this->_params[$paramName]) ? $this->_params[$paramName] : null));
                }
                $validators = array();
            }
            
            $options = (array) $options;
            
            foreach ($options as $option) {
                // optional parameter sent to the filter/validator
                // by default, it's not set
                unset($optionParameter);
                
                if (is_array($option)) {
                    $optionKeys = array_keys($option);
                    $optionValues = array_values($option);
                    
                    // call with an alias and a parameter: array('isValidId' => '\App\Toto::validateId', 22)
                    if (isset($option[0]) && $optionKeys[1] == 0) {
                        $optionName = $optionKeys[0];
                        $optionFunction = $optionValues[0];
                        $optionParameter = $optionValues[1];
                    } elseif ($this->isAssoc($option)) {
                        // call with an alias only : array('isValidId' => '\App\Toto::validateId'),
                        // or (if your name is Olivier D) call with the parameter as assoc : array('default' => 7),
                        $optionKeys = array_keys($option);
                        $optionValues = array_values($option);
                        
                        // if the value of the array is a function
                        if (isset($$optionFunction)) {
                            $optionName = $optionKeys[0];
                            $optionFunction = $optionValues[0];
                        } else {
                            // if the value of the array is a function (à la Olivier D)
                            $optionName = $optionKeys[0];
                            $optionFunction = $optionKeys[0];
                            $optionParameter = $optionValues[0];
                        }
                    } else {
                        // call with a parameter only : array('regexp', '/[a-z]*/i')
                        $optionName = $option[0];
                        $optionFunction = $option[0];
                        $optionParameter = $option[1];
                    }
                } else {
                    $optionName = $option;
                    $optionFunction = $option;
                }
                
                // if we want to validate against a method of a model
                $idx = strpos($optionFunction, '::');
                if ($idx !== false) {
                    // find (with autoload) the class and call the method
                    $className = substr($optionFunction, 0, $idx);
                    $methodName = substr($optionFunction, $idx + 2);
                    if ($metaAction == 'filter') {
                        if (isset($optionParameter)) {
                            $this->setField($paramName, $className::$methodName($this->_fields[$paramName]['value'], $optionParameter));
                        } else {
                            $this->setField($paramName, $className::$methodName($this->_fields[$paramName]['value']));
                        }
                    } elseif ($metaAction == 'validate') {
                        if (isset($optionParameter)) {
                            $ret = $className::$methodName($this->_fields[$paramName]['value'], $optionParameter, $this);
                        } else {
                            $ret = $className::$methodName($this->_fields[$paramName]['value'], null, $this);
                        }
                        // add the validator to the validators for this field
                        $isValid = $isValid && $ret;
                        $validators[$optionName] = $ret;
                    }
                } else {
                    // we will search for the function name in this class
                    $methodNameForOption = $metaAction . ucfirst($optionFunction);
                    // if the developer has used a shortname for the filter/validator
                    $methodNameFromConstants = (($metaAction == 'filter') ? 'F' : 'V') . '_' . strtoupper($optionFunction);
                    if (isset($this->_classConstants[$methodNameFromConstants])) {
                        $methodNameForOption = (($metaAction == 'filter') ? 'filter' : 'validate') . $this->_classConstants[$methodNameFromConstants];
                    }
                                        
                    if (in_array($methodNameForOption, $this->_classMethods)) {
                        if ($methodNameForOption == 'validateRequired') {
                            $ret = array_key_exists($paramName, $this->_params);
                        } else {
                            if (! isset($optionParameter)) {
                                $optionParameter = null;
                            }
                            if (is_array($this->_fields[$paramName]['value'])) {
                                if ($metaAction == 'filter') {
                                    foreach ($this->_fields[$paramName]['value'] as $paramKey => $paramValue) {
                                        $this->_fields[$paramName]['value'][$paramKey] = self::$methodNameForOption($this->_fields[$paramName]['value'][$paramKey], $optionParameter, $this);
                                    }
                                    unset($paramKey);
                                    unset($paramValue);
                                    $ret = $this->_fields[$paramName]['value'];
                                } else {
                                    $ret = true;
                                    foreach ($this->_fields[$paramName]['value'] as $paramKey => $paramValue) {
                                        $ret &= self::$methodNameForOption($this->_fields[$paramName]['value'][$paramKey], $optionParameter, $this);
                                    }
                                    unset($paramKey);
                                    unset($paramValue);
                                }
                            } else {
                                $ret = self::$methodNameForOption($this->_fields[$paramName]['value'], $optionParameter, $this);
                            }
                        }
                        if ($metaAction == 'filter') {
                            $this->setField($paramName, $ret);
                        }
                        // add the validator to the validators for this field
                        if ($metaAction == 'validate') {
                            // special case of the default value
                            if ($methodNameForOption == 'validateDefault') {
                                if (is_array($this->_fields[$paramName]['value'])) {
                                    foreach ($this->_fields[$paramName]['value'] as $paramKey => $paramValue) {
                                        if (empty($this->_fields[$paramName]['value'][$paramKey])) {
                                            $this->_fields[$paramName]['value'][$paramKey] = $optionParameter;
                                        }
                                    }
                                    unset($paramKey);
                                    unset($paramValue);
                                    $ret = true;
                                } else {
                                    if (empty($this->_fields[$paramName]['value'])) {
                                        $this->_fields[$paramName]['value'] = $optionParameter;
                                    }
                                    $ret = true;
                                }
                            }
                            $isValid = $isValid && $ret;
                            $validators[$optionName] = $ret;
                        }
                    } else {
                        throw new \Exception(__CLASS__ . ' hasn\'t a method called "' . $methodNameForOption . '"');
                    }
                }
            }
            unset($option);
            
            // we set the field after all the input value went through all validators
            if ($metaAction == 'validate') {
                // we test for each params if one of validators is not valid.
                $paramIsValid = true;
                foreach ($validators as $v) {
                    if ($v === false) {
                        $paramIsValid = false;
                        break;
                    }
                }
                $this->setField($paramName, false, $paramIsValid, $validators);
            }
        }
        if ($metaAction == 'validate') {
            return $isValid;
        }
    }
    
    /**
     * After filtering or validating, updates the field with additional data
     *
     * @param mixed $paramName the name of the input parameter
     * @param mixed $value the value after filtering
     * @param boolean $isValid is the field valid
     * @param array $validators sets the given validators for this parameter
     *
     * @return mixed
     */
    private function setField($paramName, $value = false, $isValid = null, $validators = null)
    {
        if (! isset($this->_fields[$paramName])) {
            $this->_fields[$paramName] = array(
                'originalValue' => (isset($this->_params[$paramName])) ? $this->_params[$paramName] : null,
                'value' => (isset($this->_params[$paramName])) ? $this->_params[$paramName] : null,
                'isValid' => true,
                'validators' => array()
            );
        }
        if ($value !== false) {
            $this->_fields[$paramName]['value'] = $value;
        }
        if ($isValid !== null) {
            $this->_fields[$paramName]['isValid'] = $this->_fields[$paramName]['isValid'] && $isValid;
        }
        if ($validators !== null) {
            $this->_fields[$paramName]['validators'] = $validators;
        }
    }

    /**
     * Returns the filtered value for any field given in the params
     *
     * @param mixed $paramName the name of the input parameter
     *
     * @return mixed
     */
    public function __get($paramName)
    {
        return $this->_fields[$paramName]['value'];
    }

    /**
     * Returns true or false if the input parameter was specified within the instanciation
     *
     * @param mixed $paramName the name of the input parameter
     *
     * @return boolean
     */
    public function __isset($paramName)
    {
        return isset($this->_fields[$paramName]['value']);
    }

    /**
     * Indicates if the array is an associative one or not
     *
     * @param array $paramName the name of the input parameter
     *
     * @return boolean
     */
    private function isAssoc($array)
    {
        return is_array($array) && array_diff_key($array, array_keys(array_keys($array)));
    }
    
    // ************************************************************************
    // filter functions
    // ************************************************************************

    /**
     * Used for filtering integer as string in json data
     *
     * @param mixed $value the value of the input parameter
     *
     * @return mixed
     */
    public static function filterNullIfEmptyString($value)
    {
        if ($value == '') {
            return null;
        }
        return $value;
    }

    /**
     * Parses the value as an integer
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterInt($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Parses the value as a natural (positive integer)
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterNatural($value)
    {
        return abs(self::filterInt($value));
    }

    /**
     * Parses the value as a strict natural (strictly positive integer)
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterNaturalNonZero($value)
    {
        $natural = self::filterNatural($value);
        if ($natural != 0) {
            return $natural;
        } else {
            return null;
        }
    }

    /**
     * Parses the value as alpha (letters only, no digit)
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterAlpha($value)
    {
        return preg_replace(self::REGEXP_ALPHA, '', $value);
    }

    /**
     * Parses the value as an alphanumeric
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterAlphaNum($value)
    {
        return preg_replace(self::REGEXP_ALPHANUM, '', $value);
    }

    /**
     * Parses the value as a base64 string
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterBase64($value)
    {
        return preg_replace(self::REGEXP_BASE64, '', $value);
    }

    /**
     * Parses the value as a boolean
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterBoolean($value)
    {
        $out = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return $out;
    }

    /**
     * Parses the value as a float
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterNumeric($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Removes the html tags from input
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterStripTags($value)
    {
        return strip_tags($value);
    }

    /**
     * Parses the value along a regexp
     *
     * @param mixed $value the value of the input parameter
     * @param string $regexp the regular expression to filter the input parameter to

     * @return mixed
     */
    public static function filterRegexp($value, $regexp)
    {
        return preg_replace($regexp, '', $value);
    }

    /**
     * Parses the value as a string
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterString($value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    }

    /**
     * Trims the string (default php behaviour)
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterTrim($value)
    {
        return trim($value);
    }

    /**
     * Filters the input string along the php's internal regexp for a url
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function filterUrl($value)
    {
        return filter_var($value, FILTER_SANITIZE_URL);
    }
    
    // ************************************************************************
    // validator functions
    // ************************************************************************
    
    /**
     * Validates that the input value is an integer
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateInt($value)
    {
        return (self::filterInt($value) == $value);
    }

    /**
     * Validates that the input value is a natural
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateNatural($value)
    {
        return (self::filterNatural($value) == $value);
    }

    /**
     * Validates that the input value is a positive integer greater than zero
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateNaturalNonZero($value)
    {
        return (self::filterNaturalNonZero($value) == $value);
    }

    /**
     * Validates that the input value is alpha (letters only)
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateAlpha($value)
    {
        return (bool) preg_match(self::REGEXP_ALPHA, $value);
    }

    /**
     * Validates that the input value is an alphanumeric string
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateAlphaNum($value)
    {
        return (bool) preg_match(self::REGEXP_ALPHANUM, $value);
    }

    /**
     * Validates that the input value is a base64 string
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateBase64($value)
    {
        return (bool) preg_match(self::REGEXP_BASE64, $value);
    }

    /**
     * Validates that the input value is a boolean
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateBoolean($value)
    {
        return (self::filterBoolean($value) == $value);
    }

    /**
     * Validates that the input value is a number (float)
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateNumeric($value, $compare, $instance)
    {
        return (self::filterNumeric($value) == $value);
    }

    /**
     * Validates that the input value equals the second parameter (no type checking)
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $check the value of the second parameter
     *
     * @return mixed
     */
    public static function validateEquals($value, $check)
    {
        return (bool) ($value == $check);
    }

    /**
     * Validates that the input value along a regular expression given as second parameter
     *
     * @param mixed $value the value of the input parameter
     * @param string $regexp the regular expression to validate to
     * @return mixed
     */
    public static function validateRegexp($value, $regexp)
    {
        return (bool) preg_match($regexp, $value);
    }

    /**
     * This method actually does not exist :) and should not be called directly
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateRequired($value)
    {
        throw new \Exception('This method should never be called');
    }

    /**
     * Validates that the input value is not an empty string
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateNotEmpty($value)
    {
        return ! (trim($value) === '');
    }

    /**
     * Validates that the input value is greater than the given parameter
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $compare the value to compare to
     * @return mixed
     */
    public static function validateGreaterThan($value, $compare)
    {
        return ($value >= $compare);
    }

    /**
     * Validates that the input value is lesser than the given parameter
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $compare the value to compare to
     * @return mixed
     */
    public static function validateLessThan($value, $compare)
    {
        return ($value <= $compare);
    }
    
    /**
     * Validates that the input value has a minimum length of the given parameter
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $compare the value to compare to
     * @return mixed
     */
    public static function validateMinLength($value, $compare)
    {
        return (mb_strlen($value) >= $compare);
    }

    /**
     * Validates that the input value has a maximum length of the given parameter
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $compare the value to compare to
     * @return mixed
     */
    public static function validateMaxLength($value, $compare)
    {
        return (mb_strlen($value) <= $compare);
    }

    /**
     * Validates that the input value has the exact length of the given parameter
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $compare the value to compare to
     * @return mixed
     */
    public static function validateExactLength($value, $compare)
    {
        return (mb_strlen($value) == $compare);
    }

    /**
     * Validates that the input value is an e-mail address
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateEmail($value)
    {
        $regexp = '/^[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,4}$/i';
        return (bool) preg_match($regexp, $value);
    }

    /**
     * Validates that the input value matches another input parameter
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $compareField the name of the input parameter to compare to
     * @param mixed $instance the instance of the Input object
     * @return mixed
     */
    public static function validateMatches($value, $compareField, $instance)
    {
        if (isset($instance->_fields[$compareField])) {
            return ($value == $instance->_fields[$compareField]['value']);
        }
    }

    /**
     * Validates that the input value is an url
     *
     * @param mixed $value the value of the input parameter
     * @return mixed
     */
    public static function validateUrl($value)
    {
        if (($url = parse_url($value)) && ! empty($url['scheme']) && ! empty($url['host'])) {
            return true;
        }
        return false;
    }

    /**
     * Sets the field to a default value if the input value is empty
     *
     * @param mixed $value the value of the input parameter
     * @param mixed $defaultValue the default value to assign if the input value is empty
     * @return mixed
     */
    public static function validateDefault($value, $defaultValue)
    {
        if (empty($value)) {
            return $defaultValue;
        }
        return $value;
    }
}



namespace Nf;

abstract class Date
{

   
    public static function dateFromMysql($date_origine, $return_time = false)
    {
        $date_output='';
        if ($return_time) {
            // sous la forme 2007-12-25 14:55:36 (datetime) => on renvoie tout reformaté
            if (preg_match('/^(\\d{4})\\-(\\d{2})\\-(\\d{2})\\ (\\d{2}):(\\d{2}):(\\d{2})$/', $date_origine, $matches)) {
                $date_output = preg_replace('/(\\d{4})\\-(\\d{2})\\-(\\d{2})\\ (\\d{2}):(\\d{2}):(\\d{2})/', '$3/$2/$1 $4:$5:$6', $matches[0]);
            } // sous la forme 2007-12-25 (date) => on renvoie une heure 00:00:00
            elseif (preg_match('/^(\\d{4})\\-(\\d{2})\\-(\\d{2})$/', $date_origine, $matches))
                $date_output = preg_replace('/(\\d{4})\\-(\\d{2})\\-(\\d{2})/', '$3/$2/$1 00:00:00', $matches[0]);
            // sous la forme 25/12/2007 14:55:36
            elseif (preg_match('/^(\\d{2})\/(\\d{2})\/(\\d{4}) (\\d{2}):(\\d{2}):(\\d{2})$/', $date_origine, $matches))
                $date_output = $date_origine;
            // sous la forme 25/12/2007 14:55 => on ajoute :00
            elseif (preg_match('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4}) (\\d{2}):(\\d{2})$/', $date_origine, $matches))
                $date_output = preg_replace('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4}) (\\d{2}):(\\d{2})$/', '$1/$2/$3 $4:$5:00', $matches[0]);
            // sous la forme 25/12/2007 => on ajoute 00:00:00
            elseif (preg_match('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4})$/', $date_origine, $matches))
                $date_output = preg_replace('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4})$/', '$1/$2/$3 00:00:00', $matches[0]);
        } else {
            // sous la forme 2007-12-25 (qqch)?
            if (preg_match('/(\\d{4})\\-(\\d{2})\\-(\\d{2})/', $date_origine, $matches)) {
                $date_output = preg_replace('/(\\d{4})\\-(\\d{2})\\-(\\d{2})/', '$3/$2/$1', $matches[0]);
            } // sous la forme 25/12/2007 => on ajoute 00:00:00
            elseif (preg_match('/(\\d{1,2})\/(\\d{1,2})\/(\\d{4})/', $date_origine, $matches))
                $date_output = preg_replace('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4})$/', '$1/$2/$3', $matches[0]);
        }
        if ($date_output!='') {
            return $date_output;
        } else {
            throw new \Exception('Erreur date_from_mysql : date non reconnue ' . $date_origine);
        }
    }
    
    public static function dateRange($first, $last, $step = '+1 day')
    {

        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);
        
        switch ($step) {
            case '+1 day':
                $format = 'Y-m-d';
                break;
            case '+1 month':
                $format = 'Y-m-01';
                break;
            case '+1 year':
                $format = 'Y-01-01';
                break;
            default:
                $format = 'Y-m-d';
        }
    
        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }
    
        return $dates;
    }

    public static function dateToMysql($date_origine, $return_time = false)
    {

        $date_output='';
        if ($return_time) {
            // sous la forme 25/12/2007 14:55:36 => on reformate tout
            if (preg_match('/^(\\d{2})\/(\\d{2})\/(\\d{4}) (\\d{2}):(\\d{2}):(\\d{2})$/', $date_origine, $matches)) {
                $date_output = preg_replace('/^(\\d{2})\/(\\d{2})\/(\\d{4}) (\\d{2}):(\\d{2}):(\\d{2})$/', '$3-$2-$1 $4:$5:$6', $matches[0]);
            } // sous la forme 25/12/2007 14:55 => on ajoute :00
            elseif (preg_match('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4}) (\\d{2}):(\\d{2})$/', $date_origine, $matches))
                $date_output = preg_replace('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4}) (\\d{2}):(\\d{2})$/', '$3-$2-$1 $4:$5:00', $matches[0]);
            // sous la forme 25/12/2007 => on ajoute 00:00:00
            elseif (preg_match('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4})$/', $date_origine, $matches))
                $date_output = preg_replace('/^(\\d{1,2})\/(\\d{1,2})\/(\\d{4})$/', '$3-$2-$1 00:00:00', $matches[0]);
            // sous la forme time() numérique
            elseif (is_numeric($date_origine)) {
                $date_output = date("Y-m-d H:i:s", $date_origine);
            } // sous la forme mysql datetime
            elseif (preg_match('/^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})$/', $date_origine, $matches))
                $date_output = preg_replace('/(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})/', '$1-$2-$3 $4:$5:$6', $matches[0]);
            // sous la forme mysql date
            elseif (preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})$/', $date_origine, $matches))
                $date_output = preg_replace('/(\\d{4})-(\\d{2})-(\\d{2})/', '$1-$2-$3 00:00:00', $matches[0]);
        } else {
            if (preg_match('/(\\d{1,2})\/(\\d{1,2})\/(\\d{4})/', $date_origine, $matches)) {
                $date_output = preg_replace('/(\\d{1,2})\/(\\d{1,2})\/(\\d{4})/', '$3-$2-$1', $matches[0]);
            } // sous la forme d'une timestamp numérique
            elseif (is_numeric($date_origine))
                $date_output = date("Y-m-d", $date_origine);
            // sous la forme mysql datetime
            elseif (preg_match('/^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})$/', $date_origine, $matches))
                $date_output = preg_replace('/(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})/', '$1-$2-$3', $matches[0]);
            // sous la forme mysql date
            elseif (preg_match('/^(\\d{4})-(\\d{2})-(\\d{2})$/', $date_origine, $matches))
                $date_output = preg_replace('/(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})/', '$1-$2-$3', $matches[0]);
        }
        if ($date_output!='') {
            return $date_output;
        } elseif ($date_output=='') {
            return null;
        } else {
            throw new \Exception('Erreur date_to_mysql : date non reconnue ' . $date_origine);
        }
    }
}



namespace Nf;

abstract class View extends Singleton
{

    protected static $_instance;

    private $_vars=array();

    private $_templateDirectory=null;
    private $_configDirectory=null;

    protected $_response;

    public function setResponse($response)
    {
        $this->_response=$response;
    }

    public static function factory($name)
    {
        $className='\\Nf\\View\\' . ucfirst($name);
        $view=$className::getInstance();
        return $view;
    }
}


namespace Nf;

use Nf\Localization;

/**
 * Bootstrap is responsible for instanciating the application in cli or web environment
 *
 * @package Nf
 *         
 */
class Bootstrap
{

    const DEFAULT_LOCALESELECTIONORDER = 'cookie,url,browser';

    private $_localeAndVersionFromUrlCache = null;

    private $_applicationNamespace = 'App';

    public function __construct($libraryPath, $applicationPath)
    {
        Registry::set('libraryPath', $libraryPath);
        Registry::set('applicationPath', $applicationPath);
    }

    public function initHttpEnvironment($inEnvironment = null, $inLocale = null, $inVersion = null)
    {
        $urlIni = Ini::parse(Registry::get('applicationPath') . '/configs/url.ini', true);
        Registry::set('urlIni', $urlIni);
        
        // environment : dev, test, prod
        // si il est défini en variable d'environnement
        if (empty($inEnvironment)) {
            if (getenv('environment') != '') {
                $environment = getenv('environment');
            } else {
                // sinon on lit le fichier url.ini
                if (! empty($_SERVER['HTTP_HOST'])) {
                    if (preg_match($urlIni->environments->dev->regexp, $_SERVER['HTTP_HOST'])) {
                        $environment = 'dev';
                    } elseif (preg_match($urlIni->environments->test->regexp, $_SERVER['HTTP_HOST'])) {
                        $environment = 'test';
                    } else {
                        $environment = 'prod';
                    }
                } else {
                    trigger_error('Cannot guess the requested environment');
                }
            }
        } else {
            // aucune vérification pour le moment
            $environment = $inEnvironment;
        }
        
        // locale
        if (! empty($urlIni->i18n->$environment->localeSelectionOrder)) {
            $localeSelectionOrder = $urlIni->i18n->$environment->localeSelectionOrder;
        } else {
            $localeSelectionOrder = self::DEFAULT_LOCALESELECTIONORDER;
        }
        $localeSelectionOrderArray = (array) explode(',', $localeSelectionOrder);
        // 3 possibilities : suivant l'url ou suivant un cookie ou suivant la langue du navigateur (fonctionnement indiqué dans i18n de url.ini)
        if (empty($inLocale)) {
            $locale = null;
            foreach ($localeSelectionOrderArray as $localeSelectionMethod) {
                if ($locale === null) {
                    switch ($localeSelectionMethod) {
                        case 'browser':
                            // on utilise la locale du navigateur et on voit si on a une correspondance
                            if (! empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                                // vérification de la syntaxe par une regexp
                                if (preg_match('/[a-z]+[_\-]?[a-z]+[_\-]?[a-z]+/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches)) {
                                    $locale = Localization::normalizeLocale($matches[0]);
                                    if (! empty($_SERVER['HTTP_HOST'])) {
                                        $httpHost = strtolower($_SERVER['HTTP_HOST']);
                                        list ($localeFromUrl, $versionFromUrl, $redirectToHost) = $this->getLocaleAndVersionFromUrl($httpHost, $urlIni);
                                    }
                                }
                            }
                            break;
                        case 'url':
                            // lire le fichier url.ini pour connaître la locale à utiliser
                            // en fonction de l'url
                            if (! empty($_SERVER['HTTP_HOST'])) {
                                $httpHost = strtolower($_SERVER['HTTP_HOST']);
                                list ($localeFromUrl, $versionFromUrl, $redirectToHost) = $this->getLocaleAndVersionFromUrl($httpHost, $urlIni);
                                if (! empty($localeFromUrl)) {
                                    $locale = $localeFromUrl;
                                }
                            }
                            break;
                        case 'cookie':
                            // lire le cookie pour connaître la locale à utiliser
                            if (! empty($_COOKIE['_nfLc'])) {
                                // vérification de la syntaxe par une regexp
                                if (preg_match('/[a-z]+[_\-]?[a-z]+[_\-]?[a-z]+/i', $_COOKIE['_nfLc'], $matches)) {
                                    $locale = Localization::normalizeLocale($matches[0]);
                                }
                            }
                            break;
                    }
                }
            }
        } else {
            $locale = $inLocale;
        }
        
        // if we did not find the locale, we use the default value
        if ($locale == null) {
            if (! empty($urlIni->i18n->defaultLocale)) {
                $locale = $urlIni->i18n->defaultLocale;
            } else {
                throw new \Exception('You have to set a default locale in url.ini');
            }
        }
        // we match the locale with the defined locale
        $localeFound = false;
        foreach ($urlIni->locales as $definedLocale => $definedLocaleNames) {
            if (! $localeFound) {
                if (strpos($definedLocaleNames, '|')) {
                    $arrDefinedLocaleNames = explode('|', $definedLocaleNames);
                    foreach ($arrDefinedLocaleNames as $localeNameOfArr) {
                        if (trim($localeNameOfArr) == trim($locale)) {
                            $locale = trim($definedLocale);
                            $localeFound = true;
                            break;
                        }
                    }
                } else {
                    if (trim($definedLocaleNames) == trim($locale)) {
                        $locale = trim($definedLocale);
                        $localeFound = true;
                        break;
                    }
                }
            }
        }
        
        // if the detected locale was not found in our defined locales
        if (! $localeFound) {
            // reverting to the default locale
            if (! empty($urlIni->i18n->defaultLocale)) {
                $locale = $urlIni->i18n->defaultLocale;
            } else {
                throw new \Exception('You have to set a default locale in url.ini');
            }
        }
        
        // version (web, mobile, cli...)
        if (empty($inVersion)) {
            if (! empty($versionFromUrl)) {
                $version = $versionFromUrl;
            } else {
                if (in_array('url', $localeSelectionOrderArray)) {
                    if (! empty($_SERVER['HTTP_HOST'])) {
                        $httpHost = strtolower($_SERVER['HTTP_HOST']);
                        list ($localeFromUrl, $versionFromUrl, $redirectToHost) = $this->getLocaleAndVersionFromUrl($httpHost, $urlIni);
                    }
                }
                if (! empty($versionFromUrl)) {
                    $version = $versionFromUrl;
                } else {
                    // on prend la version par défaut si elle est définie
                    if (isset($urlIni->i18n->defaultVersion)) {
                        $version = $urlIni->i18n->defaultVersion;
                    } else {
                        trigger_error('Cannot guess the requested version');
                    }
                }
            }
        } else {
            $version = $inVersion;
        }
        
        // on assigne les variables d'environnement et de language en registry
        Registry::set('environment', $environment);
        Registry::set('locale', $locale);
        Registry::set('version', $version);
        
        // on lit le config.ini à la section concernée par notre environnement
        $config = Ini::parse(Registry::get('applicationPath') . '/configs/config.ini', true, $locale . '_' . $environment . '_' . $version, 'common');
        Registry::set('config', $config);
        
        if (! empty($redirectToHost)) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: http://" . $redirectToHost . $_SERVER['REQUEST_URI']);
            return false;
        }
        
        // prevention contre l'utilisation de index.php
        if (isset($_SERVER['REQUEST_URI']) && in_array($_SERVER['REQUEST_URI'], array(
            'index.php',
            '/index.php'
        ))) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /");
            return false;
        }
        
        return true;
    }

    private function getLocaleAndVersionFromUrl($httpHost, $urlIni)
    {
        $redirectToHost = null;
        
        if (! empty($this->_localeAndVersionFromUrlCache)) {
            return $this->_localeAndVersionFromUrlCache;
        } else {
            $localeFromUrl = '';
            $versionFromUrl = '';
            
            $found = false;
            
            foreach ($urlIni->versions as $version_name => $prefix) {
                if (! $found) {
                    $redirectToHost = null;
                    foreach ($urlIni->suffixes as $locale => $suffix) {
                        if (! $found) {
                            if ($suffix != '') {
                                // the hosts names to test
                                $httpHostsToTest = array();
                                if ($prefix == '') {
                                    $httpHostsToTest = array(
                                        ltrim(str_replace('[version]', '', $suffix), '.')
                                    );
                                } else {
                                    if (strpos($prefix, '|') !== false) {
                                        $prefixes = array_values(explode('|', $prefix));
                                        $redirectToHost = ltrim(str_replace('..', '.', ($prefixes[0] == '<>' ? str_replace('[version]', '', $suffix) : str_replace('[version]', $prefixes[0] . '.', $suffix))), '.');
                                        foreach ($prefixes as $thePrefix) {
                                            // default empty prefix
                                            if ($thePrefix == '<>') {
                                                $httpHostsToTest[] = ltrim(str_replace('..', '.', str_replace('[version]', '', $suffix)), '.');
                                            } else {
                                                $httpHostsToTest[] = ltrim(rtrim(str_replace('..', '.', str_replace('[version]', $thePrefix . '.', $suffix)), '.'), '.');
                                            }
                                        }
                                    } else {
                                        $redirectToHost = null;
                                        $httpHostsToTest[] = ltrim(rtrim(str_replace('..', '.', str_replace('[version]', str_replace('<>', '', $prefix) . '.', $suffix)), '.'), '.');
                                    }
                                }
                            } else {
                                if (strpos($prefix, '|') !== false) {
                                    $prefixes = array_values(explode('|', $prefix));
                                    foreach ($prefixes as $thePrefix) {
                                        $httpHostsToTest[] = $thePrefix;
                                    }
                                } else {
                                    $httpHostsToTest[] = $prefix;
                                }
                            }
                            
                            // le test sur la chaîne reconstruite
                            foreach ($httpHostsToTest as $httpHostToTest) {
                                if ($httpHost == $httpHostToTest) {
                                    $localeFromUrl = $locale;
                                    
                                    $versionFromUrl = $version_name;
                                    if ($locale == '_default') {
                                        $localeFromUrl = $urlIni->i18n->defaultLocale;
                                    }
                                    if ($httpHostToTest == $redirectToHost) {
                                        $redirectToHost = null;
                                    }
                                    $found = true;
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    break;
                }
                unset($suffix);
            }
            
            unset($prefix);
            $this->_localeAndVersionFromUrlCache = array(
                $localeFromUrl,
                $versionFromUrl,
                $redirectToHost
            );
        }
        
        return array(
            $localeFromUrl,
            $versionFromUrl,
            $redirectToHost
        );
    }

    public function setApplicationNamespace($namespace)
    {
        $this->_applicationNamespace = $namespace;
        \Nf\Registry::set('applicationNamespace', $namespace);
    }

    public function initCliEnvironment()
    {
        $showUsage = true;
        
        if (isset($_SERVER['argv']) && $_SERVER['argc'] >= 2) {
            $urlIni = Ini::parse(Registry::get('applicationPath') . '/configs/url.ini', true);
            Registry::set('urlIni', $urlIni);
            
            $inEnvironment = 'dev';
            $inLocale = $urlIni->i18n->defaultLocale;
            $inVersion = 'cli';
            $inAction = array(
                'type' => 'default',
                'uri' => null
            );
            
            // default values
            Registry::set('environment', $inEnvironment);
            Registry::set('locale', $inLocale);
            Registry::set('version', $inVersion);
            
            $arrParams = array();
            
            $ac = 1;
            while ($ac < (count($_SERVER['argv']))) {
                switch ($_SERVER['argv'][$ac]) {
                    case '-e':
                    case '--environment':
                        $inEnvironment = $_SERVER['argv'][$ac + 1];
                        $ac += 2;
                        break;
                    case '-l':
                    case '--locale':
                        $inLocale = $_SERVER['argv'][$ac + 1];
                        $ac += 2;
                        break;
                    case '-v':
                    case '--version':
                        $inVersion = $_SERVER['argv'][$ac + 1];
                        $ac += 2;
                        break;
                    case '-a':
                    case '--action':
                        $inAction['uri'] = ltrim($_SERVER['argv'][$ac + 1], '/');
                        $ac += 2;
                        $showUsage = false;
                        break;
                    case '-m':
                    case '--make':
                        $inAction['uri'] = ltrim($_SERVER['argv'][$ac + 1], '/');
                        $inAction['type'] = 'make';
                        $showUsage = false;
                        $ac += 2;
                        break;
                    default:
                        $ac += 2;
                        break;
                }
            }
        }
        
        if (! $showUsage) {
            // on lit le config.ini à la section concernée par notre environnement
            $config = Ini::parse(Registry::get('applicationPath') . '/configs/config.ini', true, $inLocale . '_' . $inEnvironment . '_' . $inVersion);
            Registry::set('config', $config);
            
            // on assigne les variables d'environnement et de langue en registry
            Registry::set('environment', $inEnvironment);
            Registry::set('locale', $inLocale);
            Registry::set('version', $inVersion);
            
            return $inAction;
        } else {
            echo "Usage : module/controller/action";
            echo "\nOr : module/controller/action -variable1 value1 -variable2 value2 -variable3 value3";
            echo "\nOr : module/controller/action/variable1/value1/variable2/value2/variable3/value3";
            exit(04);
        }
    }

    function redirectForUserAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = new \Nf\UserAgent($_SERVER['HTTP_USER_AGENT']);
            // check the [redirections] section of the url.ini against the userAgent and redirect if we've been told to
            $urlIni = Registry::get('urlIni');
            foreach ($urlIni->redirections as $class => $forcedVersion) {
                if ($userAgent->checkClass($class)) {
                    if (! empty($forcedVersion)) {
                        // get the redirection URL according to the current class
                        $suffixes = (array) $urlIni->suffixes;
                        $versions = (array) $urlIni->versions;
                        if ($forcedVersion != $this->_localeAndVersionFromUrlCache[1]) {
                            $redirectionUrl = 'http://' . str_replace('[version]', $versions[$forcedVersion], $suffixes[$this->_localeAndVersionFromUrlCache[0]]);
                            $response = new Front\Response\Http();
                            $response->redirect($redirectionUrl, 301);
                            $response->sendHeaders();
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    function go()
    {
        if (php_sapi_name() == 'cli') {
            $inAction = $this->initCliEnvironment();
            
            $uri = $inAction['uri'];
            Error\Handler::setErrorDisplaying();
            $front = Front::getInstance();
            
            $request = new Front\Request\Cli($uri);
            $front->setRequest($request);
            
            $request->setAdditionalCliParams();
            
            $response = new Front\Response\Cli();
            
            $front->setResponse($response);
            $front->setApplicationNamespace($this->_applicationNamespace);

            $this->setTimezone();
            
            // routing
            $router = Router::getInstance();
            $front->setRouter($router);
            $router->addAllRoutes();
            
            // order in finding routes
            $router->setStructuredRoutes();
            
            $front->addModuleDirectory($this->_applicationNamespace, Registry::get('applicationPath') . '/application/cli/');
            $front->addModuleDirectory('library', Registry::get('libraryPath') . '/php/application/cli/');
            
            $labelManager = LabelManager::getInstance();
            $labelManager->loadLabels(Registry::get('locale'));
            
            $localization = Localization::getInstance();
            $localization->setLocale(Registry::get('locale'));
            
            if ($inAction['type'] == 'default') {
                $testDispatch = $front->dispatch();
                
                if ($testDispatch) {
                    if ($front->init() !== false) {
                        $front->launchAction();
                        $front->postLaunchAction();
                    }
                    $response->sendResponse();
                } else {
                    throw new \Exception('Action not found : ' . $uri);
                }
            } else {
                $front->parseParameters($inAction['uri']);
                $className = array();
                
                // $inAction['uri'] might be a class name with a static method like \Nf\Make::compress
                if ((strpos($inAction['uri'], '\\') !== false)) {
                    if (strpos($inAction['uri'], '::') === false) {
                        throw new \Exception('You have to specify the model and method to call, or just choose a method from the "Nf\Make" class.');
                    } else {
                        $uriSplit = explode('::', $inAction['uri']);
                        $className = $uriSplit[0];
                        $methodName = $uriSplit[1];
                        $obj = new $className();
                        $className::$methodName();
                    }
                } else {
                    // or an already integrated method in Nf\Make
                    $methodName = $inAction['uri'];
                    \Nf\Make::$methodName();
                }
            }
        } else {
            $this->initHttpEnvironment();
            
            Error\Handler::setErrorDisplaying();
            
            if (! $this->redirectForUserAgent()) {
                $front = Front::getInstance();
                $request = new Front\Request\Http();
                
                $front->setRequest($request);
                $response = new Front\Response\Http();
                $front->setResponse($response);
                $front->setApplicationNamespace($this->_applicationNamespace);
                
                $this->setTimezone();
                
                // routing
                $router = Router::getInstance();
                $front->setRouter($router);
                $router->addAllRoutes();
                
                // order in finding routes
                $router->setRoutesFromFiles();
                $router->setRootRoutes();
                $router->setStructuredRoutes();
                
                // modules directory for this version
                $front->addModuleDirectory($this->_applicationNamespace, Registry::get('applicationPath') . '/application/' . Registry::get('version') . '/');
                $front->addModuleDirectory('library', Registry::get('libraryPath') . '/php/application/' . Registry::get('version') . '/');
                
                $config = Registry::get('config');
                if (isset($config->session->handler)) {
                    $front->setSession(Session::start());
                }
                
                $labelManager = LabelManager::getInstance();
                $labelManager->loadLabels(Registry::get('locale'));
                
                $localization = Localization::getInstance();
                Localization::setLocale(Registry::get('locale'));
                
                $testDispatch = $front->dispatch();
                
                $requestIsClean = $request->sanitizeUri();
                
                if ($requestIsClean) {
                    if ($testDispatch === true) {
                        $request->setPutFromRequest();
                        
                        if (! $request->redirectForTrailingSlash()) {
                            if ($front->init() !== false) {
                                if (! $front->response->isRedirect()) {
                                    $front->launchAction();
                                }
                                if (! $front->response->isRedirect()) {
                                    $front->postLaunchAction();
                                }
                            }
                        }
                    } else {
                        Error\Handler::handleNotFound(404);
                    }
                } else {
                    Error\Handler::handleForbidden(403);
                }
                $response->sendResponse();
            }
        }
    }
    
    private function setTimezone() {
        $config = Registry::get('config');
        if(isset($config->date->timezone)) {
            try {
                date_default_timezone_set($config->date->timezone);
            }
            catch(\Exception $e) {
                echo 'timezone set failed (' . $config->date->timezone . ') is not a valid timezone';
            }
        }
    }
    
}



namespace Nf;

abstract class Image
{

    public static function generateThumbnail($imagePath, $thumbnailPath, $thumbnailWidth = 100, $thumbnailHeight = 100, $cut = false)
    {

        // load the original image
        $image = new \Imagick($imagePath);

        // undocumented method to limit imagick to one cpu thread
        $image->setResourceLimit(6, 1);

        // get the original dimensions
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        // the image will not be cut and the final dimensions will be within the requested dimensions
        if (!$cut) {
            // width & height : maximums and aspect ratio is maintained
            if ($thumbnailHeight==0) {
                $r=$width/$height;
                $thumbnailHeight=ceil($thumbnailWidth/$r);
                // create the thumbnail
                $image->thumbnailImage($thumbnailWidth, $thumbnailHeight);
            } elseif ($thumbnailWidth==0) {
                $r=$width/$height;
                $thumbnailWidth=ceil($thumbnailHeight*$r);
                // create thumbnail
                $image->thumbnailImage($thumbnailWidth, $thumbnailHeight);
            } else {
                // determine which dimension to fit to
                $fitWidth = ($thumbnailWidth / $width) < ($thumbnailHeight / $height);
                // create thumbnail
                $image->thumbnailImage(
                    $fitWidth ? $thumbnailWidth : 0,
                    $fitWidth ? 0 : $thumbnailHeight
                );
            }
        } else {
            if ($thumbnailWidth==0 || $thumbnailHeight==0) {
                throw new \Exception('Cannot generate thumbnail in "cut" mode when a dimension equals zero. Specify the dimensions.');
            }

            // scale along the smallest side
            $r=$width/$height;
            if ($r<1) {
                $newWidth=$thumbnailWidth;
                $newHeight=ceil($thumbnailWidth/$r);
            } else {
                $newWidth=ceil($thumbnailHeight*$r);
                $newHeight=$thumbnailHeight;
            }

            $image->thumbnailImage($newWidth, $newHeight);
            $width=$newWidth;
            $height=$newHeight;

            $workingImage=$image->getImage();
            $workingImage->contrastImage(50);
            $workingImage->setImageBias(10000);
            $kernel = array( 0,-1,0,
                             -1,4,-1,
                             0,-1,0);

            $workingImage->convolveImage($kernel);

            $x=0;
            $y=0;
            $sliceLength=16;

            while ($width-$x>$thumbnailWidth) {
                $sliceWidth=min($sliceLength, $width - $x - $thumbnailWidth);
                $imageCopy1=$workingImage->getImage();
                $imageCopy2=$workingImage->getImage();
                $imageCopy1->cropImage($sliceWidth, $height, $x, 0);
                $imageCopy2->cropImage($sliceWidth, $height, $width - $sliceWidth, 0);

                if (self::entropy($imageCopy1) < self::entropy($imageCopy2)) {
                    $x+=$sliceWidth;
                } else {
                    $width-=$sliceWidth;
                }
            }

            while ($height-$y>$thumbnailHeight) {
                $sliceHeight=min($sliceLength, $height - $y - $thumbnailHeight);
                $imageCopy1=$workingImage->getImage();
                $imageCopy2=$workingImage->getImage();
                $imageCopy1->cropImage($width, $sliceHeight, 0, $y);
                $imageCopy2->cropImage($width, $sliceHeight, 0, $height - $sliceHeight);

                if (self::entropy($imageCopy1) < self::entropy($imageCopy2)) {
                    $y+=$sliceHeight;
                } else {
                    $height-=$sliceHeight;
                }
            }

            $image->cropImage($thumbnailWidth, $thumbnailHeight, $x, $y);
        }

        if ($thumbnailPath!=null) {
            $image->writeImage($thumbnailPath);
            $image->clear();
            $image->destroy();
            return $thumbnailPath;
        } else {
            return $image;
        }

    }

    private static function entropy($image)
    {
        $image->setImageType(\Imagick::IMGTYPE_GRAYSCALE);
        $pixels=$image->getImageHistogram();
        $hist=array();
        foreach ($pixels as $p) {
            $color = $p->getColor();
            $theColor=$color['r'];
            if (!isset($hist[$theColor])) {
                $hist[$theColor]=0;
            }
            $hist[$theColor]+=$p->getColorCount();
        }
        // calculate the entropy from the histogram
        // cf http://www.mathworks.com/help/toolbox/images/ref/entropy.html
        $entropy=0;
        foreach ($hist as $c => $v) {
            $entropy-=$v*log($v, 2);
        }
        return $entropy;
    }
    
    public static function identifyImage($sourceFile)
    {
        return \Imagick::identifyImage($sourceFile);
    }
}



namespace Nf;

class LabelManager extends Singleton
{

    protected static $_instance;

    private $_labelsLoaded=false;
    private $_labels=array();

    // load the labels
    public function loadLabels($locale, $force = false)
    {
        if (!$this->_labelsLoaded || $force) {
            if (file_exists(\Nf\Registry::get('applicationPath') . '/labels/' . $locale . '.ini')) {
                $this->_labels=parse_ini_file(\Nf\Registry::get('applicationPath') . '/labels/' . $locale . '.ini', true);
                $this->_labelsLoaded=true;
            } else {
                throw new \Exception('Cannot load labels for this locale (' . $locale . ')');
            }
        }
    }

    public static function get($lbl)
    {
        $instance=self::$_instance;
        return $instance->_labels[$lbl];
    }

    public static function getAll($section = null)
    {
        $instance=self::$_instance;
        if ($section!=null) {
            return $instance->_labels[$section];
        } else {
            return $instance->_labels;
        }
    }

    public function __get($lbl)
    {
        return $this->_labels[$lbl];
    }
}



namespace Nf\Middleware;

trait Pre
{
    
}


namespace Nf\Middleware;

interface MiddlewareInterface
{

    public function execute();
}


namespace Nf\Profiler;

class File extends ProfilerAbstract
{

    protected $handle = null;
    
    protected $filepath = '/tmp/profiler.log';

    public function __construct($config)
    {
        if (isset($config['file'])) {
            $this->filepath = $config['file'];
        }
        $this->handle = fopen($this->filepath, 'a');
    }
}



namespace Nf\Middleware;

trait Post
{
    
}



namespace Nf\Session;

use Nf\Session;
use Nf\Cache;

class Memcached extends Session
{
    protected static $_instance=null;

    private $_lifeTime;
    private $_memcache;

    function __construct($params, $lifetime)
    {
        register_shutdown_function('session_write_close');
        $this->_memcache = new \Memcache;
        $this->_lifeTime = $lifetime;
        if (strpos($params->hosts, ',')>0) {
            $hosts=explode(',', $params->hosts);
            foreach ($hosts as $host) {
                $this->_memcache->addServer($host, $params->port);
            }
            unset($host);
        } else {
            $this->_memcache->addServer($params->hosts, $params->port);
        }
    }

    function open($savePath, $sessionName)
    {
        
    }

    function close()
    {
        $this->_memcache = null;
        return true;
    }

    function read($sessionId)
    {
        $sessionId = session_id();
        $cacheKey = Cache::getKeyName('session', $sessionId);
        if ($sessionId !== "") {
            return $this->_memcache->get($cacheKey);
        }
    }

    function write($sessionId, $data)
    {
        // This is called upon script termination or when session_write_close() is called, which ever is first.
        $cacheKey = Cache::getKeyName('session', $sessionId);
        $result = $this->_memcache->set($cacheKey, $data, false, $this->_lifeTime);
        return $result;
    }

    function destroy($sessionId)
    {
        $cacheKey=Cache::getKeyName('session', $sessionId);
        $this->_memcache->delete($cacheKey, 0);
        return true;
    }

    function gc($notUsedInMemcached)
    {
        return true;
    }
}


namespace Nf\View;

use Nf\View;

class Smarty extends View
{

    protected static $_instance;

    private $_smarty = null;

    const FILE_EXTENSION = '.tpl';

    private $_vars = array();

    protected function __construct()
    {
        require_once \Nf\Registry::get('libraryPath') . '/php/classes/Smarty/Smarty.class.php';
        $this->_smarty = new \Smarty();
        $front = \Nf\Front::getInstance();
        $this->setBasePath($front->getModuleName());
    }

    /**
     * Return the template engine object, if any
     * 
     * @return mixed
     */
    public function getEngine()
    {
        return $this->_smarty;
    }

    public function configLoad($filepath, $section = null)
    {
        $lang = \Nf\Registry::getInstance()->get('lang');
        $config_path = realpath(Registry::get('applicationPath') . '/configs/' . $lang . '/' . $front->getModuleName() . '/' . $filepath);
        $this->_smarty->config_load($config_path, $section);
    }

    /**
     * Assign a variable to the view
     *
     * @param string $key
     *            The variable name.
     * @param mixed $val
     *            The variable value.
     * @return void
     */
    public function __set($key, $val)
    {
        if ('_' == substr($key, 0, 1)) {
            throw new Exception('Setting private var is not allowed', $this);
        }
        if ($this->_smarty == null) {
            throw new Exception('Smarty is not defined', $this);
        }
        $this->_smarty->assignByRef($key, $val);
        return;
    }

    public function __get($key)
    {
        if ('_' == substr($key, 0, 1)) {
            throw new Exception('Setting private var is not allowed', $this);
        }
        if ($this->_smarty == null) {
            throw new Exception('Smarty is not defined', $this);
        }
        return $this->_smarty->getTemplateVars($key);
    }

    /**
     * Allows testing with empty() and
     * isset() to work
     *
     * @param string $key            
     * @return boolean
     */
    public function __isset($key)
    {
        $vars = $this->_smarty->getTemplateVars();
        return isset($vars[$key]);
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key            
     * @return void
     */
    public function __unset($key)
    {
        $this->_smarty->clearAssign($key);
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to
     * Zend_View either via {@link assign()} or
     * property overloading ({@link __get()}/{@link __set()}).
     *
     * @return void
     */
    public function clearVars()
    {
        $this->_smarty->clearAllAssign();
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name
     *            The script script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        $this->_response->addBodyPart($this->fetch($name));
    }

    public function fetch($name)
    {
        return $this->_smarty->fetch($name . self::FILE_EXTENSION);
    }

    public function setBasePath($path)
    {
        $config = \Nf\Registry::get('config');
        
        // configuration de Smarty
        $this->_smarty->setTemplateDir(array(
            \Nf\Registry::get('applicationPath') . '/application/' . \Nf\Registry::get('version') . '/' . $path . '/views/',
            \Nf\Registry::get('libraryPath') . '/php/application/' . \Nf\Registry::get('version') . '/' . $path . '/views/'
        ));
        
        // répertoire du cache Smarty
        $cacheDirectory = realpath(\Nf\Registry::get('applicationPath')) . '/cache/smarty/' . \Nf\Registry::get('version') . '/' . \Nf\Registry::get('locale') . '/' . $path . '/';
        // répertoire des templates compilés
        $compileDirectory = realpath(\Nf\Registry::get('applicationPath')) . '/cache/templates_c/' . \Nf\Registry::get('version') . '/' . \Nf\Registry::get('locale') . '/' . $path . '/';
        
        $configDirectory = realpath(\Nf\Registry::get('applicationPath')) . '/configs/' . \Nf\Registry::get('version') . '/' . \Nf\Registry::get('locale') . '/' . $path . '/';
        
        $pluginsDirectories = array(
            realpath(\Nf\Registry::get('applicationPath') . '/plugins/'),
            realpath(\Nf\Registry::get('libraryPath') . '/php/plugins/'),
            realpath(\Nf\Registry::get('libraryPath') . '/php/classes/Smarty/plugins/')
        );
        
        \Nf\File::mkdir($cacheDirectory, 0755, true);
        \Nf\File::mkdir($compileDirectory, 0755, true);
        
        $this->_smarty->setUseSubDirs(true);
        
        // répertoire de cache de smarty
        $this->_smarty->setCacheDir($cacheDirectory);
        // répertoire de compilation
        $this->_smarty->setCompileDir($compileDirectory);
        // répertoire des configs smarty des applis
        $this->_smarty->setConfigDir($configDirectory);
        // répertoire des plugins
        foreach ($pluginsDirectories as $pluginsDirectory) {
            $this->_smarty->addPluginsDir($pluginsDirectory);
        }
        
        $this->_smarty->left_delimiter = $config->view->smarty->leftDelimiter;
        $this->_smarty->right_delimiter = $config->view->smarty->rightDelimiter;
        
        // dev : we disable Smarty's caching
        if (\Nf\Registry::get('environment') == 'dev') {
            $this->_smarty->caching = false;
            $this->_smarty->force_compile = true;
            $this->_smarty->setCompileCheck(true);
        }
        
        // only one file generated for each rendering
        $this->_smarty->merge_compiled_includes = true;
        
        // send the registry to the view
        $this->_smarty->assign('_registry', \Nf\Registry::getInstance());
        
        // send the label Manager to the view
        $this->_smarty->assign('_labels', \Nf\LabelManager::getInstance());
        
        // $this->_smarty->testInstall();
    }
}


namespace Nf\Db;

class Expression
{

    protected $_expression;

    public function __construct($expression)
    {
        $this->_expression = (string) $expression;
    }

    public function __toString()
    {
        return $this->_expression;
    }
}



namespace Nf\Cache;

use \Nf\Cache;

class Memcached implements CacheInterface
{

    private $_memcache;

    function __construct($params)
    {
        $this->_memcache = new \Memcache;
        if (strpos($params->hosts, ',')>0) {
            $hosts=explode(',', $params->hosts);
            foreach ($hosts as $host) {
                $this->_memcache->addServer($host, $params->port);
            }
            unset($host);
        } else {
            $this->_memcache->addServer($params->hosts, $params->port);
        }
    }

    public function load($keyName, $keyValues = array())
    {
        if (Cache::isCacheEnabled()) {
            return $this->_memcache->get(Cache::getKeyName($keyName, $keyValues));
        } else {
            return false;
        }
    }

    public function save($keyName, $keyValues, $data, $lifetime = Cache::DEFAULT_LIFETIME)
    {
        if (Cache::isCacheEnabled()) {
            $result = $this->_memcache->set(Cache::getKeyName($keyName, $keyValues), $data, false, $lifetime);
            return $result;
        } else {
            return true;
        }
    }

    public function delete($keyName, $keyValues)
    {
        if (Cache::isCacheEnabled()) {
            $this->_memcache->delete(Cache::getKeyName($keyName, $keyValues), 0);
            return true;
        } else {
            return true;
        }
    }
}


namespace Nf\Cache;

interface CacheInterface
{

    public function load($keyName, $keyValues = array());

    public function save($keyName, $keyValues, $data, $lifetime = Cache::DEFAULT_LIFETIME);

    public function delete($keyName, $keyValues);
}



namespace Nf\Cache;

use \Nf\Cache;

class Apc implements CacheInterface
{

    function __construct($params)
    {

    }

    public function load($keyName, $keyValues = array())
    {
        if (Cache::isCacheEnabled()) {
            return apc_fetch(Cache::getKeyName($keyName, $keyValues));
        } else {
            return false;
        }
    }

    public function save($keyName, $keyValues, $data, $lifetime = Cache::DEFAULT_LIFETIME)
    {
        if (Cache::isCacheEnabled()) {
            return apc_store(Cache::getKeyName($keyName, $keyValues), $data, $lifetime);
        } else {
            return true;
        }
    }

    public function delete($keyName, $keyValues)
    {
        if (Cache::isCacheEnabled()) {
            return apc_delete(Cache::getKeyName($keyName, $keyValues));
        } else {
            return true;
        }
    }
}



namespace Nf\View;

use Nf\View;
use Nf\Front;
use Nf\Registry;
use Nf\Ini;

class Php extends View
{

    protected static $_instance;

    const FILE_EXTENSION='.php';

    private $_vars=array();

    private $_templateDirectory=null;
    private $_configDirectory=null;

    protected $_response;

    protected function __construct()
    {
        parent::__construct();
        $front=Front::getInstance();
        $this->setBasePath($front->getModuleName());
        // send the label Manager to the view
        $this->_vars['labels'] = \Nf\LabelManager::getInstance();
    }

    /**
     * Assign a variable to the view
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     */
    public function __set($key, $val)
    {
        $this->_vars[$key]=$val;
        return;
    }

    public function __get($key)
    {
        return $this->_vars[$key];
    }

    /**
     * Allows testing with empty() and
     * isset() to work
     *
     * @param string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->_vars[$key]);
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->_vars[$key]);
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to
     * Zend_View either via {@link assign()} or
     * property overloading ({@link __get()}/{@link __set()}).
     *
     * @return void
     */
    public function clearVars()
    {
        $this->_vars=array();
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        $this->_response->addBodyPart($this->fetch($name));
    }

     public function fetch($name)
     {
        // ob_start, require du tpl, ob_get_contents et return
        extract($this->_vars);
        ob_start();
        include($this->_templateDirectory . $name . self::FILE_EXTENSION);
        $content=ob_get_contents();
        ob_end_clean();
        return $content;
        }


        public function setBasePath($path)
        {
            $this->_templateDirectory = Registry::get('applicationPath') . '/application/' . Registry::get('version') . '/' . $path . '/views/';
            $this->_configDirectory = Registry::get('applicationPath') . '/configs/' . Registry::get('version') . '/' . Registry::get('locale') . '/' . $path . '/';
        }

        public function configLoad($filepath, $section = null)
        {
            // lire le fichier ini, ajouter aux variables
            $ini = Ini::parse($filepath);
            foreach ($ini as $key => $value) {
                $this->_vars[$key]=$value;
            }

        }
}


namespace Nf\Task;

class Manager
{

    protected $pool;

    function __construct()
    {
        $this->pool = array();
    }

    function addTask($task, $callbackFunction = null)
    {
        $this->pool[] = array(
            'task' => $task,
            'callback' => $callbackFunction
        );
    }

    function run()
    {
        foreach ($this->pool as $taskInfos) {
            $taskInfos['task']->fork();
        }
        
        while (1) {
            // echo "waiting\n";
            $pid = pcntl_wait($extra);
            if ($pid == - 1) {
                break;
            }
            // echo ": task done : $pid\n";
            $this->finishTask($pid);
        }
        // echo "processes done ; exiting\n";
        return;
    }

    function finishTask($pid)
    {
        $taskInfos = $this->pidToTaskInfos($pid);
        if ($taskInfos) {
            $taskInfos['task']->finish();
            $taskInfos['callback']();
        }
    }

    function pidToTaskInfos($pid)
    {
        foreach ($this->pool as $taskInfos) {
            if ($taskInfos['task']->pid() == $pid)
                return $taskInfos;
        }
        return false;
    }
}

namespace Nf\Profiler;

use \Nf\Registry;
use \Nf\Middleware\Post;

class Firephp extends ProfilerAbstract
{
    
    use Post;

    protected $totalDuration = 0;

    protected $firephp = false;
    
    protected $dbName = '';

    public function output()
    {
    }

    public function __construct($config)
    {
        
        require_once(realpath(Registry::get('libraryPath') . '/php/classes/FirePHPCore/FirePHP.class.php'));
        $this->firephp = \FirePHP::getInstance(true);
        
        $this->label = static::LABEL_TEMPLATE;
        
        $front = \Nf\Front::getInstance();
        
        $this->dbName = $config['name'];
        
        $front->registerMiddleware($this);
        
        $this->payload = array(
            array(
                'Duration',
                'Query',
                'Time'
            )
        );
    }
}


namespace Nf\Profiler;

use \Nf\Registry;

class ProfilerAbstract
{

    protected $label = array();
    protected $payload = array();
}


namespace Nf\Error;

class Handler extends \Exception
{

    static $lastError = array(
        'type' => 'error',
        'httpCode' => 0,
        'message' => '',
        'number' => 0,
        'file' => '',
        'line' => 0,
        'trace' => ''
    );

    public static function getLastError()
    {
        return self::$lastError;
    }

    public static function disableErrorHandler()
    {
        while (set_error_handler(create_function('$errno,$errstr', 'return false;'))) {
            // Unset the error handler we just set.
            restore_error_handler();
            // Unset the previous error handler.
            restore_error_handler();
        }
        // Restore the built-in error handler.
        restore_error_handler();
        
        while (set_exception_handler(create_function('$e', 'return false;'))) {
            // Unset the error handler we just set.
            restore_exception_handler();
            // Unset the previous error handler.
            restore_exception_handler();
        }
        // Restore the built-in error handler.
        restore_exception_handler();
    }

    public static function handleError($errno = null, $errstr = 0, $errfile = null, $errline = null)
    {
        $error_reporting = error_reporting();
        if ($error_reporting == 0) {
            return true; // developer used @ to ignore all errors
        }
        if (! ($error_reporting & $errno)) {
            return true; // developer asked to ignore this error
        }
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        return false;
    }

    public static function handleException($exception)
    {
        self::$lastError['httpCode'] = 500;
        self::$lastError['message'] = $exception->getMessage();
        
        if (method_exists($exception, 'getHttpStatus')) {
            self::$lastError['httpCode'] = $exception->getHttpStatus();
        }
        
        if (method_exists($exception, 'getErrors')) {
            self::$lastError['message'] = $exception->getErrors();
        }
        
        self::disableErrorHandler();
        self::$lastError['type'] = 'exception';
        self::$lastError['fullException'] = $exception;
        self::$lastError['number'] = 0;
        self::$lastError['file'] = $exception->getFile();
        self::$lastError['line'] = $exception->getLine();
        self::$lastError['trace'] = $exception->getTraceAsString();
        
        return self::displayAndLogError($exception);
    }

    public static function handleFatal()
    {
        self::disableErrorHandler();
        $last = error_get_last();
        if ($last != null) {
            self::$lastError['type'] = 'fatal';
            self::$lastError['httpCode'] = 500;
            self::$lastError['message'] = $last['message'];
            self::$lastError['number'] = $last['type'];
            self::$lastError['file'] = $last['file'];
            self::$lastError['line'] = $last['line'];
            self::$lastError['trace'] = '';
            return self::displayAndLogError();
        }
    }

    public static function handleForbidden($httpCode = 403, $friendlyMessage = '')
    {
        return self::handleHttpError('forbidden', $httpCode, $friendlyMessage);
    }

    public static function handleNotFound($httpCode = 404, $friendlyMessage = '')
    {
        return self::handleHttpError('notFound', $httpCode, $friendlyMessage);
    }

    private static function handleHttpError($type = 'notFound', $httpCode, $friendlyMessage = '')
    {
        self::$lastError['type'] = $type;
        self::$lastError['httpCode'] = $httpCode;
        self::$lastError['message'] = $friendlyMessage;
        self::$lastError['number'] = 0;
        self::$lastError['file'] = '';
        self::$lastError['line'] = 0;
        self::$lastError['trace'] = '';
        
        if (\Nf\Registry::isRegistered('config')) {
            $config = \Nf\Registry::get('config');
            $front = \Nf\Front::getInstance();
            $response = $front->getResponse();
            if ((isset($config->error->clearResponse) && $config->error->clearResponse) || (! isset($config->error->clearResponse))) {
                $response->clearBody();
                $response->clearBuffer();
            }
            try {
                $response->setHttpResponseCode($httpCode);
                $response->sendHeaders();
            } catch (Exception $e) {}
            
            $configName = strtolower($type);
            
            if (isset($config->error->displayMethod)) {
                if ($config->error->displayMethod == 'forward') {
                    // forward
                    if (! $front->forward($config->$configName->forward->module, $config->$configName->forward->controller, $config->$configName->forward->action)) {
                        ini_set('display_errors', 'On');
                        trigger_error('Error Handler failed to forward to the error controller.', E_USER_ERROR);
                    }
                    return true;
                } else {
                    $response->addBodyPart('http error: ' . $httpCode);
                }
            }
        }
    }

    public static function displayAndLogError($exception = null)
    {
        $err = self::getLastError();
        
        if (\Nf\Registry::isRegistered('config')) {
            $config = \Nf\Registry::get('config');
            $front = \Nf\Front::getInstance();
            $response = $front->getResponse();
            
            // optional error logging
            if ((isset($exception->doLog) && $exception->doLog || ! isset($exception->doLog))) {
                if (isset($config->error->logger->class) && strtolower($config->error->logger->class) != 'syslog') {
                    $className = $config->error->logger->class;
                    $logger = new $className();
                    if (! $logger->log($err)) {}
                } else {
                    $logger = new \Nf\Error\Logger\Syslog();
                    if (! $logger->log($err)) {}
                }
            }
            
            if ($response->isBinary()) {
                $response->setContentType('html');
            }
            if ((isset($config->error->clearResponse) && $config->error->clearResponse) || (! isset($config->error->clearResponse))) {
                $response->clearBody();
                $response->clearBuffer();
            }
            try {
                $response->setHttpResponseCode($err['httpCode']);
                $response->sendHeaders();
            } catch (Exception $e) {}
            
            if (isset($config->error->displayMethod)) {
                if ($config->error->displayMethod == 'forward') {
                    // forward
                    if (! $front->forward($config->error->forward->module, $config->error->forward->controller, $config->error->forward->action)) {
                        echo '** Nf: Cannot instantiate error module, printing error message **' . PHP_EOL . PHP_EOL;
                        $response->displayError($err);
                        echo PHP_EOL;
                    } else {
                        $response->sendResponse();
                    }
                    return true;
                } else {
                    if (method_exists($exception, 'display')) {
                        $response->setHttpResponseCode($err['httpCode']);
                        $exception->display();
                    } else {
                        // default : display (if xhr, use alternative display)
                        $response->displayError($err, $front->getRequest()
                            ->isXhr());
                    }
                }
            }
            
            return true;
        } else {
            @header('HTTP/1.1 500 Internal Server Error');
            print_r($err);
            error_log(print_r($err, true));
            return true;
        }
    }

    public static function setErrorHandler()
    {
        set_error_handler(array(
            'Nf\Error\Handler',
            'handleError'
        ));
        set_exception_handler(array(
            'Nf\Error\Handler',
            'handleException'
        ));
        register_shutdown_function(array(
            'Nf\Error\Handler',
            'handleFatal'
        ));
    }

    public static function setErrorDisplaying()
    {
        if (\Nf\Registry::isRegistered('config')) {
            $config = \Nf\Registry::get('config');
            if (isset($config->error->displayPHPErrors) && (strtolower($config->error->displayPHPErrors) == 'off' || $config->error->displayPHPErrors == 0)) {
                ini_set('display_errors', 0); // don't display the errors
            } else {
                ini_set('display_errors', 1); // display the errors
            }
        } else {
            ini_set('display_errors', 1);
        }
    }

    public static function recursiveArrayToString($arr)
    {
        if (! is_string($arr)) {
            return json_encode($arr);
        } else {
            return $arr;
        }
    }
}



namespace Nf\Session;

use Nf\Session;
use Nf\Db;
use Nf\Date;

class Mysqli extends Session
{
    protected static $_instance=null;

    private $_lifeTime;
    private $_connection;
    private $_params;

    function __construct($params, $lifetime)
    {
        register_shutdown_function('session_write_close');
        $db = Db::getConnection($params->db_adapter);
        $this->_params=$params;
        $this->_connection=$db;
        $this->_lifeTime = $lifetime;
    }

    function open($savePath, $sessionName)
    {
        
    }

    function close()
    {
        $this->_connection->closeConnection();
        return true;
    }

    function read($sessionId)
    {
        if ($sessionId !== '') {
            $sql="SELECT data FROM " . $this->_params->db_table . " WHERE id=" . $this->_connection->quote($sessionId) . " LIMIT 1";
            $res=$this->_connection->query($sql);
            if ($res->rowCount()>0) {
                $row=$res->fetch();
                return $row['data'];
            }
        }
    }

    function write($sessionId, $data)
    {
        // This is called upon script termination or when session_write_close() is called, which ever is first.
        $values=array(
            'data' => $data,
            'id' => $sessionId,
            'modified' => date('Y-m-d H:i:s'),
            'lifetime' => $this->_lifeTime
        );
        $sql="INSERT INTO " . $this->_params->db_table . " (id, data, modified, lifetime) VALUES(" . $this->_connection->quote($values['id']) . ", " . $this->_connection->quote($values['data']) . ", " . $this->_connection->quote($values['modified']) . ", " . $this->_connection->quote($values['lifetime']) . ")
				ON DUPLICATE KEY UPDATE data=" . $this->_connection->quote($values['data']) . ", modified=" . $this->_connection->quote($values['modified']);
        $this->_connection->query($sql);
        return true;
    }

    function destroy($sessionId)
    {
        $sql="DELETE FROM " . $this->_params->db_table . " WHERE id=" . $sessionId;
        $this->_connection->query($sql);
        return true;
    }

    function gc()
    {
        $sql="DELETE FROM " . $this->_params->db_table . " WHERE modified < DATE_SUB('" . date('Y-m-d H:i:s') . "',INTERVAL lifetime SECOND)";
        $this->_connection->query($sql);
        return true;
    }
}



namespace Nf\Front;

class Controller
{

    protected $_front;
    protected $_view;

    public function __construct($front)
    {
        $this->_front=$front;
    }

    public function getParams()
    {
        return $this->_front->getParams();
    }

    public function __get($var)
    {
        if ($var=='view') {
            if (is_null($this->_view)) {
                $this->_view=$this->_front->getView();
            }
            return $this->_view;
        } elseif ($var=='front') {
            return $this->_front;
        } elseif ($var=='session') {
            return $this->_front->getSession();
        } elseif ($var=='request') {
            return $this->_front->getRequest();
        } elseif ($var=='response') {
            return $this->_front->getResponse();
        } else {
            return $this->$var;
        }
    }
    
    public function getRequest()
    {
        return $this->_front->getRequest();
    }
    
    public function getResponse()
    {
        return $this->_front->getResponse();
    }

    // called after dispatch
    public function init()
    {
        return true;
    }

    // called after action
    public function postAction()
    {

    }

    public function getLabel($lbl)
    {
        return \Nf\LabelManager::get($lbl);
    }
}


namespace Nf\Front\Request;

class Http extends AbstractRequest
{

    protected $_params = array();

    private $_put = null;

    public function __construct()
    {
        if (! empty($_SERVER['REDIRECT_URL'])) {
            $uri = ltrim($_SERVER['REDIRECT_URL'], '/');
            if (! empty($_SERVER['REDIRECT_QUERY_STRING'])) {
                $uri .= '?' . $_SERVER['REDIRECT_QUERY_STRING'];
            }
        } else {
            $uri = ltrim($_SERVER['REQUEST_URI'], '/');
        }
        $this->_uri = $uri;
    }

    public function sanitizeUri()
    {
        // filter the uri according to the config of security.restrictCharactersInUrl
        // this option only allows us to use Alpha-numeric text, Tilde: ~, Period: ., Colon: :, Underscore: _, Dash: -
        $config = \Nf\Registry::get('config');
        if (isset($config->security->restrictCharactersInUrl) && $config->security->restrictCharactersInUrl) {
            if (preg_match('%[\w0-9~.,/@\-=:[\]{}|&?!\%]*%i', $this->_uri, $regs)) {
                if ($this->_uri == $regs[0]) {
                    return true;
                }
            }
            return false;
        } else {
            return true;
        }
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function isPost()
    {
        if ('POST' == $this->getMethod()) {
            return true;
        }
        return false;
    }

    public function isGet()
    {
        if ('GET' == $this->getMethod()) {
            return true;
        }
        return false;
    }

    public function isXhr()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function getParams()
    {
        $return = $this->_params;
        $paramSources = $this->getParamSources();
        if (in_array('_GET', $paramSources) && isset($_GET) && is_array($_GET)) {
            $return += $_GET;
        }
        if (in_array('_POST', $paramSources) && isset($_POST) && is_array($_POST)) {
            $return += $_POST;
        }
        return $return;
    }
    
    // get the string sent as put
    public function setPutFromRequest()
    {
        if ($this->_put === null) {
            if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
                $this->_put = file_get_contents("php://input");
            }
        } else {
            $this->_put = '';
        }
    }

    public function getPost($jsonDecode = 'assoc')
    {
        $post = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = file_get_contents("php://input");
        }
        
        if ($jsonDecode == 'assoc') {
            return json_decode($post, true);
        } else {
            return $post;
        }
    }

    public function getPut($jsonDecode = 'assoc')
    {
        if ($jsonDecode == 'assoc') {
            return json_decode($this->_put, true);
        } else {
            return $this->_put;
        }
    }
    
    // handle the redirection according to the trailing slash configuration
    public function redirectForTrailingSlash()
    {
        $config = \Nf\Registry::get('config');
        $redirectionUrl = false;
        $requestParams = '';
        $requestPage = '/' . $this->_uri;
        
        // we don't redirect for the home page...
        if ($requestPage != '/' && mb_strpos($requestPage, '/?') !== 0) {
            // the url without the params is :
            if (mb_strpos($requestPage, '?') !== false) {
                $requestParams = mb_substr($requestPage, mb_strpos($requestPage, '?'), mb_strlen($requestPage) - mb_strpos($requestPage, '?'));
                $requestPage = mb_substr($requestPage, 0, mb_strpos($requestPage, '?'));
            }
            
            if (isset($config->trailingSlash->needed) && $config->trailingSlash->needed==true) {
                if (mb_substr($requestPage, - 1, 1) != '/') {
                    $redirectionUrl = 'http://' . $_SERVER['HTTP_HOST'] . $requestPage . '/' . $requestParams;
                }
            } else {
                if (mb_substr($requestPage, - 1, 1) == '/') {
                    $redirectionUrl = 'http://' . $_SERVER['HTTP_HOST'] . rtrim($requestPage, '/') . $requestParams;
                }
            }
            
            if ($redirectionUrl !== false) {
                $response = new \Nf\Front\Response\Http();
                $response->redirect($redirectionUrl, 301);
                $response->sendHeaders();
                return true;
            }
        }
        
        return false;
    }
}


namespace Nf\Front\Response;

class Http extends AbstractResponse
{

    const SEPARATOR = 'separator';

    const SEPARATOR_ALT = "\n";

    const NEWLINE = '<br>';

    const NEWLINE_ALT = "\n";

    const TAB = " ";

    const TAB_ALT = " ";

    private $contentType = 'html';

    private $isBinaryContent = false;
    
    private $encoding = 'utf-8';

    protected $_headers = array();

    protected $_headersRaw = array();

    protected $_httpResponseCode = 200;

    protected $_isRedirect = false;

    protected function _normalizeHeader($name)
    {
        $filtered = str_replace(array(
            '-',
            '_'
        ), ' ', (string) $name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }

    public function setHeader($name, $value, $replace = false)
    {
        $this->canSendHeaders(true);
        $name = $this->_normalizeHeader($name);
        $value = (string) $value;
        
        if ($replace) {
            foreach ($this->_headers as $key => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$key]);
                }
            }
        }
        $this->_headers[] = array(
            'name' => $name,
            'value' => $value,
            'replace' => $replace
        );
        return $this;
    }

    public function redirect($url, $code = 302, $exit = true)
    {
        $this->canSendHeaders();
        $this->setHeader('Location', $url, true)->setHttpResponseCode($code);
        if ($exit) {
            $front = \Nf\Front::getInstance();
            $front->postLaunchAction();
            $this->clearBuffer();
            $this->clearBody();
            $this->sendHeaders();
            exit();
        }
        return $this;
    }

    public function isRedirect()
    {
        return $this->_isRedirect;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function clearHeaders()
    {
        $this->_headers = array();
        
        return $this;
    }

    public function clearHeader($name)
    {
        if (! count($this->_headers)) {
            return $this;
        }
        foreach ($this->_headers as $index => $header) {
            if ($name == $header['name']) {
                unset($this->_headers[$index]);
            }
        }
        return $this;
    }

    public function setRawHeader($value)
    {
        $this->canSendHeaders();
        if ('Location' == substr($value, 0, 8)) {
            $this->_isRedirect = true;
        }
        $this->_headersRaw[] = (string) $value;
        return $this;
    }

    public function clearRawHeaders()
    {
        $this->_headersRaw = array();
        return $this;
    }

    public function clearRawHeader($headerRaw)
    {
        if (! count($this->_headersRaw)) {
            return $this;
        }
        $key = array_search($headerRaw, $this->_headersRaw);
        unset($this->_headersRaw[$key]);
        return $this;
    }

    public function clearAllHeaders()
    {
        return $this->clearHeaders()->clearRawHeaders();
    }

    public function setHttpResponseCode($code)
    {
        if (! is_int($code) || (100 > $code) || (599 < $code)) {
            throw new \Exception('Invalid HTTP response code');
        }
        if ((300 <= $code) && (307 >= $code)) {
            $this->_isRedirect = true;
        } else {
            $this->_isRedirect = false;
        }
        $this->_httpResponseCode = $code;
        return $this;
    }

    public function canSendHeaders()
    {
        $headersSent = headers_sent($file, $line);
        if ($headersSent) {
            trigger_error('Cannot send headers; headers already sent in ' . $file . ', line ' . $line);
        }
        return ! $headersSent;
    }

    public function sendHeaders()
    {
        // Only check if we can send headers if we have headers to send
        if (count($this->_headersRaw) || count($this->_headers) || (200 != $this->_httpResponseCode)) {
            $this->canSendHeaders();
        } elseif (200 == $this->_httpResponseCode) {
            // Haven't changed the response code, and we have no headers
            return $this;
        }
        
        $httpCodeSent = false;
        
        foreach ($this->_headersRaw as $header) {
            if (! $httpCodeSent && $this->_httpResponseCode) {
                header($header, true, $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header);
            }
        }
        
        foreach ($this->_headers as $header) {
            if (! $httpCodeSent && $this->_httpResponseCode) {
                header($header['name'] . ': ' . $header['value'], $header['replace'], $this->_httpResponseCode);
                $httpCodeSent = true;
            } else {
                header($header['name'] . ': ' . $header['value'], $header['replace']);
            }
        }
        
        if (! $httpCodeSent) {
            header('HTTP/1.1 ' . $this->_httpResponseCode);
            $httpCodeSent = true;
        }
        
        return $this;
    }

    public function displayError($err, $isXhr = false)
    {
        // removes the cache headers if there is an error
        $this->setCacheable(0);
        if ($isXhr || $this->contentType!='html') {
            $this->setContentType('text');
            echo 'Error' . self::NEWLINE_ALT;
            echo strip_tags(self::displayErrorHelper($err, true));
            echo 'Error' . self::NEWLINE_ALT;
        } else {
            echo '<pre style="color:#555; line-height:16px;"><span style="color:red;">Error</span><br />';
            echo self::displayErrorHelper($err, false);
            echo '</pre>';
        }
    }

    protected static function boldText($text, $alternativeSeparator = false)
    {
        if ($alternativeSeparator) {
            return '* ' . $text . ' *';
        } else {
            return '<b>' . $text . '</b>';
        }
    }

    protected static function preFormatErrorText($beginOrEnd, $alternativeSeparator)
    {
        if ($alternativeSeparator) {
            return ($beginOrEnd == 0) ? '' : '';
        } else {
            return ($beginOrEnd == 0) ? '<pre>' : '</pre>';
        }
    }
    
    // sends header to allow the browser to cache the response a given time
    public function setCacheable($minutes)
    {
        $this->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + $minutes * 60) . ' GMT', true);
        $this->setHeader('Cache-Control', 'max-age=' . $minutes * 60, true);
        $this->setHeader('Pragma', 'public', true);
    }

    public function getContentType()
    {
        return $this->contentType;
    }
    
    public function isBinary()
    {
        return $this->isBinaryContent;
    }

    public function setContentType($type = 'html')
    {
        $this->contentType = $type;
        $this->isBinaryContent = false;
        $type = strtolower($type);
        switch ($type) {
            case 'atom':
                $this->setHeader('content-type', 'application/atom+xml');
                break;
            case 'css':
                $this->setHeader('content-type', 'text/css');
                break;
            case 'gif':
                $this->setHeader('content-type', 'image/gif');
                $this->isBinaryContent = true;
                break;
            case 'jpeg':
            case 'jpg':
                $this->setHeader('content-type', 'image/jpeg');
                $this->isBinaryContent = true;
                break;
            case 'png':
                $this->setHeader('content-type', 'image/png');
                $this->isBinaryContent = true;
                break;
            case 'js':
            case 'javascript':
                $this->setHeader('content-type', 'text/javascript');
                break;
            case 'json':
                $this->setHeader('content-type', 'application/json');
                break;
            case 'pdf':
                $this->setHeader('content-type', 'application/pdf');
                $this->isBinaryContent = true;
                break;
            case 'rss':
                $this->setHeader('content-type', 'application/rss+xml');
                break;
            case 'text':
                $this->setHeader('content-type', 'text/plain');
                break;
            case 'xml':
                $this->setHeader('content-type', 'text/xml');
                break;
            case 'html':
                $this->setHeader('content-type', 'text/html');
                break;
            default:
                throw new \Exception('This content type was not found: "' . $type . '"');
        }
    }
    
    public function getEncoding()
    {
        return $this->encoding;
    }
    
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    public static function colorText($text, $color, $alternativeSeparator = false)
    {
        if(!$alternativeSeparator) {
            return '<span style="color:' . $color . '">' . $text . '</span>';
        }
        else {
            return $text;
        }
    }

    protected static function escape($str)
    {
        return strip_tags($str);
    }
}


namespace Nf\Db\Adapter;

abstract class AbstractAdapter
{

    const PROFILER_NAMESPACE_ROOT = '\Nf\Db\Profiler';
    
    protected $_config = array();

    protected $_connection = null;

    protected $_autoQuoteIdentifiers = true;

    protected $_cache = false;
    
    protected $profiler = false;

    public function __construct($config)
    {
        if (! is_array($config)) {
            throw new \Exception('Adapter parameters must be in an array');
        }
        if (! isset($config['charset'])) {
            $config['charset'] = null;
        }
        $this->_config = $config;
    }

    public function getConnection()
    {
        $this->_connect();
        return $this->_connection;
    }

    public function query($sql)
    {
        $this->_connect();
        $res = new $this->_resourceClass($sql, $this);
        
        $beginTime = microtime(true);
        
        $res->execute();
        
        $endTime = microtime(true);
        
        if ($this->profiler) {
            $this->profiler->afterQuery($res, $sql, $endTime-$beginTime);
        }
        
        return $res;
    }

    public function fetchAll($sql)
    {
        $cacheKey = md5($sql) . 'All';
        
        if (($result = $this->_getCachedResult($cacheKey)) === false) {
            $stmt = $this->query($sql);
            $result = $stmt->fetchAll(\Nf\Db::FETCH_ASSOC);
            $this->_setCachedResult($cacheKey, $result);
        }
        $this->disableCache();
        return $result;
    }

    public function fetchAssoc($sql)
    {
        $cacheKey = md5($sql) . 'Assoc';
        
        if (($result = $this->_getCachedResult($cacheKey)) === false) {
            $stmt = $this->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\Nf\Db::FETCH_ASSOC)) {
                $tmp = array_values(array_slice($row, 0, 1));
                $result[$tmp[0]] = $row;
            }
            $this->_setCachedResult($cacheKey, $result);
        }
        $this->disableCache();
        return $result;
    }

    public function fetchRow($sql)
    {
        $cacheKey = md5($sql) . 'Row';
        
        if (($result = $this->_getCachedResult($cacheKey)) === false) {
            $stmt = $this->query($sql);
            $result = $stmt->fetch();
            $this->_setCachedResult($cacheKey, $result);
        }
        $this->disableCache();
        return $result;
    }

    public function fetchCol($sql)
    {
        $cacheKey = md5($sql) . 'Col';
        
        if (($result = $this->_getCachedResult($cacheKey)) === false) {
            $stmt = $this->query($sql);
            $result = $stmt->fetchAll(\Nf\Db::FETCH_COLUMN, 0);
            $this->_setCachedResult($cacheKey, $result);
        }
        $this->disableCache();
        return $result;
    }

    public function fetchOne($sql)
    {
        $cacheKey = md5($sql) . 'One';
        
        if (($result = $this->_getCachedResult($cacheKey)) === false) {
            $stmt = $this->query($sql);
            $result = $stmt->fetchColumn(0);
            $this->_setCachedResult($cacheKey, $result);
        }
        $this->disableCache();
        return $result;
    }

    public function fetchPairs($sql)
    {
        $cacheKey = md5($sql) . 'Pairs';
        
        if (($result = $this->_getCachedResult($cacheKey)) === false) {
            $stmt = $this->query($sql);
            $result = array();
            while ($row = $stmt->fetch(\Nf\Db::FETCH_NUM)) {
                $result[$row[0]] = $row[1];
            }
            $this->_setCachedResult($cacheKey, $result);
        }
        $this->disableCache();
        return $result;
    }

    public function beginTransaction()
    {
        $this->_beginTransaction();
        return $this;
    }

    public function commit()
    {
        $this->_commit();
        return $this;
    }

    public function rollback()
    {
        $this->_rollback();
        return $this;
    }

    public function enableCache($lifetime = \Nf\Cache::DEFAULT_LIFETIME, $cacheKey = null)
    {
        $this->_cache = array(
            'lifetime' => $lifetime
        );
        if ($cacheKey !== null) {
            $this->_cache['key'] = $cacheKey;
        }
        return $this;
    }

    public function disableCache()
    {
        $this->_cache = false;
        return $this;
    }

    protected function _getCachedResult($cacheKey)
    {
        if ($this->_cache !== false) {
            $cache = \Nf\Front::getInstance()->getCache('global');
            $cacheKey = isset($this->_cache['key']) ? $this->_cache['key'] : $cacheKey;
            return $cache->load('sql', $cacheKey);
        }
        return false;
    }

    protected function _setCachedResult($cacheKey, $result)
    {
        if ($this->_cache !== false) {
            $cache = \Nf\Front::getInstance()->getCache('global');
            $cacheKey = isset($this->_cache['key']) ? $this->_cache['key'] : $cacheKey;
            return $cache->save('sql', $cacheKey, $result, $this->_cache['lifetime']);
        }
        return false;
    }

    protected function _quote($value)
    {
        if (null === $value) {
            return 'NULL';
        } elseif (is_int($value) || $value instanceof \Nf\Db\Expression) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        } else {
            return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
        }
    }

    public function quote($value, $type = null)
    {
        $this->_connect();
        return $this->_quote($value);
    }

    public function quoteIdentifier($ident, $auto = false)
    {
        return $this->_quoteIdentifierAs($ident, null, $auto);
    }

    public function quoteColumnAs($ident, $alias, $auto = false)
    {
        return $this->_quoteIdentifierAs($ident, $alias, $auto);
    }

    protected function _quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ')
    {
        if (is_string($ident)) {
            $ident = explode('.', $ident);
        }
        if (is_array($ident)) {
            $segments = array();
            foreach ($ident as $segment) {
                $segments[] = $this->_quoteIdentifier($segment, $auto);
            }
            if ($alias !== null && end($ident) == $alias) {
                $alias = null;
            }
            $quoted = implode('.', $segments);
        } else {
            $quoted = $this->_quoteIdentifier($ident, $auto);
        }
        
        if ($alias !== null) {
            $quoted .= $as . $this->_quoteIdentifier($alias, $auto);
        }
        return $quoted;
    }

    protected function _quoteIdentifier($value, $auto = false)
    {
        if ($auto === false || $this->_autoQuoteIdentifiers === true) {
            $q = $this->getQuoteIdentifierSymbol();
            return ($q . str_replace("$q", "$q$q", $value) . $q);
        }
        return $value;
    }

    public function getQuoteIdentifierSymbol()
    {
        return '"';
    }

    public function setProfilerConfig($profilerConfig)
    {
        if ($profilerConfig!=null) {
            if (isset($profilerConfig['class'])) {
                if (!empty($profilerConfig['class'])) {
                    $profilerClass = $profilerConfig['class'];
                    unset($profilerConfig['class']);
                    $optionalConfig = $profilerConfig;
                    $profilerFullClassName = self::PROFILER_NAMESPACE_ROOT . '\\' . $profilerClass;
                    $profilerInstance = new $profilerFullClassName($optionalConfig);
                    $this->profiler = $profilerInstance;
                }
            } else {
                throw new \Exception('You must set the profiler class name in the config.ini file');
            }
        }
    }
    
    abstract protected function _connect();

    abstract public function isConnected();

    abstract public function closeConnection();

    abstract public function lastInsertId($tableName = null, $primaryKey = null);
}


namespace Nf\Db\Adapter;

use Nf\Localization;

class Mysqli extends AbstractAdapter
{

    protected $_resourceClass = '\\Nf\\Db\\Resource\\Mysqli';

    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }
        
        if (! extension_loaded('mysqli')) {
            throw new \Exception('The Mysqli extension is required for this adapter but the extension is not loaded');
        }
        
        if (isset($this->_config['port'])) {
            $port = (integer) $this->_config['port'];
        } else {
            $port = null;
        }
        
        $this->_connection = mysqli_init();
        
        if (! empty($this->_config['driver_options'])) {
            foreach ($this->_config['driver_options'] as $option => $value) {
                if (is_string($option)) {
                    // Suppress warnings here
                    // Ignore it if it's not a valid constant
                    $option = @constant(strtoupper($option));
                    if ($option === null) {
                        continue;
                    }
                }
                @mysqli_options($this->_connection, $option, $value);
            }
        }
        
        // Suppress connection warnings here.
        // Throw an exception instead.
        try {
            $_isConnected = mysqli_real_connect($this->_connection, $this->_config['hostname'], $this->_config['username'], $this->_config['password'], $this->_config['database'], $port);
        } catch (Exception $e) {
            $_isConnected = false;
        }
        
        if ($_isConnected === false || mysqli_connect_errno()) {
            $this->closeConnection();
            throw new \Exception(mysqli_connect_error());
        }
        
        if ($_isConnected && ! empty($this->_config['charset'])) {
            mysqli_set_charset($this->_connection, $this->_config['charset']);
        }
    }

    public function isConnected()
    {
        return ((bool) ($this->_connection instanceof mysqli));
    }

    public function closeConnection()
    {
        if ($this->isConnected()) {
            $this->_connection->close();
        }
        $this->_connection = null;
    }

    public function getQuoteIdentifierSymbol()
    {
        return "`";
    }

    protected function _quote($value)
    {
        if (null === $value) {
            return 'NULL';
        } elseif (is_int($value) || is_float($value) || $value instanceof \Nf\Db\Expression) {
            return $value;
        }
        $this->_connect();
        return "'" . $this->_connection->real_escape_string($value) . "'";
    }

    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        $mysqli = $this->_connection;
        return (string) $mysqli->insert_id;
    }

    public function insert($tableName, array $bind)
    {
        $sql = "INSERT INTO " . $this->quoteIdentifier($tableName, true);
        if (! count($bind)) {
            // allows for inserting a row without values to get an auto increment id
            $sql .= " VALUES()";
        } else {
            $sql .= " SET ";
            $insertFields = array();
            foreach ($bind as $key => $value) {
                $insertFields[] = $this->quoteIdentifier($key) . "=" . $this->quote($value);
            }
            $sql .= " " . implode(', ', $insertFields);
        }
        
        $res = new $this->_resourceClass($sql, $this);
        $res->execute();
        
        return $this->getConnection()->affected_rows;
    }

    public function insertIgnore($tableName, array $bind)
    {
        $sql = "INSERT IGNORE INTO " . $this->quoteIdentifier($tableName, true) . " SET ";
        $updateFields = array();
        foreach ($bind as $key => $value) {
            $updateFields[] = $this->quoteIdentifier($key) . "=" . $this->quote($value);
        }
        $sql .= " " . implode(', ', $updateFields);
        
        $res = new $this->_resourceClass($sql, $this);
        $res->execute();
        
        return $this->getConnection()->affected_rows;
    }

    public function upsert($tableName, array $bind, array $where)
    {
        $sql = "INSERT INTO " . $this->quoteIdentifier($tableName, true) . " SET ";
        $updateFields = array();
        foreach ($bind as $key => $value) {
            $updateFields[] = $this->quoteIdentifier($key) . "=" . $this->quote($value);
        }
        foreach ($where as $key => $value) {
            $updateFields[] = $this->quoteIdentifier($key) . "=" . $this->quote($value);
        }
        $sql .= " " . implode(', ', $updateFields);
        
        $sqlOnDuplicate = " ON DUPLICATE KEY UPDATE ";
        $onDuplicateFields = array();
        foreach ($bind as $key => $value) {
            $onDuplicateFields[] = $this->quoteIdentifier($key) . "=" . $this->quote($value);
        }
        $sqlOnDuplicate .= " " . implode(', ', $onDuplicateFields);
        
        $sql .= $sqlOnDuplicate;
        
        $res = new $this->_resourceClass($sql, $this);
        $res->execute();
        
        return $this->getConnection()->affected_rows;
    }

    public function update($tableName, array $bind, $where = '')
    {
        $sql = "UPDATE " . $this->quoteIdentifier($tableName, true) . " SET ";
        $updateFields = array();
        foreach ($bind as $key => $value) {
            $updateFields[] = $this->quoteIdentifier($key) . "=" . $this->quote($value);
        }
        $sql .= " " . implode(', ', $updateFields);
        if ($where != '') {
            $sql .= " WHERE " . $where;
        }
        
        $res = new $this->_resourceClass($sql, $this);
        $res->execute();
        
        return $this->getConnection()->affected_rows;
    }

    public function delete($tableName, $where = '')
    {
        if ($where != '') {
            $sql = "DELETE FROM " . $this->quoteIdentifier($tableName, true) . " WHERE " . $where;
        } else {
            $sql = "TRUNCATE TABLE" . $this->quoteIdentifier($tableName, true);
        }
        
        $res = new $this->_resourceClass($sql, $this);
        $res->execute();
        
        return $this->getConnection()->affected_rows;
    }

    function cleanConnection()
    {
        $mysqli = $this->_connect();
        $mysqli = $this->_connection;
        
        while ($mysqli->more_results()) {
            if ($mysqli->next_result()) {
                $res = $mysqli->use_result();
                if (is_object($res)) {
                    $res->free_result();
                }
            }
        }
    }

    public function multiQuery($queries)
    {
        $mysqli = $this->_connect();
        $mysqli = $this->_connection;
        
        if (is_array($queries)) {
            $queries = implode(';', $queries);
        }
        
        $ret = $mysqli->multi_query($queries);
        
        if ($ret === false) {
            throw new \Exception($mysqli->error);
        }
    }

    public static function formatDate($inShortFormatDateOrTimestamp, $hasMinutes = false)
    {
        $tstp = Localization::dateToTimestamp($inShortFormatDateOrTimestamp, Localization::SHORT, ($hasMinutes ? Localization::SHORT : Localization::NONE), true);
        if ($hasMinutes) {
            return date('Y-m-d', $tstp);
        } else {
            return date('Y-m-d H:i:s', $tstp);
        }
    }

    /**
     * Begin a transaction.
     *
     * @return void
     */
    protected function _beginTransaction()
    {
        $this->_connect();
        $this->_connection->autocommit(false);
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    protected function _commit()
    {
        $this->_connect();
        $this->_connection->commit();
        $this->_connection->autocommit(true);
    }

    /**
     * Roll-back a transaction.
     *
     * @return void
     */
    protected function _rollBack()
    {
        $this->_connect();
        $this->_connection->rollback();
        $this->_connection->autocommit(true);
    }
}


namespace Nf\Db\Profiler;

class File extends \Nf\Profiler\File
{
    
    public function afterQuery($resource, $sql, $duration)
    {
        fputs($this->handle, date('Y-m-d H:i:s') . PHP_EOL . str_replace(array(
            "\n",
            "\t"
        ), ' ', $sql) . PHP_EOL . round($duration * 10000, 2) . ' ms' . PHP_EOL . '--' . PHP_EOL);
    }
}


namespace Nf\Error\Exception;

class ClientException extends \Exception
{
}


namespace Nf\Db\Profiler;

class Firephp extends \Nf\Profiler\Firephp
{

    const LABEL_TEMPLATE = '#dbName# (#nbQueries# @ #totalDuration# ms)';

    public function afterQuery($resource, $sql, $duration)
    {
        $this->payload[] = array(
            '' . round($duration * 10000, 2),
            str_replace(array(
                "\n",
                "\t"
            ), ' ', $sql),
            date('Y-m-d H:i:s')
        );
        
        $this->totalDuration += $duration * 10000;
    }
    
    // outputs the payload
    public function execute()
    {
        $this->label = str_replace(array(
            '#dbName#',
            '#nbQueries#',
            '#totalDuration#'
        ), array(
            $this->dbName,
            count($this->payload) - 1,
            round($this->totalDuration, 2)
        ), $this->label);
        
        $this->firephp->fb(array(
            $this->label,
            $this->payload
        ), \FirePHP::TABLE);
    }
}


namespace Nf\Error\Exception;

class Http extends \Exception
{

    protected $_httpStatus = 500;
    
    public $doLog = true;

    public function getHttpStatus()
    {
        return $this->_httpStatus;
    }
    
    public function display()
    {
        $front = \Nf\Front::getInstance();
        $response = $front->getResponse();
        $response->sendResponse();
    }
}


namespace Nf\Error\Logger;

use \Nf\Registry;
use \Nf\Error\Handler;

class Gelf
{

    public function log($err)
    {
        $config = Registry::get('config');
        
        // We need a transport - UDP via port 12201 is standard.
        $transport = new \Gelf\Transport\UdpTransport($config->error->logger->gelf->ip, $config->error->logger->gelf->port, \Gelf\Transport\UdpTransport::CHUNK_SIZE_LAN);
        
        // While the UDP transport is itself a publisher, we wrap it in a real Publisher for convenience
        // A publisher allows for message validation before transmission, and it calso supports to send messages
        // to multiple backends at once
        $publisher = new \Gelf\Publisher();
        $publisher->addTransport($transport);
        
        $fullMessage = \Nf\Front\Response\Cli::displayErrorHelper($err);
        
        // Now we can create custom messages and publish them
        $message = new \Gelf\Message();
        $message->setShortMessage(Handler::recursiveArrayToString($err['message']))
            ->setLevel(\Psr\Log\LogLevel::ERROR)
            ->setFile($err['file'])
            ->setLine($err['line'])
            ->setFullMessage($fullMessage);
        
        if (php_sapi_name() == 'cli') {
            global $argv;
            $message->setAdditional('url', 'su ' . $_SERVER['LOGNAME'] . ' -c "php ' . Registry::get('applicationPath') . '/html/' . implode(' ', $argv) . '"');
        } else {
            $message->setAdditional('url', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        }
        
        if (isset($config->error->logger->additionals)) {
            foreach ($config->error->logger->additionals as $additionalName => $additionalValue) {
                $message->setAdditional($additionalName, $additionalValue);
            }
        }
        
        if ($publisher->publish($message)) {
            return true;
        } else {
            return false;
        }
    }
}


namespace Nf\Front\Response;

abstract class AbstractResponse
{

    protected $_bodyParts = array();

    public function addBodyPart($bodyPart)
    {
        $this->_bodyParts[] = $bodyPart;
    }

    public function clearBody()
    {
        $this->_bodyParts = array();
    }

    public function clearBuffer()
    {
        $maxObLevel = \Nf\Front::$obLevel;
        $curObLevel = ob_get_level();
        if ($curObLevel > $maxObLevel) {
            do {
                ob_end_clean();
                $curObLevel = ob_get_level();
            } while ($curObLevel > $maxObLevel);
        }
    }

    public function output()
    {
        echo implode('', $this->_bodyParts);
    }

    public function sendResponse()
    {
        $this->sendHeaders();
        $this->output();
    }

    public function setHttpResponseCode($code)
    {}

    public function isBinary()
    {
        return false;
    }

    public function getContentType()
    {
        return false;
    }

    public function setContentType($type = 'html')
    {}

    public static function displayErrorHelper($err, $alternativeSeparator = false)
    {
        $output = '';
        
        $separator = $alternativeSeparator ? static::NEWLINE_ALT : static::NEWLINE;
        
        if ($err['type'] != 'fatal') {
            $output .= static::colorText($err['type'] . ': ' . \Nf\Error\Handler::recursiveArrayToString(static::escape($err['message'])), 'red');
            $output .= $separator;
            $output .= static::colorText($err['file'] . ' (line ' . $err['line'] . ')', 'green', $alternativeSeparator);
            $output .= $separator . '-----' . $separator;
            $output .= implode($separator, self::getFileSample($err['file'], $err['line']));
            $output .= $separator . '-----' . $separator;
            $trace = $err['fullException']->getTrace();
            foreach ($trace as $entry) {
                $output .= self::stackTracePrintEntry($entry);
                if(isset($entry['file']) && isset($entry['line'])) {
                    $output .= '-----' . $separator;
                    $output .= implode($separator, self::getFileSample($entry['file'], $entry['line'], 2));
                    $output .= $separator . '-----' . $separator;
                }
            }
        } else {
            $output .= $err['message'] . $separator;
            $output .= static::preFormatErrorText(0, $alternativeSeparator);
            $output .= self::stackTracePrintEntry($err, 2, $alternativeSeparator);
            $output .= static::preFormatErrorText(1, $alternativeSeparator);
        }
        return $output;
    }

    protected static function stackTracePrintEntry($entry, $displayArgsType = 1, $alternativeSeparator = false)
    {
        $output = '';
        
        if (isset($entry['file'])) {
            $output .= static::colorText($entry['file'] . ' (line ' . $entry['line'] . ')', 'green', $alternativeSeparator);
            $output .= ($alternativeSeparator ? static::NEWLINE_ALT : static::NEWLINE);
        }
        if (isset($entry['class'])) {
            if ($entry['class'] != 'Nf\Error\Handler') {
                $output .= 'call: ' . $entry['class'] . '::';
                if (isset($entry['function'])) {
                    $output .= $entry['function'];
                    $output .= ($alternativeSeparator ? static::NEWLINE_ALT : static::NEWLINE);
                }
            }
        }
        
        if ($displayArgsType > 0 && isset($entry['args']) && count($entry['args'])) {
            $output .= static::stackTracePrintArgs($entry['args'], $alternativeSeparator);
            $output .= ($alternativeSeparator ? static::NEWLINE_ALT : static::NEWLINE);
        }
        return $output;
    }

    protected static function stackTracePrintArgs($args, $alternativeSeparator)
    {
        $output = '';
        $output .= 'arguments: ';
        $out = array();
        
        if (is_array($args)) {
            foreach ($args as $k => $v) {
                $forOut = '';
                $forOut = $k . ' = ';
                if (is_array($v) || is_object($v)) {
                    $strV = print_r($v, true);
                    if (strlen($strV) > 50) {
                        $strV = substr($strV, 0, 50) . '...';
                    }
                    $forReplace = [
                        "\n",
                        "\r"
                    ];
                    $forOut .= str_replace($forReplace, '', $strV);
                } else {
                    $forOut .= $v;
                }
                $out[] = $forOut;
            }
        }
        
        $output .= static::escape($alternativeSeparator ? static::TAB_ALT : static::TAB . '[ ' . implode(', ', $out) . ' ]');
        return $output;
    }

    protected static function getFileSample($filename, $line, $linesAround = 3)
    {
        $file = new \SplFileObject($filename);
        $currentLine = $line - $linesAround - 1;
        $sample = [];
        while ($currentLine >= 0 && ! $file->eof() && $currentLine < $line + $linesAround) {
            $file->seek($currentLine);
            $currentText = trim($file->current(), "\n\r");
            if ($currentLine == $line - 1) {
                $sample[] = $currentText;
            } else {
                $sample[] = static::colorText($currentText, 'bold_gray');
            }
            
            $currentLine ++;
        }
        return $sample;
    }
}


namespace Nf\Front\Response;

class Cli extends AbstractResponse
{

    const SEPARATOR = "\r\n";

    const NEWLINE = "\r\n";

    const TAB = "\t";

    public function setHeader($name, $value, $replace = false)
    {
        return true;
    }

    public function redirect($url, $code = 302)
    {
        throw new Exception('cannot redirect in cli version');
    }

    public function clearHeaders()
    {
        return false;
    }

    public function canSendHeaders()
    {
        return true;
    }

    public function sendHeaders()
    {
        return false;
    }

    public function displayError($err, $isXhr = false)
    {
        echo static::colorText('Error', 'red') . static::NEWLINE;
        echo self::displayErrorHelper($err);
    }

    protected static function boldText($text)
    {
        return self::colorText($text, 'green');
    }

    protected static function preFormatErrorText($beginOrEnd)
    {
        return '';
    }

    public static function colorText($text, $color = 'black')
    {
        $colors = array(
            'black' => '0;30',
            'dark_gray' => '1;30',
            'red' => '0;31',
            'bold_red' => '1;31',
            'green' => '0;32',
            'bold_green' => '1;32',
            'brown' => '0;33',
            'yellow' => '1;33',
            'blue' => '0;34',
            'bold_blue' => '1;34',
            'purple' => '0;35',
            'bold_purple' => '1;35',
            'cyan' => '0;36',
            'bold_cyan' => '1;36',
            'white' => '1;37',
            'bold_gray' => '0;37'
        );
        if (isset($colors[$color])) {
            return "\033[" . $colors[$color] . 'm' . $text . "\033[0m";
        }
    }

    protected static function escape($str)
    {
        return $str;
    }
}


namespace Nf\Front\Request;

class Cli extends AbstractRequest
{
    
    // cli parameters that are already used by the framework
    const RESERVED_CLI_PARAMS = 'e,environment,l,locale,a,action,m,make';

    protected $_params = array();

    public function __construct($uri)
    {
        $this->_uri = $uri;
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function isXhr()
    {
        return false;
    }
    
    // sets additional parameters from the command line from the arguments
    public function setAdditionalCliParams()
    {
        $reservedParams = explode(',', self::RESERVED_CLI_PARAMS);
        
        $params = [];
        
        $ac = 1;
        while ($ac < (count($_SERVER['argv']))) {
            $paramName = substr($_SERVER['argv'][$ac], 1);
            if (! in_array($paramName, $reservedParams)) {
                $params[$paramName] = $_SERVER['argv'][$ac + 1];
            }
            $ac += 2;
        }
        
        foreach ($params as $param => $value) {
            $this->setParam($param, $value);
        }
    }

    public function getParams()
    {
        $return = $this->_params;
        return $return;
    }
}



namespace Nf\Front\Request;

class AbstractRequest
{

    private $_paramSources=array('_GET', '_POST');

    public function setParam($name, $value)
    {
        $this->_params[$name]=$value;
    }

    public function getParams()
    {
        return $this->_params;
    }

    protected function getParamSources()
    {
        return $this->_paramSources;
    }
}


namespace Nf\Error\Logger;

use \Nf\Registry;

class Syslog
{

    public function log($err)
    {
        if(!is_string($err['message'])) {
            $err['message'] = print_r($err['message'], true);
        }
        syslog(LOG_WARNING, 'error in file: ' . $err['file'] . ' (line ' . $err['line'] . '). ' . $err['message']);
    }
}


namespace Nf\Db\Resource;

class Mysqli
{

    private $_sql;

    private $_res;

    private $_adapter;

    public function __construct($sql, $adapter)
    {
        $this->_sql = $sql;
        $this->_adapter = $adapter;
    }

    public function execute()
    {
        $this->_res = $this->_adapter->getConnection()->query($this->_sql);
        if ($this->_res === false) {
            throw new \Exception('The query you tried to execute raised an exception: ' . $this->_sql . ' - ' . $this->_adapter->getConnection()->error);
        }
    }

    public function fetch($mode = null)
    {
        if (! $this->_res) {
            return false;
        }
        
        switch ($mode) {
            case \Nf\Db::FETCH_NUM:
                return $this->_res->fetch_row();
                break;
            case \Nf\Db::FETCH_OBJ:
                return $this->_res->fetch_object();
                break;
            default:
                return $this->_res->fetch_assoc();
        }
    }

    public function fetchAll()
    {
        $data = array();
        while ($row = $this->fetch()) {
            $data[] = $row;
        }
        return $data;
    }

    public function fetchColumn($col = 0)
    {
        $data = array();
        $col = (int) $col;
        $row = $this->fetch(\Nf\Db::FETCH_NUM);
        if (! is_array($row)) {
            return false;
        }
        return $row[$col];
    }

    public function rowCount()
    {
        if (! $this->_adapter) {
            return false;
        }
        $mysqli = $this->_adapter->getConnection();
        return $mysqli->affected_rows;
    }
}


namespace Nf\Error\Exception\Http;

use Nf\Error\Exception\Http;

class NotFound extends Http
{
    
    public $doLog = false;
    
    protected $_httpStatus = 404;
    
    public function getErrors()
    {
        return '';
    }
}


namespace Nf\Error\Exception\Http;

use Nf\Error\Exception\Http;

class Unauthorized extends Http
{
    
    public $doLog = false;
    
    protected $_httpStatus = 401;
    
    public function getErrors()
    {
        return '';
    }
}


namespace Nf\Error\Exception\Http;

use Nf\Error\Exception\Http;

class NoContent extends Http
{
    public $doLog = false;

    protected $_httpStatus = 204;

    public function getErrors()
    {
        return '';
    }
}


namespace Nf\Error\Exception\Http;

use Nf\Error\Exception\Http;

class Forbidden extends Http
{
    
    public $doLog = false;
    
    protected $_httpStatus = 403;
}


namespace Nf\Error\Exception\Http;

use Nf\Error\Exception\Http;

/**
 * Gestion des exceptions pour le client avec passage d'array
 */
class BadRequest extends Http
{

    public $doLog = false;
    
    protected $_httpStatus = 400;

    private $_errors = null;

    /**
     *
     * @param array $errors
     */
    public function __construct($errors)
    {
        if (is_string($errors)) {
            $errors = array(
                $errors
            );
        }
        
        $this->_errors = $errors;
        parent::__construct();
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function display()
    {
        $front = \Nf\Front::getInstance();
        $response = $front->getResponse();
        $response->addBodyPart(json_encode($this->_errors));
        $response->sendResponse();
    }
}
