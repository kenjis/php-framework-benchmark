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
 * @package    Cache
 * @subpackage Drivers
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Cacheo de Archivos
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class APCCache extends Cache
{

    /**
     * Carga un elemento cacheado, el apc_fetch puede recibir un array (nuestro cache no)
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    public function get($id, $group='default')
    {
        $this->_id = $id;
        $this->_group = $group;

        $data = apc_fetch("$id.$group");
        if ($data !== FALSE) {
            return $data;
        }
    }

    /**
     * Guarda un elemento en la cache con nombre $id y valor $value
     *
     * @param string $id
     * @param string $group
     * @param string $value
     * @param int $lifetime tiempo de vida en forma timestamp de unix
     * @return boolean
     */
    public function save($id, $group, $value, $lifetime)
    {
        if (!$id) {
            $id = $this->_id;
            $group = $this->_group;
        }

        if ($lifetime) {
            $lifetime = strtotime($lifetime) - time();
        } else {
            $lifetime = '0';
        }

        return apc_store("$id.$group", $value, $lifetime);
    }

    /**
     * Limpia la cache, con APC se limpia TODA no sólo el grupo
     *
     * @param string $group No se usa con APC
     * @return boolean
     */
    public function clean($group=false)
    {
        return apc_clear_cache('user');
    }

    /**
     * Elimina un elemento de la cache
     *
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public function remove($id, $group='default')
    {
        return apc_delete("$id.$group");
    }

}