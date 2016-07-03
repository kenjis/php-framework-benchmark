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
 * Class Para el manejo de Benchmark y Profiling
 *
 * Permite obtener tiempo de ejecucion de un script ó una peticion
 * con el fin de encontrar posibles cuellos de botellas y
 * optimizar el rendimiento de la aplicacion...
 *
 * @category   Kumbia
 * @package    Core
 */
final class Benchmark
{

    /**
     * Almacena los datos de un Benchmark especifico, esto para evitar colision
     *
     * @var name
     */
    private static $_benchmark;
    private static $_avgload = 0;

    /**
     * Inicia el reloj (profiling)
     *
     * @return array $_benchmark
     */
    public static function start_clock($name)
    {
        if (!isset(self::$_benchmark[$name])) {
            self::$_benchmark[$name] = array('start_time' => microtime(), 'final_time' => 0, 'memory_start' => memory_get_usage(), 'memory_stop' => 0, 'time_execution' => 0);
        }
    }

    /**
     * Detiene el reloj para efecto del calculo del
     * tiempo de ejecucion de un script
     *
     * @return array $_benchmark
     */
    private static function _stop_clock($name)
    {
        if (isset(self::$_benchmark[$name])) {
            if (PHP_OS == 'Linux') {
                $load = sys_getloadavg();
            } else {
                $load = 0;
            }
            self::$_avgload = $load[0];
            self::$_benchmark[$name]['memory_stop'] = memory_get_usage();
            self::$_benchmark[$name]['final_time'] = microtime();
            list ($sm, $ss) = explode(' ', self::$_benchmark[$name]['start_time']);
            list ($em, $es) = explode(' ', self::$_benchmark[$name]['final_time']);
            self::$_benchmark[$name]['time_execution'] = number_format(($em + $es) - ($sm + $ss), 4);
            return self::$_benchmark[$name]['time_execution'];
        }
    }

    /**
     * Permite obtener la memoria usada por un script
     *
     * @return string memory_usage
     */
    public static function memory_usage($name)
    {
        if (self::$_benchmark[$name]) {
            self::$_benchmark[$name]['memory_usage'] = number_format((self::$_benchmark[$name]['memory_stop'] - self::$_benchmark[$name]['memory_start']) / 1048576, 2);
            return self::$_benchmark[$name]['memory_usage'];
        } else {
            throw new KumbiaException("No existe el Benchmark para el nombre: '$name', especificado \n");
        }
    }

    /**
     * Retorna el tiempo de ejecucion del scripts (profiling)
     *
     * @return string time_execution
     */
    public static function time_execution($name)
    {
        if (isset(self::$_benchmark[$name])) {
            return self::_stop_clock($name);
        } else {
            throw new KumbiaException("No existe el Benchmark para el nombre: $name, especificado \n");
        }
    }

    /**
     *
     *
     */
    public static function test($func, $loops)
    {
        self::start_clock($func);
        ob_start();
        for ($i = 1; $i <= $loops; $i++) {
            eval($func);
        }
        ob_end_flush();
        $time = self::time_execution($func);
        echo '** Funcion: ', $func;
        echo $loops, ' veces';
        echo ' Tiempo: ', $time;
    }

}