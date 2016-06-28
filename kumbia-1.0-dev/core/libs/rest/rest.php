<?php
/**
 * Warning! This IS A ALPHA VERSION NOT USE IN PRODUCTION APP!
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
 * Rest. Clase estática para el manejo de API basada en REST
 *
 * @category   Kumbia
 * @package    Controller
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 * @deprecated since version 1  Usar rest_controller
 */

/**
 * Clase para el manejo de API basada en REST
 *
 * @category   Kumbia
 * @package    Controller
 * @deprecated 0.9 use KumbiaController front-end
 *
 */
class Rest {

    private static $code = array(
        201=> 'Creado ', /* Se ha creado un nuevo recuerso (INSERT) */
        400=> 'Bad Request', /* Petición herronea */
        401=> 'Unauthorized', /* La petición requiere loggin */
        403=> 'Forbidden',
        405=> 'Method Not Allowed'/* No está permitido ese metodo */
    );
    /**
     * Array con los tipos de datos soportados para salida
     */
    private static $_outputFormat = array('json', 'text', 'html', 'xml', 'cvs', 'php');
    /**
     * Tipo de datos soportados para entrada
     */
    private static $_inputFormat = array('json', 'plain', 'x-www-form-urlencoded');
    /**
     * Metodo de petición (GET, POST, PUT, DELETE)
     */
    private static $_method = null;
    /**
     * Establece el formato de salida
     */
    private static $_oFormat = null;
    /**
     * Establece el formato de entrada
     */
    private static $_iFormat = null;

    /**
     * Establece los tipos de respuesta aceptados
     *
     * @param string $accept Cada uno de los tipos separados por coma ','
     */
    static public function accept($accept) {
        self::$_outputFormat = is_array($accept)?$accept:explode(',', $accept);
    }

    /**
     * Define el inicio de un servicio REST
     *
     * @param Controller $controller controlador que se convertira en un servicio REST
     */
    static public function init(Controller $controller) {
        $content = isset($_SERVER['CONTENT_TYPE'])?$_SERVER['CONTENT_TYPE']:'text/html';
        /**
         * Verifico el formato de entrada
         */
        self::$_iFormat = str_replace(array('text/', 'application/'), '', $content);

        /* Compruebo el método de petición */
        self::$_method = strtolower($_SERVER['REQUEST_METHOD']);
        $format        = explode(',', $_SERVER['HTTP_ACCEPT']);
        while (self::$_oFormat = array_shift($format)) {
            self::$_oFormat = str_replace(array('text/', 'application/'), '', self::$_oFormat);
            if (in_array(self::$_oFormat, self::$_outputFormat)) {
                break;
            }
        }

        /**
         * Si no lo encuentro, revuelvo un error
         */
        if (self::$_oFormat == null) {
            return 'error';
        } else {
            View::response(self::$_oFormat);
            View::select('response');
        }

        /**
         * Si la acción del controlador es un numero lo pasamos a los parametros
         */
        if (is_numeric($controller->action_name)) {
            $controller->parameters = array($controller->action_name)+Rest::param();
        } else {
            $controller->parameters = Rest::param();
        }

        /**
         * reescribimos la acción a ejecutar, ahora tendra será el metodo de
         * la peticion: get , put, post, delete, etc.
         */
        $controller->action_name  = self::$_method;
        $controller->limit_params = FALSE;//no hay verificación en el numero de parametros.
        $controller->data         = array();//variable por defecto para las vistas.

    }

    /**
     * Retorna los parametros de la petición el función del método
     * de la petición
     * @return Array
     */
    public static function param() {
        $input = file_get_contents('php://input');
        if (strncmp(self::$_iFormat, 'json', 4) == 0) {
            return json_decode($input, true);
        } else {
            parse_str($input, $output);
            return $output;
        }
    }

}
