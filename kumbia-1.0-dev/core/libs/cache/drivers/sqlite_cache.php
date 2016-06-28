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
 * Cache con Sqlite
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class SqliteCache extends Cache
{

    /**
     * Conexion a la base de datos Sqlite
     *
     * @var resource
     * */
    protected $_db = null;

    /**
     * Constructor
     *
     * */
    public function __construct()
    {
        /**
         * Abre una conexión SqLite a la base de datos cache
         *
         */
        $this->_db = sqlite_open(APP_PATH . 'temp/cache.db');
        $result = sqlite_query($this->_db, "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND tbl_name='cache' ");
        $count = sqlite_fetch_single($result);

        if (!$count) {
            sqlite_exec(' CREATE TABLE cache (id TEXT, "group" TEXT, value TEXT, lifetime TEXT) ', $this->_db);
        }
    }

    /**
     * Carga un elemento cacheado
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    public function get($id, $group='default')
    {
        $this->_id = $id;
        $this->_group = $group;

        $id = addslashes($id);
        $group = addslashes($group);

        $id = addslashes($id);
        $group = addslashes($group);
        $lifetime = time();

        $result = sqlite_query($this->_db, " SELECT value FROM cache WHERE id='$id' AND \"group\"='$group' AND lifetime>'$lifetime' OR lifetime='undefined' ");
        return sqlite_fetch_single($result);
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
    public function save($value, $lifetime='', $id='', $group='default')
    {
        if (!$id) {
            $id = $this->_id;
            $group = $this->_group;
        }

        if ($lifetime) {
            $lifetime = strtotime($lifetime);
        } else {
            $lifetime = 'undefined';
        }

        $id = addslashes($id);
        $group = addslashes($group);
        $value = addslashes($value);

        $result = sqlite_query($this->_db, " SELECT COUNT(*) FROM cache WHERE id='$id' AND \"group\"='$group' ");
        $count = sqlite_fetch_single($result);


        // Ya existe el elemento cacheado
        if ($count) {
            return sqlite_exec(" UPDATE cache SET value='$value', lifetime='$lifetime' WHERE id='$id' AND \"group\"='$group' ", $this->_db);
        }

        return sqlite_exec(" INSERT INTO cache (id, \"group\", value, lifetime) VALUES ('$id','$group','$value','$lifetime')", $this->_db);
    }

    /**
     * Limpia la cache
     *
     * @param string $group
     * @return boolean
     */
    public function clean($group='')
    {
        if ($group) {
            $group = addslashes($group);
            return sqlite_exec(" DELETE FROM cache WHERE \"group\"='$group' ", $this->_db);
        }
        return sqlite_exec(" DELETE FROM cache ", $this->_db);
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
        $id = addslashes($id);
        $group = addslashes($group);

        return sqlite_exec(" DELETE FROM cache WHERE id='$id' AND \"group\"='$group' ", $this->_db);
    }

}
