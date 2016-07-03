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
 * Utilidades para uso general del framework
 *
 * Manejo de cadenas de caracteres.
 * Conversión de parametros con nombre a arreglos.
 *
 * @category   Kumbia
 * @package    Core
 */
class Util
{

    /**
     * Convierte la cadena con espacios o guión bajo en notacion camelcase
     *
     * @param string $s cadena a convertir
     * @param boolean $lower indica si es lower camelcase
     * @return string
     * */
    public static function camelcase($s, $lower=FALSE)
    {
        // Notacion lowerCamelCase
        if ($lower) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $s))));
        }

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $s)));
    }

    /**
     * Convierte la cadena CamelCase en notacion smallcase
     * @param string $s cadena a convertir
     * @return string
     * */
    public static function smallcase($s)
    {
        return strtolower(preg_replace('/([A-Z])/', "_\\1", lcfirst($s)));
    }

    /**
     * Remplaza en la cadena los espacios por guiónes bajos (underscores)
     * @param string $s
     * @return string
     * */
    public static function underscore($s)
    {
        return strtr($s, ' ', '_');
    }

    /**
     * Remplaza en la cadena los espacios por dash (guiones)
     * @param string $s
     * @return string
     */
    public static function dash($s)
    {
        return strtr($s, ' ', '-');
    }

    /**
     * Remplaza en una cadena los underscore o dashed por espacios
     * @param string $s
     * @return string
     */
    public static function humanize($s)
    {
        return strtr($s, '_-', '  ');
    }

    /**
     * Convierte los parametros de una funcion o metodo de parametros por nombre a un array
     *
     * @param array $params
     * @return array
     */
    public static function getParams($params)
    {
        $data = array();
        foreach ($params as $p) {
            if (is_string($p)) {
                $match = explode(': ', $p, 2);
                if (isset($match[1])) {
                    $data[$match[0]] = $match[1];
                } else {
                    $data[] = $p;
                }
            } else {
                $data[] = $p;
            }
        }
        return $data;
    }

    /**
     * Recibe una cadena como: item1,item2,item3 y retorna una como: "item1","item2","item3".
     *
     * @param string $lista Cadena con Items separados por comas (,).
     * @return string Cadena con Items encerrados en doblecomillas y separados por comas (,).
     */
    public static function encomillar($lista)
    {
        $items = explode(',', $lista);
        return '"' . implode('","', $items) . '"';
    }

}
