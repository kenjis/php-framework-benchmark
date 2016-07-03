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
 * SQLite Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbSQLite extends DbBase implements DbBaseInterface
{

    /**
     * Resource de la Conexion a SQLite
     *
     * @var resource
     */
    public $id_connection;
    /**
     * Ultimo Resultado de una Query
     *
     * @var resource
     */
    public $last_result_query;
    /**
     * Ultima sentencia SQL enviada a SQLite
     *
     * @var string
     */
    protected $last_query;
    /**
     * Ultimo error generado por SQLite
     *
     * @var string
     */
    public $last_error;

    /**
     * Resultado de Array Asociativo
     *
     */
    const DB_ASSOC = SQLITE_ASSOC;

    /**
     * Resultado de Array Asociativo y Numerico
     *
     */
    const DB_BOTH = SQLITE_BOTH;

    /**
     * Resultado de Array Numerico
     *
     */
    const DB_NUM = SQLITE_NUM;


    /**
     * Tipo de Dato Integer
     *
     */
    const TYPE_INTEGER = 'INTEGER';

    /**
     * Tipo de Dato Date
     *
     */
    const TYPE_DATE = 'DATE';

    /**
     * Tipo de Dato Varchar
     *
     */
    const TYPE_VARCHAR = 'VARCHAR';

    /**
     * Tipo de Dato Decimal
     *
     */
    const TYPE_DECIMAL = 'DECIMAL';

    /**
     * Tipo de Dato Datetime
     *
     */
    const TYPE_DATETIME = 'DATETIME';

    /**
     * Tipo de Dato Char
     *
     */
    const TYPE_CHAR = 'CHAR';

    /**
     * Hace una conexion a la base de datos de SQLite
     *
     * @param array $config
     * @return bool
     */
    public function connect($config)
    {

        if (!extension_loaded('sqlite')) {
            throw new KumbiaException('Debe cargar la extensión de PHP llamada sqlite');
        }
        if ($this->id_connection = sqlite_open(APP_PATH . 'config/sql/' . $config['name'])) {
            return true;
        } else {
            throw new KumbiaException($this->error('No se puede conectar a la base de datos'));
        }
    }

    /**
     * Efectua operaciones SQL sobre la base de datos
     *
     * @param string $sqlQuery
     * @return resource or false
     */
    function query($sqlQuery)
    {
        $this->debug($sqlQuery);
        if ($this->logger) {
            Logger::debug($sqlQuery);
        }

        $this->last_query = $sqlQuery;
        if ($resultQuery = @sqlite_query($this->id_connection, $sqlQuery)) {
            $this->last_result_query = $resultQuery;
            return $resultQuery;
        } else {
            throw new KumbiaException($this->error(" al ejecutar <em>'$sqlQuery'</em>"));
        }
    }

    /**
     * Cierra la Conexión al Motor de Base de datos
     */
    function close()
    {
        if ($this->id_connection) {
            return sqlite_close($this->id_connection);
        } else {
            return false;
        }
    }

    /**
     * Devuelve fila por fila el contenido de un select
     *
     * @param resource $resultQuery
     * @param int $opt
     * @return array
     */
    function fetch_array($resultQuery='', $opt=SQLITE_BOTH)
    {

        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }

        return sqlite_fetch_array($resultQuery, $opt);
    }

    /**
     * Constructor de la Clase
     *
     * @param array $config
     */
    function __construct($config)
    {
        $this->connect($config);
    }

    /**
     * Devuelve el numero de filas de un select
     */
    function num_rows($resultQuery='')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = sqlite_num_rows($resultQuery)) !== false) {
            return $numberRows;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el nombre de un campo en el resultado de un select
     *
     * @param int $number
     * @param resource $resultQuery
     * @return string
     */
    function field_name($number, $resultQuery='')
    {

        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($fieldName = sqlite_field_name($resultQuery, $number)) !== false) {
            return $fieldName;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Se Mueve al resultado indicado por $number en un select
     *
     * @param int $number
     * @param resource $resultQuery
     * @return boolean
     */
    function data_seek($number, $resultQuery='')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($success = sqlite_rewind($resultQuery, $number)) !== false) {
            return $success;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Numero de Filas afectadas en un insert, update o delete
     *
     * @param resource $resultQuery
     * @return int
     */
    function affected_rows($resultQuery='')
    {

        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = pg_affected_rows($resultQuery)) !== false) {
            return $numberRows;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el error de SQLite
     *
     * @return string
     */
    function error($err='')
    {
        if (!$this->id_connection) {
            $this->last_error = sqlite_last_error($this->id_connection) ? sqlite_last_error($this->id_connection) . $err : "[Error Desconocido en SQLite \"$err\"]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }
            return $this->last_error;
        }
        $this->last_error = 'SQLite error: ' . sqlite_error_string(sqlite_last_error($this->id_connection));
        $this->last_error.= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }
        return $this->last_error;
    }

    /**
     * Devuelve el no error de SQLite
     *
     * @return int
     */
    function no_error()
    {
        return 0; //Codigo de Error?
    }

    /**
     * Devuelve el ultimo id autonumerico generado en la BD
     *
     * @return int
     */
    public function last_insert_id($table='', $primary_key='')
    {
        $last_id = $this->fetch_one("SELECT COUNT(*) FROM $table");
        return $last_id[0];
    }

    /**
     * Verifica si una tabla existe o no
     *
     * @param string $table
     * @return boolean
     */
    function table_exists($table, $schema='')
    {
        $table = addslashes(strtolower($table));
        if (strpos($table, ".")) {
            list($schema, $table) = explode(".", $table);
        }
        $num = $this->fetch_one("SELECT COUNT(*) FROM sqlite_master WHERE name = '$table'");
        return $num[0];
    }

    /**
     * Devuelve un LIMIT valido para un SELECT del RBDM
     *
     * @param string $sql consulta sql
     * @return string
     */
    public function limit($sql)
    {
        $params = Util::getParams(func_get_args());
        $sql_new = $sql;

        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $sql_new.=" LIMIT $params[limit]";
        }

        if (isset($params['offset']) && is_numeric($params['offset'])) {
            $sql_new.=" OFFSET $params[offset]";
        }

        return $sql_new;
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
     * @param string $table
     * @param array $definition
     * @return boolean|null
     */
    public function create_table($table, $definition, $index=array())
    {

    }

    /**
     * Listar las tablas en la base de datos
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT name FROM sqlite_master WHERE type='table' " .
                "UNION ALL SELECT name FROM sqlite_temp_master " .
                "WHERE type='table' ORDER BY name");
    }

    /**
     * Listar los campos de una tabla
     *
     * @param string $table
     * @return array
     */
    public function describe_table($table, $schema='')
    {
        $fields = array();
        $results = $this->fetch_all("PRAGMA table_info($table)");
        //var_dump($results); die();
        foreach ($results as $field) {
            $fields[] = array(
                "Field" => $field["name"],
                "Type" => $field["type"],
                "Null" => $field["notnull"] == '0' ? "YES" : "NO",
                "Key" => $field['pk'] == 1 ? "PRI" : ""
            );
        }
        return $fields;
    }

    /**
     * Devuelve la ultima sentencia sql ejecutada por el Adaptador
     *
     * @return string
     */
    public function last_sql_query()
    {
        return $this->last_query;
    }

}
