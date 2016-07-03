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
 * @package    Core
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Renderer de vistas
 *
 * @category   Kumbia
 * @package    Core
 */
class KumbiaView
{

    /**
     * Contenido
     *
     * @var string
     */
    protected static $_content;
    /**
     * Vista a renderizar
     *
     * @var string
     * */
    protected static $_view;
    /**
     * Template
     *
     * @var string
     */
    protected static $_template = 'default';
    /**
     * Indica el tipo de salida generada por el controlador
     *
     * @var string
     */
    protected static $_response;
    /**
     * Indica el path al que se le añadira la constante correspondiente
     *
     * @var string
     */
    protected static $_path;
    /**
     * Número de minutos que será cacheada la vista actual
     *
     * type: tipo de cache (view, template)
     * time: tiempo de vida de cache
     *
     * @var array
     */
    protected static $_cache = array('type' => FALSE, 'time' => FALSE, 'group' => FALSE);

    /**
     * Controlador actual
     *
     * @var Controller
     */
    protected static $_controller;

    /**
     * Cambia el view y opcionalmente el template
     *
     * @param string $view nombre del view a utilizar sin .phtml
     * @param string $template  opcional nombre del template a utilizar sin .phtml
     */
    public static function select($view, $template = FALSE)
    {
        self::$_view = $view;

        // verifica si se indico template
        if ($template !== FALSE) {
            self::$_template = $template;
        }
    }

    /**
     * Asigna el template para la vista
     *
     * @param string $template nombre del template a utilizar sin .phtml
     */
    public static function template($template)
    {
        self::$_template = $template;
    }

    /**
     * Indica el tipo de Respuesta dada por el controlador
     * buscando el view con esa extension.
     * ej. View::response('xml');
     * buscara: views/controller/action.xml.phtml
     *
     * @param string $response
     * @param string $template Opcional nombre del template sin .phtml
     */
    public static function response($response, $template = FALSE)
    {
        self::$_response = $response;

        // verifica si se indico template
        if ($template !== FALSE) {
            self::$_template = $template;
        }
    }

    /**
     * Asigna el path de la vista
     *
     * @param string $path path de la vista sin extension .phtml
     */
    public static function setPath($path)
    {
        self::$_path = $path . '/';
    }

    /**
     * Obtiene el path para vista incluyendo la extension .phtml
     *
     * @return string
     */
    public static function getPath()
    {
        if (self::$_response)
            return self::$_path . self::$_view . '.' . self::$_response . '.phtml';

        return self::$_path . self::$_view . '.phtml';
    }

    /**
     * Obtiene un atributo de KumbiaView
     *
     * @param string $atribute nombre de atributo (template, response, path, etc)
     */
    public static function get($atribute)
    {
        return self::${"_$atribute"};
    }

    /**
     * Asigna cacheo de vistas o template
     *
     * @param $time Tiempo de vida de cache
     * @param $type Tipo de cache (view, template)
     * @param $group Grupo de pertenencia de cache
     *
     * @return boolean  En producción y cache de view
     */
    public static function cache($time, $type='view', $group='kumbia.view')
    {
        if ($time === FALSE) { //TODO borrar cache
            self::$_cache['type'] = FALSE;
            return FALSE;
        }
        self::$_cache['type'] = $type;
        self::$_cache['time'] = $time;
        self::$_cache['group'] = $group;
        //Si está en producción para view
        if (PRODUCTION && $type === 'view') {
            return self::getCache(); //TRUE si está cacheada
        }
        return FALSE;
    }

    /**
     * Obtiene la cache de view
     *
     * @return boolean
     */
    protected static function getCache()
    {
        // el contenido permanece nulo si no hay nada cacheado o la cache expiro
        self::$_content = Cache::driver()->get(Router::get('route'), self::$_cache['group']);
        return self::$_content !== NULL;
    }

    /**
     * Obtiene el view
     *
     * @return string path del view
     */
    protected static function getView()
    {
        $file = APP_PATH . 'views/' . self::getPath();
        //Si no existe el view y es scaffold
        if (!is_file($file) && ($scaffold = self::$_controller->scaffold)) {
            $file = APP_PATH . "views/_shared/scaffolds/$scaffold/".self::$_view.'.phtml';
        }
        return $file;
    }
    /**
     * Cachea el view o template
     *
     * @param string $type view o template
     * @return void
     */
    protected static function saveCache($type)
    {
        // si esta en produccion y se cachea la vista
        if (PRODUCTION && self::$_cache['type'] === $type) {
                Cache::driver()->save(ob_get_contents(), self::$_cache['time'], Router::get('route'), self::$_cache['group']);
            }
    }

    /**
     * Renderiza la vista
     *
     * @param Controller $controller
     */
    public static function render(Controller $controller)
    {
        if (!self::$_view && !self::$_template)
            return ob_end_flush();

        // Guarda el controlador
        self::$_controller = $controller;

        self::generate($controller);
    }

    /**
     * Genera la vista
     *
     * @param Controller $controller
     */
    protected static function generate(Controller $controller)
    {
        // Registra la autocarga de helpers
        spl_autoload_register('kumbia_autoload_helper', true, true);
        // Mapea los atributos del controller en el scope
        extract(get_object_vars($controller), EXTR_OVERWRITE);

        // carga la vista si tiene view y no esta cacheada
        if (self::$_view && self::$_content === NULL) {
            // Carga el contenido del buffer de salida
            self::$_content = ob_get_clean();
            // Renderizar vista
            ob_start();

            // carga la vista
            if (!include self::getView())
                throw new KumbiaException('Vista "' . self::getPath() . '" no encontrada', 'no_view');

            // si esta en produccion y se cachea la vista
            self::saveCache('view');

            // Verifica si hay template
            if (!self::$_template) {
                ob_end_flush();
                return;
            }

            self::$_content = ob_get_clean();

        }

        // Renderizar template
        if ($__template = self::$_template) {
            ob_start();

            // carga el template
            if (!include APP_PATH . "views/_shared/templates/$__template.phtml")
                throw new KumbiaException("Template $__template no encontrado");

            // si esta en produccion y se cachea template
            self::saveCache('template');

            return ob_end_flush();
        }

        echo self::$_content;
    }

    /**
     * Imprime el contenido del buffer
     *
     */
    public static function content()
    {
        if (isset($_SESSION['KUMBIA.CONTENT'])) {
            echo $_SESSION['KUMBIA.CONTENT'];
            unset($_SESSION['KUMBIA.CONTENT']);
        }
        echo self::$_content;
    }

    /**
     * Renderiza una vista parcial
     *
     * @param string $partial vista a renderizar
     * @param string $__time tiempo de cache
     * @param array $params
     * @param string $group grupo de cache
     * @return string
     * @throw KumbiaException
     */
    public static function partial($partial, $__time='', $params=NULL, $group ='kumbia.partials')
    {
        if (PRODUCTION && $__time && !Cache::driver()->start($__time, $partial, $group)) {
            return;
        }

        //Verificando el partials en el dir app
        $__file = APP_PATH . "views/_shared/partials/$partial.phtml";

        if (!is_file($__file)) {
            //Verificando el partials en el dir core
            $__file = CORE_PATH . "views/partials/$partial.phtml";
        }

        if($params){
            if (is_string($params)) {
                    $params = Util::getParams(explode(',', $params));
            }

            // carga los parametros en el scope
            extract($params, EXTR_OVERWRITE);
        }

        // carga la vista parcial
        if (!include $__file) {
            throw new KumbiaException('Vista Parcial "' . $__file . '" no se encontro');
        }

        // se guarda en la cache de ser requerido
        if (PRODUCTION && $__time) {
            Cache::driver()->end();
        }
    }

    /**
     * Obtiene el valor de un atributo público o todos del controlador
     *
     * @param string $var nombre de variable
     * @return mixed valor de la variable
     */
    public static function getVar($var = '')
    {
        if(!$var) return get_object_vars(self::$_controller);

        return isset(self::$_controller->$var) ? self::$_controller->$var : NULL;
    }
}

/**
 * Atajo para htmlspecialchars, por defecto toma el charset de la
 * aplicacion
 *
 * @param string $s
 * @param string $charset
 * @return string
 */
function h($s, $charset = APP_CHARSET)
{
    return htmlspecialchars($s, ENT_QUOTES, $charset);
}