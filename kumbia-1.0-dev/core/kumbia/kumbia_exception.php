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
 * Clase principal para el manejo de excepciones
 *
 * @category   Kumbia
 * @package    Core
 */
class KumbiaException extends Exception
{

    /**
     * View de error de la Excepción
     *
     * @var string
     */
    protected $view;
    
    /**
     * Error 404 para los siguientes views
     *
     * @var array
     */
    protected static $view404 = array('no_controller', 'no_action', 'num_params', 'no_view');

    /**
     * Path del template de exception
     *
     * @var string
     */
    protected $template = 'views/templates/exception.phtml';
    
    /**
     * Constructor de la clase;
     *
     * @param string $message mensaje
     * @param string $view vista que se mostrara
     */
    public function __construct($message, $view = 'exception')
    {
        $this->view = $view;
        parent::__construct($message);
    }

    /**
     * Maneja las excepciones no capturadas
     *
     * @param Exception $e
     * */
    public static function handleException($e)
    {
        self::setHeader($e);
        //TODO quitar el extract, que el view pida los que necesite
        extract(Router::get(), EXTR_OVERWRITE);
        // Registra la autocarga de helpers
        spl_autoload_register('kumbia_autoload_helper', true, true);

        $Controller = Util::camelcase($controller);
        ob_start();
        if (PRODUCTION) { //TODO: añadir error 500.phtml
            include APP_PATH . 'views/_shared/errors/404.phtml';
            return;
        }
        if ($e instanceof KumbiaException) {
            $view = $e->view;
            $tpl = $e->template;
        } else {
            $view = 'exception';
            $tpl = 'views/templates/exception.phtml';
        }
        //Fix problem with action name in REST
        $action =  $e->getMessage() ? $e->getMessage() : $action;

        include CORE_PATH . "views/errors/{$view}.phtml";
 
        $content = ob_get_clean();

        // termina los buffers abiertos
        while (ob_get_level ()) {
            ob_end_clean();
        }
        include CORE_PATH . $tpl;
    }

    /**
     * Añade la cabezera de error http
     * */
    private static function setHeader($e)
    {
        if (isset($e->view) && in_array($e->view, self::$view404)){
            header('HTTP/1.1 404 Not Found');
            return;
        }
        header('HTTP/1.1 500 Internal Server Error');
        //TODO: mover a los views
    }
}
