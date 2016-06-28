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
 * @package    I18n
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * Enlazando al textdomain de la aplicacion
 */
bindtextdomain('default', APP_PATH . 'locale/');
textdomain('default');

/**
 * Implementación para internacionalización
 *
 * @category   Kumbia
 * @package    I18n
 */
class I18n
{

    /**
     * Efectua una traducción. Cuando se pasan argumentos adicionales se remplaza con sprintf
     *
     * @example
     *   $saludo = I18n::get('Hola %s', 'Emilio')
     *
     * @param string
     * @return string
     * */
    public static function get($sentence)
    {
        /**
         * Obtengo la traduccion
         * */
        $sentence = gettext($sentence);

        /**
         * Si se pasan multiples parametros
         * */
        if (func_num_args() > 1) {
            $args = func_get_args();
            /**
             * Se remplaza con vsprintf
             * */
            unset($args[0]);
            $sentence = vsprintf($sentence, $args);
        }

        return $sentence;
    }

    /**
     * Obtiene una traduccion al plural, cuando se pasan argumentos adicionales se remplaza con sprintf
     *
     * @param string $sentence1 mensaje en singular
     * @param string $sentence2 mensaje en plural
     * @param int $n conteo
     * @return string
     * */
    public static function nget($sentence1, $sentence2, $n)
    {
        /**
         * Obtengo la traduccion
         * */
        $sentence = ngettext($sentence1, $sentence2, $n);

        /**
         * Si se pasan multiples parametros
         * */
        if (func_num_args() > 3) {
            $sentence = $sentence = self::sprintf($sentence, func_get_args(), 3);
        }

        return $sentence;
    }

    /**
     * Obtiene una traduccion por categoria, cuando se pasan argumentos adicionales se remplaza con sprintf
     *
     * @param string $sentence
     * @param int $category categoria del mensaje (LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES, LC_ALL)
     * @return string
     * */
    public static function cget($sentence, $category)
    {
        /**
         * Obtengo la traduccion
         * */
        $sentence = dcgettext(textdomain(null), $sentence, $category);

        /**
         * Si se pasan multiples parametros
         * */
        if (func_num_args() > 2) {
            $sentence = $sentence = self::sprintf($sentence, func_get_args(), 2);
        }

        return $sentence;
    }

    /**
     * Obtiene una traduccion al plural por categoria, cuando se pasan argumentos adicionales se remplaza con sprintf
     *
     * @param string $sentence1 mensaje en singular
     * @param string $sentence2 mensaje en plural
     * @param int $n conteo
     * @param int $category categoria del mensaje (LC_CTYPE, LC_NUMERIC, LC_TIME, LC_COLLATE, LC_MONETARY, LC_MESSAGES, LC_ALL)
     * @return string
     * */
    public static function cnget($sentence1, $sentence2, $n, $category)
    {
        /**
         * Obtengo la traduccion en funcion del dominio
         * */
        $sentence = dcngettext(textdomain(null), $sentence1, $sentence2, $n, $category);

        /**
         * Si se pasan multiples parametros
         * */
        if (func_num_args() > 4) {
            $sentence = self::sprintf($sentence, func_get_args(), 4);
        }

        return $sentence;
    }
    
    
    private static function sprintf($sentence, $args, $offset)
    {
        return vsprintf( $sentence, array_slice( $args, $offset));
        
    }
}
