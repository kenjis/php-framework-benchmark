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
 * @package    Filter
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see FilterInterface
 * */
require_once __DIR__.'/filter_interface.php';

/**
 * Implementación de Filtros para Kumbia
 *
 * @category   Kumbia
 * @package    Filter
 * @deprecated 1.0 Use PHP Filter
 */
class Filter {

    /**
     * Aplica filtro de manera estatica
     *
     * @param mixed $s variable a filtrar
     * @param string $filter filtro
     * @param array $options
     * @return mixed
     */
    public static function get($s, $filter, $options = array()) {
        if (is_string($options)) {
            $filters = func_get_args();
            unset($filters[0]);

            $options = array();
            foreach ($filters as $f) {
                $filter_class = Util::camelcase($f).'Filter';
                if (!class_exists($filter_class, false)) {
                    self::_load_filter($f);
                }

                $s = call_user_func(array($filter_class, 'execute'), $s, $options);
            }
        } else {
            $filter_class = Util::camelcase($filter).'Filter';
            if (!class_exists($filter_class, false)) {
                self::_load_filter($filter);
            }
            $s = call_user_func(array($filter_class, 'execute'), $s, $options);
        }

        return $s;
    }

    /**
     * Aplica los filtros a un array
     *
     * @param array $array array a filtrar
     * @param string $filter filtro
     * @param array $options
     * @return array
     */
    public static function get_array($array, $filter, $options = array()) {
        $args = func_get_args();

        foreach ($array as $k => $v) {
            $args[0]   = $v;
            $array[$k] = call_user_func_array(array('self', 'get'), $args);
        }

        return $array;
    }

    /**
     * Aplica los filtros a un array de datos.
     *
     * Muy util cuando queremos validar que de un formulario solo nos lleguen
     * los datos necesarios para cierta situación, eliminando posibles elementos
     * indeseados.
     *
     * Ejemplos de uso:
     *
     * $form = array(
     *          'nombre' => "Pedro José",
     *          'apellido' => "  Perez Aguilar  ",
     *          'fecha_nac' => "2000-05-20",
     *          'input_coleado' => "valor coleado",
     *          'edad' => "25"
     *      );
     *
     * Filter::data($form, array(
     *                      'nombre',
     *                      'apellido',
     *                      'fecha_nac' => 'date',
     *                      'edad' => 'int'
     *                  ), 'trim');
     *
     * Devuelve: array(
     *          'nombre' => "Pedro José",
     *          'apellido' => "Perez Aguilar",
     *          'fecha_nac' => "2000-05-20",
     *          'edad' => "25"
     *      );
     *
     * Otro ejemplo para el mismo $form:
     *
     * Filter::data($form, array(
     *                      'nombre' => 'upper|alpha',
     *                      apellido => 'lower|htmlentities|addslashes'
     *                      'fecha_nac' => 'date',
     *                      'edad' => 'int'
     *                  ), 'trim');
     *
     * Otros ejemplos más:
     *
     * Filter::data($form, array('nombre', 'apellido','fecha_nac','edad'),'trim');
     *
     * Filter::data($form, array('nombre', 'apellido','fecha_nac'));
     *
     * @param array $data datos a filtrar.
     * @param array $fields arreglo donde los indices son los campos a devolver
     * del array original, y el valor de cada indice es el filtro que se
     * aplicará. si no se desea especificar ningun filtro para algun indice,
     * se coloca solo el nombre del mismo como un valor mas del arreglo.
     * @param string $filterAll filtros que se aplicaran a todos los elementos.
     * @return array datos filtrados. (Ademas solo devuelve los indices
     * especificados en el segundo parametro).
     */
    public static function data(array $data, array $fields, $filterAll = '') {
        $filtered = array();//datos filtrados a devolver.
        foreach ($fields as $index => $filters) {
            if (is_numeric($index) && array_key_exists($filters, $data)) {
                //si el indice es numerico, no queremos usar filtro para ese campo
                $filtered[$filters] = $data[$filters];
                continue;
            } elseif (array_key_exists($index, $data)) {//verificamos de nuevo la existencia del indice en $data
                $filters = explode('|', $filters);//convertimos el filtro en arreglo
                array_unshift($filters, $data[$index]);
                $filtered[$index] = call_user_func_array(array('self', 'get'), $filters);
                //$filtered[$index] = self::get($data[$index], $filters); //por ahora sin opciones adicionales.
            }
        }
        if ($filterAll) {
            $filterAll = explode('|', $filterAll);
            array_unshift($filterAll, $filtered);
            return call_user_func_array(array('self', 'get_array'), $filterAll);
        } else {
            return $filtered;
        }
    }

    /**
     * Aplica filtros a un objeto
     *
     * @param mixed $object
     * @param array $options
     * @return object
     */
    public static function get_object($object, $filter, $options = array()) {
        $args = func_get_args();

        foreach ($object as $k => $v) {
            $args[0]    = $v;
            $object->$k = call_user_func_array(array('self', 'get'), $args);
        }

        return $object;
    }

    /**
     * Carga un Filtro
     *
     * @param string $filter filtro
     * @throw KumbiaException
     */
    protected static function _load_filter($filter) {
        $file = APP_PATH."extensions/filters/{$filter}_filter.php";
        if (!is_file($file)) {
            $file = __DIR__."/base_filter/{$filter}_filter.php";
            if (!is_file($file)) {
                throw new KumbiaException("Filtro $filter no encontrado");
            }
        }

        include $file;
    }

}
