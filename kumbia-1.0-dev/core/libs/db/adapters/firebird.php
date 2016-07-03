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
 * Firebird/Interbase Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbFirebird extends DbBase implements DbBaseInterface
{

    /**
     * Resource de la Conexion a Firebird
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
     * Ultima sentencia SQL enviada a Firebird
     *
     * @var string
     */
    protected $last_query;
    /**
     * Ultimo error generado por Firebird
     *
     * @var string
     */
    public $last_error;

    /**
     * Resultado de Array Asociativo
     *
     */
    const DB_ASSOC = MYSQL_ASSOC;

    /**
     * Resultado de Array Asociativo y Numerico
     *
     */
    const DB_BOTH = MYSQL_BOTH;

    /**
     * Resultado de Array Numerico
     *
     */
    const DB_NUM = MYSQL_NUM;

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
     * Hace una conexion a la base de datos de Firebird
     *
     * @param array $config
     * @return bool
     */
    public function connect($config)
    {

        if (!extension_loaded('interbase')) {
            throw new KumbiaException('Debe cargar la extensión de PHP llamada php_interbase');
        }

        if (isset($config['host']) && $config['host']) {
            $id_con = ibase_connect("{$config['host']}:{$config['name']}", $config['username'], $config['password']);
        } else {
            $id_con = ibase_connect($config['name'], $config['username'], $config['password']);
        }

        if ($this->id_connection = $id_con) {
            return true;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Efectua operaciones SQL sobre la base de datos
     *
     * @param string $sql_query
     * @return resource or false
     */
    public function query($sql_query)
    {
        $this->debug($sql_query);
        if ($this->logger) {
            Logger::debug($sql_query);
        }

        $this->last_query = $sql_query;
        if ($result_query = ibase_query($this->id_connection, $sql_query)) {
            $this->last_result_query = $result_query;
            return $result_query;
        } else {
            throw new KumbiaException($this->error(" al ejecutar <em>\"$sql_query\"</em>"));
        }
    }

    /**
     * Cierra la Conexión al Motor de Base de datos
     *
     */
    public function close()
    {
        if ($this->id_connection) {
            return ibase_close();
        }
        return false;
    }

    /**
     * Devuelve fila por fila el contenido de un select
     *
     * @param resource $result_query
     * @param int $opt
     * @return array
     */
    public function fetch_array($result_query=NULL, $opt=MYSQL_BOTH)
    {
        $result=array();
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if ($opt == db::DB_BOTH) {
            $fetch = ibase_fetch_assoc($result_query);
            $result = array();
            $i = 0;
            foreach ($fetch as $key => $value) {
                $result[$key] = $value;
                $result[$i++] = $value;
            }
            return $result;
        }
        if ($opt == db::DB_ASSOC) {
            return ibase_fetch_assoc($result_query);
        }
        if ($opt == db::DB_NUM) {
            return ibase_fetch_row($result_query);
        }
        return $result;
    }

    /**
     * Constructor de la Clase
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->connect($config);
    }

    /**
     * Devuelve el numero de filas de un select
     */
    public function num_rows($result_query='')
    {
        // GDS Interbase no soporta esta funcion (No debe ser usada)
        return false;
    }

    /**
     * Devuelve el nombre de un campo en el resultado de un select
     *
     * @param int $number
     * @param resource $result_query
     * @return string
     */
    public function field_name($number, $result_query='')
    {

        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($fieldName = ibase_field_name($result_query, $number)) !== false) {
            return $fieldName;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Se Mueve al resultado indicado por $number en un select
     *
     * @param int $number
     * @param resource $result_query
     * @return boolean
     */
    public function data_seek($number, $result_query=NULL)
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($success = ibase_data_seek($result_query, $number)) !== false) {
            return $success;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Numero de Filas afectadas en un insert, update o delete
     *
     * @param resource $result_query
     * @return int
     */
    public function affected_rows($result_query=NULL)
    {
        if (($numberRows = ibase_affected_rows()) !== false) {
            return $numberRows;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el error de Firebird
     *
     * @return string
     */
    public function error($err='')
    {
        if (!$this->id_connection) {
            $this->last_error = ibase_errmsg() ? ibase_errmsg() : "[Error Desconocido en Firebird: $err]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }
            return $this->last_error;
        }
        $this->last_error = ibase_errmsg() ? ibase_errmsg() : "[Error Desconocido en Firebird: $err]";
        $this->last_error.= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }
        return $this->last_error;
    }

    /**
     * Devuelve un array del resultado de un select de un solo registro. Esta implementacion no
     *
     *
     * @param string $sql
     * @return array
     */
    public function fetch_one($sql)
    {
        $q = $this->query($sql);
        if ($q) {
            return $this->fetch_array($q);
        } else {
            return array();
        }
    }

    /**
     * Devuelve el no error de Firebird
     *
     * @return int
     */
    public function no_error()
    {
        return ibase_errcode();
    }

    /**
     * Devuelve el ultimo id autonumerico generado en la BD
     *
     * @return int
     */
    public function last_insert_id($table='', $primary_key='')
    {
        return ibase_insert_id($this->id_connection);
    }

    /**
     * Verifica si una tabla existe o no
     *
     * @param string $table
     * @return boolean
     */
    public function table_exists($table, $schema='')
    {
        $table = strtoupper(addslashes("$table"));
        // NOT LIKE 'RDB\$%'
        $num = $this->fetch_one("SELECT COUNT(*) FROM rdb\$relations WHERE rdb\$relation_name = '$table'");
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
            $sql_new.=" FIRST $params[limit]";
        }

        if (isset($params['offset']) && is_numeric($params['offset'])) {
            $sql_new.=" SKIP $params[offset]";
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
     * TODO:
     * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
     * - Agregar el tipo de tabla que debe usarse (Firebird)
     * - Soporte para campos autonumericos
     * - Soporte para llaves foraneas
     *
     * @param string $table
     * @param array $definition
     * @return resource
     */
    public function create_table($table, $definition, $index=array())
    {
        $create_sql = "CREATE TABLE $table (";
        if (!is_array($definition)) {
            throw new KumbiaException("Definición invalida para crear la tabla '$table'");
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
                    $gen = $this->fetch_one("SELECT COUNT(*) FROM RDB\$GENERATORS WHERE RDB\$GENERATOR_NAME = UPPER('{$table}_{$field}_seq')");
                    if (!$gen[0]) {
                        $this->query("INSERT INTO RDB\$GENERATORS (RDB\$GENERATOR_NAME) VALUES (UPPER('{$table}_{$field}_seq'))");
                    }
                    $this->query("SET GENERATOR {$table}_{$field}_seq TO 1;");
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
        return $this->fetch_all("SHOW TABLES");
    }

    /**
     * Listar los campos de una tabla
     *
     * @param string $table
     * @return array
     */
    public function describe_table($table, $schema='')
    {
        if ($schema == '') {
            return $this->fetch_all("DESCRIBE $table");
        } else {
            return $this->fetch_all("DESCRIBE $schema.$table");
        }
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
