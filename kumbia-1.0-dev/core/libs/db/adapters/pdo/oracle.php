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
 * PDO Oracle Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbPdoOracle extends DbPDO
{

    /**
     * Nombre de RBDM
     */
    protected $db_rbdm = "oci";

    /**
     * Tipo de Dato Integer
     *
     */
    const TYPE_INTEGER = "INTEGER";

    /**
     * Tipo de Dato Date
     *
     */
    const TYPE_DATE = "DATE";

    /**
     * Tipo de Dato Varchar
     *
     */
    const TYPE_VARCHAR = "VARCHAR2";

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
        $this->pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        $this->exec("alter session set nls_date_format = 'YYYY-MM-DD'");
        $this->begin();
    }

    /**
     * Devuelve un LIMIT valido para un SELECT del RBDM
     *
     * @param integer $number
     * @return string
     */
    public function limit($sql, $number)
    {
        if (!is_numeric($number) || $number < 0) {
            return $sql;
        }
        if (eregi("ORDER[\t\n\r ]+BY", $sql)) {
            if (stripos($sql, "WHERE")) {
                return eregi_replace("ORDER[\t\n\r ]+BY", "AND ROWNUM <= $number ORDER BY", $sql);
            } else {
                return eregi_replace("ORDER[\t\n\r ]+BY", "WHERE ROWNUM <= $number ORDER BY", $sql);
            }
        } else {
            if (stripos($sql, "WHERE")) {
                return "$sql AND ROWNUM <= $number";
            } else {
                return "$sql WHERE ROWNUM <= $number";
            }
        }
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
     * - Agregar el tipo de tabla que debe usarse (Oracle)
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
                    $this->query("CREATE SEQUENCE {$table}_{$field}_seq START WITH 1");
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
     * Listado de Tablas
     *
     * @return boolean
     */
    function list_tables()
    {
        return $this->fetch_all("SELECT table_name FROM all_tables");
    }

    /**
     * Devuelve el ultimo id autonumerico generado en la BD
     *
     * @return integer
     */
    public function last_insert_id($table='', $primary_key='')
    {
        /**
         * Oracle No soporta columnas autonum&eacute;ricas
         */
        if ($table && $primary_key) {
            $sequence = $table . "_" . $primary_key . "_seq";
            $value = $this->fetch_one("SELECT $sequence.CURRVAL FROM dual");
            return $value[0];
        }
        return false;
    }

    /**
     * Verifica si una tabla existe o no
     *
     * @param string $table
     * @return boolean
     */
    function table_exists($table, $schema='')
    {
        $num = $this->fetch_one("SELECT COUNT(*) FROM ALL_TABLES WHERE TABLE_NAME = '" . strtoupper($table) . "'");
        return $num[0];
    }

    /**
     * Listar los campos de una tabla
     *
     * @param string $table
     * @return array
     */
    public function describe_table($table, $schema='')
    {
        /**
         * Soporta schemas?
         */
        $describe = $this->fetch_all("SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD, LOWER(ALL_TAB_COLUMNS.DATA_TYPE) AS TYPE, ALL_TAB_COLUMNS.DATA_LENGTH AS LENGTH, (SELECT COUNT(*) FROM ALL_CONS_COLUMNS WHERE TABLE_NAME = '" . strtoupper($table) . "' AND ALL_CONS_COLUMNS.COLUMN_NAME = ALL_TAB_COLUMNS.COLUMN_NAME AND ALL_CONS_COLUMNS.POSITION IS NOT NULL) AS KEY, ALL_TAB_COLUMNS.NULLABLE AS ISNULL FROM ALL_TAB_COLUMNS WHERE ALL_TAB_COLUMNS.TABLE_NAME = '" . strtoupper($table) . "'");
        $final_describe = array();
        foreach ($describe as $key => $value) {
            $final_describe[] = array(
                "Field" => $value["field"],
                "Type" => $value["type"],
                "Null" => $value["isnull"] == "Y" ? "YES" : "NO",
                "Key" => $value["key"] == 1 ? "PRI" : ""
            );
        }
        return $final_describe;
    }

}
