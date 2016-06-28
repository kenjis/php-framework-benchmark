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
 * @package    KumbiaRouter
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

class KumbiaRouter
{

    /**
     * Toma $url y la descompone en (modulo), controlador, accion y argumentos
     *
     * @param string $url
     * @return  array
     */
    public static function rewrite($url)
    {
        $router = array();
        //Valor por defecto
        if ($url == '/') {
            return $router;
        }

        //Se limpia la url, en caso de que la hallan escrito con el último parámetro sin valor, es decir controller/action/
        // Obtiene y asigna todos los parámetros de la url
        $urlItems = explode('/', trim($url, '/'));

        // El primer parametro de la url es un módulo?
        if (is_dir(APP_PATH."controllers/$urlItems[0]")) {
            $router['module'] = $urlItems[0];

            // Si no hay mas parametros sale
            if (next($urlItems) === false) {
                $router['controller_path'] = "$urlItems[0]/index";
                return $router;
            }
        }

        // Controlador
        $router['controller']      = current($urlItems);
        $router['controller_path'] = !empty($router['module']) ? "$urlItems[0]/$urlItems[1]" : current($urlItems);

        // Si no hay mas parametros sale
        if (next($urlItems) === false) {
            return $router;
        }

        // Acción
        $router['action'] = current($urlItems);

        // Si no hay mas parametros sale
        if (next($urlItems) === false) {
            return $router;
        }

        // Crea los parámetros y los pasa
        $router['parameters'] = array_slice($urlItems, key($urlItems));
        return $router;
    }

    /**
     * Busca en la tabla de entutamiento si hay una ruta en config/routes.ini
     * para el controlador, accion, id actual
     *
     * @param string $url Url para enrutar
     * @return string
     */
    public static function ifRouted($url)
    {
        $routes = Config::get('routes.routes');

        // Si existe una ruta exacta la devuelve
        if (isset($routes[$url])) {
            return $routes[$url];
        }

        // Si existe una ruta con el comodin * crea la nueva ruta
        foreach ($routes as $key => $val) {
            if ($key == '/*') {
                return rtrim($val, '*').$url;
            }

            if (strripos($key, '*', -1)) {
                $key = rtrim($key, '*');
                if (strncmp($url, $key, strlen($key)) == 0) {
                    return str_replace($key, rtrim($val, '*'), $url);
                }
            }
        }
        return $url;
    }

    /**
     * Carga y devuelve una instancia del controllador
     */
    public static function getController($param)
    {
        // Extrae las variables para manipularlas facilmente
        extract($param, EXTR_OVERWRITE);
        if (!include_once "$default_path{$dir}/$controller_path{$suffix}") {
            throw new KumbiaException(null, 'no_controller');
        }
        //Asigna el controlador activo
        $app_controller = Util::camelcase($controller).'Controller';
        return new $app_controller($param);
    }
}
