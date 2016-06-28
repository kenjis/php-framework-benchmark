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
 * @category   KumbiaPHP
 * @package    Helpers
 * @copyright  Copyright (c) 2005 - 2016 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Helper que utiliza Ajax
 *
 * @category   KumbiaPHP
 * @package    Helpers
 */
class Ajax
{

    /**
     * Crea un enlace en una Aplicacion actualizando la capa con ajax
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $update capa a actualizar
     * @param string $class clases adicionales
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function link($action, $text, $update, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . "$action\" class=\"js-remote $class\" data-to=\"{$update}\" $attrs>$text</a>";
    }

    /**
     * Crea un enlace a una acción actualizando la capa con ajax
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $update capa a actualizar
     * @param string $class clases adicionales
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function linkAction($action, $text, $update, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . Router::get('controller_path') . "/$action\" class=\"js-remote $class\" data-to=\"{$update}\" $attrs>$text</a>";
    }

    /**
     * Crea un enlace en una Aplicacion actualizando la capa con ajax con mensaje
     * de confirmacion
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $update capa a actualizar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function linkConfirm($action, $text, $update, $confirm, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . "$action\" class=\"js-remote-confirm $class\" data-to=\"{$update}\" title=\"$confirm\" $attrs>$text</a>";
    }

    /**
     * Crea un enlace a una acción actualizando la capa con ajax con mensaje
     * de confirmacion
     *
     * @param string $action ruta a la accion
     * @param string $text texto a mostrar
     * @param string $update capa a actualizar
     * @param string $confirm mensaje de confirmacion
     * @param string $class clases adicionales
     * @param string|array $attrs atributos adicionales
     * @return string
     */
    public static function linkActionConfirm($action, $text, $update, $confirm, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        return '<a href="' . PUBLIC_PATH . Router::get('controller_path') . "/$action\" class=\"js-remote-confirm $class\" data-to=\"{$update}\" title=\"$confirm\" $attrs>$text</a>";
    }

    /**
     * Lista desplegable para actualizar usando ajax
     *
     * @param string $field nombre de campo
     * @param array $data
     * @param string $update capa que se actualizara
     * @param string $action accion que se ejecutara
     * @param string $class
     * @param string|array $attrs
     */
    public static function select($field, $data, $update, $action, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        // ruta a la accion
        $action = PUBLIC_PATH . rtrim($action, '/') . '/';
        // genera el campo
        return Form::select($field, $data, "class=\"js-remote $class\" data-update=\"$update\" data-url=\"$action\" $attrs");
    }

    /**
     * Lista desplegable para actualizar usando ajax que toma los valores de un array de objetos
     *
     * @param string $field nombre de campo
     * @param string $show campo que se mostrara
     * @param array  $data Array('modelo','metodo','param')
     * @param string $update capa que se actualizara
     * @param string $action accion que se ejecutara
     * @param string $blank campo en blanco
     * @param string $class
     * @param string|array $attrs
     */
    public static function dbSelect($field, $show, $data, $update, $action, $blank=null, $class = '', $attrs = '')
    {
        $attrs = Tag::getAttrs($attrs);
        // ruta a la accion
        $action = PUBLIC_PATH . rtrim($action, '/') . '/';

        // genera el campo
        return Form::dbSelect($field, $show, $data, $blank, "class=\"js-remote $class\" data-update=\"$update\" data-url=\"$action\" $attrs");
    }

    /**
     * Genera un formulario Ajax
     *
     * @param string $update capa que se actualizara
     * @param string $action accion a ejecutar
     * @param string $class clase de estilo
     * @param string $method metodo de envio
     * @param string|array $attrs atributos
     * @return string
     */
    public static function form($update, $action = '', $class = '', $method = 'post', $attrs = '')
    {
        $attrs = "class=\"js-remote $class\" data-to=\"$update\" ".Tag::getAttrs($attrs);
        return Form::open($action, $method, $attrs);
    }

}
