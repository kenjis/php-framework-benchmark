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
 * @package    Db
 * @subpackage Adapters
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see DbPdo Padre de Drivers Pdo
 */
require_once CORE_PATH . 'libs/db/adapters/pdo.php';

/**
 * PDO Microsoft SQL Server Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbPdoAccess extends DbPDO
{

    /**
     * Nombre del Driver RBDM
     */
    protected $db_rbdm = "odbc";

    /**
     * Tipo de Dato Integer
     *
     */
    const TYPE_INTEGER = "INTEGER";

    /**
     * Tipo de Dato Date
     *
     */
    const TYPE_DATE = "DATETIME";

    /**
     * Tipo de Dato Varchar
     *
     */
    const TYPE_VARCHAR = "VARCHAR";

    /**
     * Tipo de Dato Decimal
     *
     */
    const TYPE_DECIMAL = "DECIMAL";

    /**
     * Tipo de Dato Datetime
     *
     */
    const TYPE_DATETIME = "DATETIME";

    /**
     * Tipo de Dato Char
     *
     */
    const TYPE_CHAR = "CHAR";

    /**
     * Ejecuta acciones de incializacion del driver
     *
     */
    public function initialize()
    {
        /**
         * Permite insertar valores en columnas identidad
         */
        //$this->exec("SET IDENTITY_INSERT ON");
    }

    /**
     * Verifica si una tabla existe o no
     *
     * @param string $table
     * @return boolean
     */
    public function table_exists($table, $schema='')
    {
        $table = addslashes("$table");
        $num = $this->fetch_one("SELECT COUNT(*) FROM sysobjects WHERE type = 'U' AND name = '$table'");
        return $num[0];
    }

    /**
     * Devuelve un LIMIT valido para un SELECT del RBDM
     *
     * @param integer $number
     * @return string
     */
    public function limit($sql, $number)
    {
        if (!is_numeric($number)) {
            return $sql;
        }
        $orderby = stristr($sql, 'ORDER BY');
        if ($orderby !== false) {
            $sort = (stripos($orderby, 'desc') !== false) ? 'desc' : 'asc';
            $order = str_ireplace('ORDER BY', '', $orderby);
            $order = trim(preg_replace('/ASC|DESC/i', '', $order));
        }
        $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . ($number) . ' ', $sql);
        $sql = 'SELECT * FROM (SELECT TOP ' . $number . ' * FROM (' . $sql . ') AS itable';
        if ($orderby !== false) {
            $sql.= ' ORDER BY ' . $order . ' ';
            $sql.= ( stripos($sort, 'asc') !== false) ? 'DESC' : 'ASC';
        }
        $sql.= ') AS otable';
        if ($orderby !== false) {
            $sql.=' ORDER BY ' . $order . ' ' . $sort;
        }
        return $sql;
    }

    /**
     * Borra una tabla de la base de datos
     *
     * @param string $table
     * @return boolean
     */
    public function drop_table($table, $if_exists=true)
    {
        if ($if_exists) {
            if ($this->table_exists($table)) {
                return $this->query("DROP TABLE $table");
            } else {
                return true;
            }
        } else {
            return $this->query("DROP TABLE $table");
        }
    }

    /**
     * Crea una tabla utilizando SQL nativo del RDBM
     *
     * TODO:
     * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
     * - Agregar el tipo de tabla que debe usarse (MySQL)
     * - Soporte para campos autonumericos
     * - Soporte para llaves foraneas
     *
     * @param string $table
     * @param array $definition
     * @return boolean
     */
    public function create_table($table, $definition, $index=array())
    {
        $create_sql = "CREATE TABLE $table (";
        if (!is_array($definition)) {
            new KumbiaException("Definici&oacute;n invalida para crear la tabla '$table'");
            return false;
        }
        $create_lines = array();
        $index = array();
        $unique_index = array();
        $primary = array();
        //$not_null = "";
        //$size = "";
        foreach ($definition as $field => $field_def) {
            if (isset($field_def['not_null'])) {
                $not_null = $field_def['not_null'] ? 'NOT NULL' : '';
            } else {
                $not_null = "";
            }
            if (isset($field_def['size'])) {
                $size = $field_def['size'] ? '(' . $field_def['size'] . ')' : '';
            } else {
                $size = "";
            }
            if (isset($field_def['index'])) {
                if ($field_def['index']) {
                    $index[] = "INDEX($field)";
                }
            }
            if (isset($field_def['unique_index'])) {
                if ($field_def['unique_index']) {
                    $index[] = "UNIQUE($field)";
                }
            }
            if (isset($field_def['primary'])) {
                if ($field_def['primary']) {
                    $primary[] = "$field";
                }
            }
            if (isset($field_def['auto'])) {
                if ($field_def['auto']) {
                    $field_def['extra'] = isset($field_def['extra']) ? $field_def['extra'] . " IDENTITY" : "IDENTITY";
                }
            }
            if (isset($field_def['extra'])) {
                $extra = $field_def['extra'];
            } else {
                $extra = "";
            }
            $create_lines[] = "$field " . $field_def['type'] . $size . ' ' . $not_null . ' ' . $extra;
        }
        $create_sql.= join(',', $create_lines);
        $last_lines = array();
        if (count($primary)) {
            $last_lines[] = 'PRIMARY KEY(' . join(",", $primary) . ')';
        }
        if (count($index)) {
            $last_lines[] = join(',', $index);
        }
        if (count($unique_index)) {
            $last_lines[] = join(',', $unique_index);
        }
        if (count($last_lines)) {
            $create_sql.= ',' . join(',', $last_lines) . ')';
        }
        return $this->query($create_sql);
    }

    /**
     * Listar las tablas en la base de datos
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name");
    }

    /**
     * Listar los campos de una tabla
     *
     * @param string $table
     * @return array
     */
    public function describe_table($table, $schema='')
    {
        $describe_table = $this->fetch_all("exec sp_columns @table_name = '$table'");
        $final_describe = array();
        foreach ($describe_table as $field) {
            $final_describe[] = array(
                "Field" => $field["COLUMN_NAME"],
                "Type" => $field['LENGTH'] ? $field["TYPE_NAME"] : $field["TYPE_NAME"] . "(" . $field['LENGTH'] . ")",
                "Null" => $field['NULLABLE'] == 1 ? "YES" : "NO"
            );
        }
        $describe_keys = $this->fetch_all("exec sp_pkeys @table_name = '$table'");
        foreach ($describe_keys as $field) {
            for ($i = 0; $i <= count($final_describe) - 1; $i++) {
                if ($final_describe[$i]['Field'] == $field['COLUMN_NAME']) {
                    $final_describe[$i]['Key'] = 'PRI';
                } else {
                    $final_describe[$i]['Key'] = "";
                }
            }
        }
        return $final_describe;
    }

    /**
     * Devuelve el ultimo id autonumerico generado en la BD
     *
     * @return integer
     */
    public function last_insert_id($table='', $primary_key='')
    {
        /**
         * Porque no funciona SELECT SCOPE_IDENTITY()?
         */
        $num = $this->fetch_one("SELECT MAX($primary_key) FROM $table");
        return (int) $num[0];
    }

}
