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
 * @package    Controller
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

require_once __DIR__ . '/controller.php';

/**
 * Controlador para manejar peticiones REST
 *
 * Por defecto cada acción se llama como el método usado por el cliente
 * (GET, POST, PUT, DELETE, OPTIONS, HEADERS, PURGE...)
 * ademas se puede añadir mas acciones colocando delante el nombre del método
 * seguido del nombre de la acción put_cancel, post_reset...
 *
 * @category Kumbia
 * @package Controller
 * @author kumbiaPHP Team
 */
class KumbiaRest extends Controller {

    /**
     * Formato de entrada usado para interpretar los datos
     * enviados por el cliente
     * @var String  MIME Type del formato
     */
    protected $_fInput = null;

    /**
     * Permite definir parser personalizados por MIME TYPE
     * Esto es necesario para interpretar las entradas
     * Se define como un MIME type como clave y el valor debe ser un
     * callback que devuelva los datos interpretado
     */
    protected $_inputType = array(
        'application/json' => array('RestController', 'parseJSON'),
        'application/xml' => array('RestController', 'parseXML'),
        'text/xml' => array('RestController', 'parseXML'),
        'text/csv' => array('RestController', 'parseCSV'),
        'application/x-www-form-urlencoded' => array('RestController', 'parseForm'),
    );

    /**
     * Formato de salida enviada al cliente
     * @var String nombre del template a usar
     */
    protected $_fOutput = null;

    /**
     * Permite definir las salidas disponibles,
     * de esta manera se puede presentar la misma salida en distintos
     * formatos a requerimientos del cliente
     */
    protected $_outputType = array(
        'application/json' => 'json',
        'application/xml' => 'xml',
        'text/xml' => 'xml',
        'text/csv' => 'csv',
    );

    public function __construct($arg) {
        parent::__construct($arg);
        $this->initREST();
    }

    /**
     * Hacer el router de la petición y envia los parametros correspondientes
     * a la acción, adema captura formatos de entrada y salida
     */
    protected function initREST() {
        /* formato de entrada */
        $this->_fInput = self::getInputFormat();
        $this->_fOutput = self::getOutputFormat($this->_outputType);
        View::select(null, $this->_fOutput);
        $this->rewriteActionName();

    }

    /**
     * Reescribe la acción
     */
    protected function rewriteActionName() {
        /**
         * reescribimos la acción a ejecutar, ahora tendra será el metodo de
         * la peticion: get(:id), getAll , put, post, delete, etc.
         */
        $action = $this->action_name;
        $method = strtolower(Router::get('method'));
        $rewrite = "{$method}_{$action}";
        if ($this->actionExist($rewrite)) {
            $this->action_name = $rewrite;
        } elseif ($action == 'index' && $method != 'post') {
            $this->action_name = 'getAll';
        } else {
            $this->action_name = $method;
            $this->parameters = ($action == 'index') ? $this->parameters : array($action) + $this->parameters;
        }
    }

    /**
     * Verifica si existe la acción $name existe
     * @param string $name nombre de la acción
     * @return boolean
     */
    protected function actionExist($name) {
        if (method_exists($this, $name)) {
            $reflection = new ReflectionMethod($this, $name);
            return $reflection->isPublic();
        }
        return false;
    }

    /**
     * Retorna los parametros de la petición el función del formato de entrada
     * de los mismos. Hace uso de los parser definidos en la clase
     */
    protected function param() {
        $input = file_get_contents('php://input');
        $format = $this->_fInput;
        /* verifica si el formato tiene un parser válido */
        if (isset($this->_inputType[$format]) && is_callable($this->_inputType[$format])) {
            $result = call_user_func($this->_inputType[$format], $input);
            if ($result) {
                return $result;
            }
        }
        return $input;
    }

    /**
     * Envia el codigo de respuesta $num al cliente
     * @param int $num
     */
    protected function setCode($num) {
        $code = array(
            //Informational 1xx
            100 => '100 Continue',
            101 => '101 Switching Protocols',
            //Successful 2xx
            200 => '200 OK',
            201 => '201 Created',
            202 => '202 Accepted',
            203 => '203 Non-Authoritative Information',
            204 => '204 No Content',
            205 => '205 Reset Content',
            206 => '206 Partial Content',
            //Redirection 3xx
            300 => '300 Multiple Choices',
            301 => '301 Moved Permanently',
            302 => '302 Found',
            303 => '303 See Other',
            304 => '304 Not Modified',
            305 => '305 Use Proxy',
            306 => '306 (Unused)',
            307 => '307 Temporary Redirect',
            //Client Error 4xx
            400 => '400 Bad Request',
            401 => '401 Unauthorized',
            402 => '402 Payment Required',
            403 => '403 Forbidden',
            404 => '404 Not Found',
            405 => '405 Method Not Allowed',
            406 => '406 Not Acceptable',
            407 => '407 Proxy Authentication Required',
            408 => '408 Request Timeout',
            409 => '409 Conflict',
            410 => '410 Gone',
            411 => '411 Length Required',
            412 => '412 Precondition Failed',
            413 => '413 Request Entity Too Large',
            414 => '414 Request-URI Too Long',
            415 => '415 Unsupported Media Type',
            416 => '416 Requested Range Not Satisfiable',
            417 => '417 Expectation Failed',
            422 => '422 Unprocessable Entity',
            423 => '423 Locked',
            //Server Error 5xx
            500 => '500 Internal Server Error',
            501 => '501 Not Implemented',
            502 => '502 Bad Gateway',
            503 => '503 Service Unavailable',
            504 => '504 Gateway Timeout',
            505 => '505 HTTP Version Not Supported',
        );
        if (isset($code[$num])) {
            header(sprintf('HTTP/1.1 %d %s', $num, $code[$num]));
        }
    }

    /**
     * Envia un error al cliente junto con el mensaje
     * @param String $text texto del error
     * @param int $error Número del error HTTP
     * @return Array data de error
     */
    protected function error($text, $error = 400) {
        $this->setCode($error);
        return array('error' => $text);
    }

    /**
     * Retorna los formato aceptados por el cliente ordenados por prioridad
     * interpretando la cabecera HTTP_ACCEPT
     * @return array
     */
    protected static function accept() {
        /* para almacenar los valores acceptados por el cliente */
        $aTypes = array();
        /* Elimina espacios, convierte a minusculas, y separa */
        $accept = explode(',', strtolower(str_replace(' ', '', Input::server('HTTP_ACCEPT'))));
        foreach ($accept as $a) {
            $q = 1; /* Por defecto la proridad es uno, el siguiente verifica si es otra */
            if (strpos($a, ';q=')) {
                /* parte el "mime/type;q=X" en dos: "mime/type" y "X" */
                list($a, $q) = explode(';q=', $a);
            }
            $aTypes[$a] = $q;
        }
        /* ordena por prioridad (mayor a menor) */
        arsort($aTypes);
        return $aTypes;
    }

    /**
     * Parse JSON
     * Convierte formato JSON en array asociativo
     *
     * @param  string       $input
     * @return array|string
     */
    protected static function parseJSON($input) {
        if (function_exists('json_decode')) {
            $result = json_decode($input, true);
            if ($result) {
                return $result;
            }
        }
    }

    /**
     * Parse XML
     *
     * Convierte formato XML en un objeto, esto será necesario volverlo estandar
     * si se devuelven objetos o arrays asociativos
     *
     * @param  string                  $input
     * @return \SimpleXMLElement|string
     */
    protected static function parseXML($input) {
        if (class_exists('SimpleXMLElement')) {
            try {
                return new SimpleXMLElement($input);
            } catch (Exception $e) {
                // Do nothing
            }
        }

        return $input;
    }

    /**
     * Parse CSV
     *
     * Convierte CSV en arrays numéricos,
     * cada item es una linea
     * @param  string $input
     * @return array
     */
    protected static function parseCSV($input) {
        $temp = fopen('php://memory', 'rw');
        fwrite($temp, $input);
        fseek($temp, 0);
        $res = array();
        while (($data = fgetcsv($temp)) !== false) {
            $res[] = $data;
        }
        fclose($temp);
        return $res;
    }

    /**
     * Realiza la conversion de formato de Formulario a array
     *
     * @param string $input
     * @return arrat
     */
    protected static function parseForm($input) {
        parse_str($input, $vars);
        return $vars;
    }

    /**
     * Retorna el tipo de formato de entrada
     * @return string
     */
    protected static function getInputFormat() {
        $str = '';
        if (isset($_SERVER["CONTENT_TYPE"])) {
            $s = explode(';', $_SERVER["CONTENT_TYPE"]);
            $str = trim($s[0]);
        }
        return $str;
    }

    /**
     * Devuelve le nombre del formato de salida
     * @param array $validOutput Array de formatos de salida soportado
     * @return string
     */
    protected function getOutputFormat(Array $validOutput) {
        /* busco un posible formato de salida */
        $accept = self::accept();
        foreach ($accept as $key => $a) {
            if (array_key_exists($key, $validOutput)) {
                return $validOutput[$key];
            }
        }
        return 'json';
    }

    /**
     * Retorna todas las cabeceras enviadas por el cliente
     * @return Array
     */
    protected static function getHeaders() {

        /*Esta función solo existe en apache*/
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = array();

        foreach ($_SERVER as $name => $value) {

            if (substr($name, 0, 5) == 'HTTP_') {

                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;

            }

        }

        return $headers;

    }

}
