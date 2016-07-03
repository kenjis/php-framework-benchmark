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
 * Permite almacenar valores durante la ejecución de la aplicación. Implementa el
 * patrón de diseño Registry
 *
 * @category   Kumbia
 * @package    security
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase para el almacenar valores durante una petición.
 *
 * Permite almacenar valores durante la ejecución de la aplicación. Implementa el
 * patrón de diseño Registry
 *
 * @category   Kumbia
 * @package    security
 *
 */
class Registry
{

    /**
     * Variable donde se guarda el registro
     *
     * @var array
     */
    private static $registry = array();

    /**
     * Establece un valor del registro
     *
     * @param string $index
     * @param string $value
     */
    public static function set($index, $value)
    {
        self::$registry[$index] = $value;
    }

    /**
     * Agrega un valor al registro a uno ya establecido
     *
     * @param string $index
     * @param string $value
     */
    public static function append($index, $value)
    {
        self::exist($index);
        self::$registry[$index][] = $value;
    }

    /**
     * Agrega un valor al registro al inicio de uno ya establecido
     *
     * @param string $index
     * @param string $value
     */
    public static function prepend($index, $value)
    {
        self::exist($index);
        array_unshift(self::$registry[$index], $value);
    }

    /**
     * Obtiene un valor del registro
     *
     * @param string $index
     * @return mixed
     */
    public static function get($index)
    {
        if (isset(self::$registry[$index])) {
            return self::$registry[$index];
        } 
    }
    
    /**
     * Crea un index si no existe
     *
     * @param string $index
     */
    protected function exist($index) {
        if (!isset(self::$registry[$index])) {
            self::$registry[$index] = array();
        }
    }
}