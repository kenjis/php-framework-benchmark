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
 abstract class KumbiaFacade
 {
    protected static $providers = [];


    /**
     * Set the providers
     * @param  Array  $p key/value array with providers
     * @return void
     */
    public static function providers(Array $p){
        self::$providers = $p;
    }

    /**
     * Getter for the alias of the component
     */
    protected static function getAlias(){
        throw new RuntimeException('Not implement');
    }


    protected static function getInstance($name)
    {
        return  isset(self::$providers[$name])?self::$providers[$name]:null;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = self::getInstance(static::getAlias());
        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }

 }
