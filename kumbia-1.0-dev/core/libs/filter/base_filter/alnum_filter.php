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
 * @subpackage BaseFilter
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Filtra una cadena haciendo alfa numerica
 *
 * @category   Kumbia
 * @package    Filter
 * @subpackage BaseFilter
 */
class AlnumFilter implements FilterInterface
{

    /**
     * Ejecuta el filtro
     *
     * @param string $s
     * @param array $options
     * @return string
     */
    public static function execute($s, $options)
    {
        /**
         * Revisa si PCRE esta compilado para soportar UNICODE
         * de esta forma filtra tambien tildes y otros caracteres latinos
         */
        if (preg_match('/\pL/u', 'a')) {
            $patron = '/[^\p{L}\p{N}]/';
        } else {
            $patron = '/[^a-zA-Z0-9\s]/';
        }
        return preg_replace($patron, '', (string) $s);
    }

}