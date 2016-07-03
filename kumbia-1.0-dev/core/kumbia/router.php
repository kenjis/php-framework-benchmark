<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Router
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase que Actua como router del Front-Controller
 *
 * Manejo de redirecciones de peticiones
 * Contiene información referente a la url de
 * la petición ( modudo, controlador, acción, parametros, etc )
 *
 * @category   Kumbia
 * @package    Router
 */
class Router
{
    /**
     * Array estatico con las variables del router
     *
     * @var array
     */

    protected static $vars = array(
        'method'          => null, //Método usado GET, POST, ...
        'route'           => null, //Ruta pasada en el GET
        'module'          => null, //Nombre del módulo actual
        'controller'      => 'index', //Nombre del controlador actual
        'action'          => 'index', //Nombre de la acción actual, por defecto index
        'parameters'      => array(), //Lista los parámetros adicionales de la URL
        'controller_path' => 'index',
        'default_path'    => APP_PATH, //Path donde se encuentran los controller
        'suffix'          => '_controller.php', //suffix for controler
        'dir'             => 'controllers', //dir of controller
    );

    /**
     * This is the name of router class
     * @var String
     */
    protected static $router = 'KumbiaRouter';
    //Es el router por defecto;

    /**
     * Indica si esta pendiente la ejecución de una ruta por parte del dispatcher
     *
     * @var boolean
     */
    protected static $routed = false;

    /**
     * Procesamiento basico del router
     * @param string $url
     * @return void
     */
    public static function init($url)
    {
        // Se miran los parámetros por seguridad
        if (stripos($url, '/../') !== false) {
            throw new KumbiaException("Posible intento de hack en URL: '$url'");
        }
        // Si hay intento de hack TODO: añadir la ip y referer en el log
        self::$vars['route'] = $url;
        //Método usado
        self::$vars['method'] = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Ejecuta una url
     *
     * @param string $url
     * @return Controller
     */
    public static function execute($url)
    {
        self::init($url);
        //alias
        $router = self::$router;
        $conf   = Config::get('config.application.routes');
        //Si config.ini tiene routes activados, mira si esta routed
        if ($conf) {
            /*Esta activado el router*/
            /* This if for back compatibility*/
            if ($conf === '1') {
                $url = $router::ifRouted($url);
            } else {
                /*Es otra clase de router*/
                $router = self::$router = $conf;
            }
        }

        // Descompone la url
        self::$vars = $router::rewrite($url) + self::$vars;

        // Despacha la ruta actual
        return self::dispatch($router::getController(self::$vars));
    }

    /**
     * Realiza el dispatch de la ruta actual
     *
     * @return Controller
     */
    private static function dispatch($cont)
    {
        // Se ejecutan los filtros initialize y before
        if ($cont->k_callback(true) === false) {
            return $cont;
        }

        //Obteniendo el metodo
        try {
            $reflectionMethod = new ReflectionMethod($cont, $cont->action_name);
        } catch (ReflectionException $e) {
            throw new KumbiaException($cont->action_name, 'no_action');//TODO: enviar a un método del controller
        }

        //k_callback y __constructor metodo reservado
        if ($cont->action_name == 'k_callback' || $reflectionMethod->isConstructor()) {
            throw new KumbiaException('Esta intentando ejecutar un método reservado de KumbiaPHP');
        }

        //se verifica que los parametros que recibe
        //la action sea la cantidad correcta
        $num_params = count($cont->parameters);
        if ($cont->limit_params && ($num_params < $reflectionMethod->getNumberOfRequiredParameters() ||
                $num_params > $reflectionMethod->getNumberOfParameters())) {
            throw new KumbiaException(null, 'num_params');
        }

        try {
            $reflectionMethod->invokeArgs($cont, $cont->parameters);
        } catch (ReflectionException $e) {
            throw new KumbiaException(null, 'no_action');//TODO: mejor no_public
        }

        //Corre los filtros after y finalize
        $cont->k_callback();

        //Si esta routed internamente volver a ejecutar
        self::isRouted();

        return $cont;
    }

    /**
     * Redirecciona la ejecución internamente
     */
    protected static function isRouted()
    {
        if (self::$routed) {
            self::$routed = false;
            $router = self::$router;
            // Despacha la ruta actual
            self::dispatch($router::getController(self::$vars));
        }
    }

    /**
     * Envia el valor de un atributo o el array con todos los atributos y sus valores del router
     * Mirar el atributo vars del router
     * ej.
     * <code>Router::get()</code>
     *
     * ej.
     * <code>Router::get('controller')</code>
     *
     * @param string $var (opcional) un atributo: route, module, controller, action, parameters o routed
     * @return array|string con el valor del atributo
     */
    public static function get($var = '')
    {
        return ($var) ? self::$vars[$var] : self::$vars;
    }

    /**
     * Redirecciona la ejecución internamente o externamente con un routes propio
     *
     * @param array $params array de $vars (móddulo, controller, action, params, ...)
     * @param boolean $intern si la redirección es interna
     */
    public static function to($params, $intern = false)
    {
        if ($intern) {
            self::$routed = true;
        }
        self::$vars = $params + self::$vars;
    }
}
