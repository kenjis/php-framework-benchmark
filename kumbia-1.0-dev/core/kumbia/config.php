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
 * @package    Config
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase para la carga de Archivos .INI y de configuración
 *
 * Aplica el patrón Singleton que utiliza un array
 * indexado por el nombre del archivo para evitar que
 * un .ini de configuración sea leido mas de una
 * vez en runtime con lo que aumentamos la velocidad.
 *
 * @category   Kumbia
 * @package    Config
 */
class Config
{

    /**
     * Contain all the config
     * -
     * Contenido de variables de configuración
     *
     * @var array
     */
    protected static $vars = [];

    /**
     * Get config vars
     * -
     * Obtiene configuración
     *
     * @param string $var fichero.sección.variable
     * @return mixed
     */
    public static function get($var)
    {
        $namespaces = explode('.', $var);
        if (! isset(self::$vars[$namespaces[0]])) {
            self::load($namespaces[0]);
        }
        switch (count($namespaces)) {
            case 3:
                return isset(self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]]) ?
                             self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] : null;
            case 2:
                return isset(self::$vars[$namespaces[0]][$namespaces[1]]) ?
                             self::$vars[$namespaces[0]][$namespaces[1]] : null;
            case 1:
                return isset(self::$vars[$namespaces[0]]) ? self::$vars[$namespaces[0]] : null;
            
            default:
                trigger_error('Máximo 3 niveles en Config::get(fichero.sección.variable), pedido: '. $var);
        }
    }
    /**
     * Get all configs
     * -
     * Obtiene toda la configuración
     *
     * @return array
     */
    public static function getAll()
    {
        return self::$vars;
    }

    /**
     * Set variable in config
     * -
     * Asigna un atributo de configuración
     *
     * @param string $var   variable de configuración
     * @param mixed  $value valor para atributo
     */
    public static function set($var, $value)
    {
        $namespaces = explode('.', $var);
        switch (count($namespaces)) {
            case 3:
                self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] = $value;
                break;
            case 2:
                self::$vars[$namespaces[0]][$namespaces[1]] = $value;
                break;
            case 1:
                self::$vars[$namespaces[0]] = $value;
                break;
            default:
                trigger_error('Máximo 3 niveles en Config::set(fichero.sección.variable), pedido: '. $var);
        }
    }

    /**
     * Read config file
     * -
     * Lee y devuelve un archivo de configuración
     *
     * @param string  $file  archivo .ini
     * @param boolean $force forzar lectura de .ini
     * @return array
     */
    public static function & read($file, $force = false)
    {
        if (isset(self::$vars[$file]) && !$force) {
            return self::$vars[$file];
        }
        self::load($file);
        return self::$vars[$file];
    }

    /**
     * Load config file
     * -
     * Lee un archivo de configuración
     *
     * @param string  $file  archivo .ini
     */
    private static function load($file)
    {
        self::$vars[$file] = parse_ini_file(APP_PATH . "config/$file.ini", true);
    }
}
