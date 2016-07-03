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
 * @package    Session
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Modelo orientado a objetos para el acceso a datos en Sesiones
 *
 * @category   Kumbia
 * @package    Session
 */

/*Session start*/
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();};
class Session
{

    /**
     * Crear o especificar el valor para un indice de la sesión
     * actual
     *
     * @param string $index
     * @param string $namespace
     */
    public static function set($index, $value, $namespace='default')
    {
        $_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index] = $value;
    }

    /**
     * Obtener el valor para un indice de la sesión
     *
     * @param string $index
     * @param string $namespace
     * @return mixed
     */
    public static function get($index, $namespace='default')
    {
        if (isset($_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index])) {
            return $_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index];
        }
    }

    /**
     * Elimina un indice
     *
     * @param string $index
     * @param string $namespace
     */
    public static function delete($index, $namespace='default')
    {
        unset($_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index]);
    }

    /**
     * Verifica si el indice esta cargado en sesión
     *
     * @param string $index
     * @param string $namespace
     * @return boolean
     */
    public static function has($index, $namespace='default')
    {
        return isset($_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index]);
    }

}
