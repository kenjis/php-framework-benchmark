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
 * Informix Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbInformix extends DbBase implements DbBaseInterface
{

    /**
     * Resource de la Conexion a Informix
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
     * Ultima sentencia SQL enviada a Informix
     *
     * @var string
     */
    protected $last_query;
    /**
     * Ultimo error generado por Informix
     *
     * @var string
     */
    public $last_error;
    /**
     * Indica si query devuelve registros o no;
     *
     * @var boolean
     */
    private $return_rows = true;
    /**
     * Emula un limit a nivel de Adaptador para Informix
     *
     * @var int
     */
    private $limit = -1;
    /**
     * Numero de limit actual para fetch_array
     *
     * @var int
     */
    private $actual_limit = 0;

    /**
     * Resultado de Array Asociativo
     *
     */
    const DB_ASSOC = 1;

    /**
     * Resultado de Array Asociativo y Numerico
     *
     */
    const DB_BOTH = 2;

    /**
     * Resultado de Array Numerico
     *
     */
    const DB_NUM = 3;

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
     * Hace una conexión a la base de datos de Informix
     *
     * @param array $config
     * @return bool
     */
    public function connect($config)
    {
        if (!extension_loaded('informix')) {
            throw new KumbiaException('Debe cargar la extensión de PHP llamada php_ifx');
        }

        if ($this->id_connection = ifx_connect("{$config['name']}@{$config['host']}", $config['username'], $config['password'])) {
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

        // Los resultados que devuelven filas usan cursores tipo SCROLL
        if ($this->return_rows) {
            $result_query = ifx_query($sql_query, $this->id_connection, IFX_HOLD);
        } else {
            $result_query = ifx_query($sql_query, $this->id_connection);
        }
        $this->set_return_rows(true);
        if ($result_query === false) {
            throw new KumbiaException($this->error(" al ejecutar <em>\"$sql_query\"</em>"));
        } else {
            $this->last_result_query = $result_query;
            return $result_query;
        }
    }

    /**
     * Cierra la Conexión al Motor de Base de datos
     *
     */
    public function close()
    {
        if ($this->id_connection) {
            return ifx_close($this->id_connection);
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
    public function fetch_array($result_query=NULL, $opt=2)
    {

        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        $fetch = ifx_fetch_row($result_query, $opt);

        // Informix no soporta limit por eso hay que emularlo
        if ($this->limit != -1) {
            if ($this->actual_limit >= $this->limit) {
                $this->limit = -1;
                $this->actual_limit = 0;
                return false;
            } else {
                $this->actual_limit++;
                if ($this->actual_limit == $this->limit) {
                    $this->limit = -1;
                    $this->actual_limit = 0;
                }
            }
        }

        // Informix no soporta fetch numerico, solo asociativo
        if (!is_array($fetch) || ($opt == self::DB_ASSOC)) {
            return $fetch;
        }
        if ($opt == self::DB_BOTH) {
            $result = array();
            $i = 0;
            foreach ($fetch as $key => $value) {
                $result[$key] = $value;
                $result[$i++] = $value;
            }
            return $result;
        }
        if ($opt == self::DB_NUM) {
            return array_values($fetch);
        }
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
     *
     * @param resource $result_query
     * @return int
     */
    public function num_rows($result_query=NULL)
    {

        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($number_rows = ifx_num_rows($result_query)) !== false) {

            // Emula un limit a nivel de adaptador
            if ($this->limit == -1) {
                return $number_rows;
            } else {
                return $this->limit < $number_rows ? $this->limit : $number_rows;
            }
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el nombre de un campo en el resultado de un select
     *
     * @param int $number
     * @param resource $result_query
     * @return string
     */
    public function field_name($number, $result_query=NULL)
    {

        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        $fields = ifx_fieldproperties($result_query);
        if (!is_array($fields)) {
            return false;
        }

        $fields = array_keys($fields);
        return $fields[$number];
    }

    /**
     * Se Mueve al resultado indicado por $number en un select
     * Hay problemas con este metodo hay problemas con curesores IFX_SCROLL
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
        if (($success = ifx_fetch_row($result_query, $number)) !== false) {
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
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($numberRows = ifx_affected_rows($result_query)) !== false) {
            return $numberRows;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el error de Informix
     *
     * @return string
     */
    public function error($err='')
    {
        if (!$this->id_connection) {
            $this->last_error = ifx_errormsg() ? ifx_errormsg() : "[Error Desconocido en Informix: $err]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }
            return $this->last_error;
        }
        $this->last_error = ifx_errormsg($this->id_connection) ? ifx_errormsg($this->id_connection) : "[Error Desconocido en Informix: $err]";
        $this->last_error.= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }
        return $this->last_error;
    }

    /**
     * Devuelve el no error de Informix
     *
     * @return int
     */
    public function no_error()
    {
        return ifx_error();
    }

    /**
     * Devuelve el ultimo id autonumerico generado en la BD
     *
     * @return int
     */
    public function last_insert_id($table='', $primary_key='')
    {
        $sqlca = ifx_getsqlca($this->last_result_query);
        return $sqlca["sqlerrd1"];
    }

    /**
     * Verifica si una tabla existe o no
     *
     * @param string $table
     * @return integer
     */
    public function table_exists($table, $schema='')
    {
        // Informix no soporta schemas
        $table = addslashes("$table");
        $num = $this->fetch_one("SELECT COUNT(*) FROM systables WHERE tabname = '$table'");
        return (int) $num[0];
    }

    /**
     * Devuelve un LIMIT valido para un SELECT del RBDM
     *
     * @param string $sql
     * @return string
     */
    public function limit($sql){
           /**
                 * No esta soportado por Informix
                 */
                return "$sql \n";
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
                $this->set_return_rows(false);
                return $this->query("DROP TABLE $table");
            } else {
                return true;
            }
        } else {
            $this->set_return_rows(false);
            return $this->query("DROP TABLE $table");
        }
    }

    /**
     * Crea una tabla utilizando SQL nativo del RDBM
     *
     * TODO:
     * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
     * - Agregar el tipo de tabla que debe usarse (Informix)
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
                    $field_def['type'] = "SERIAL";
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
        $this->set_return_rows(false);
        return $this->query($create_sql);
    }

    /**
     * Listar las tablas en la base de datos
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT tabname FROM systables WHERE tabtype = 'T' AND version <> 65537");
    }

    /**
     * Listar los campos de una tabla
     *
     * @param string $table
     * @return array
     */
    public function describe_table($table, $schema='')
    {

        // Informix no soporta schemas
        // TODO: No hay un metodo identificable para obtener llaves primarias
        // no nulos y tamaños reales de campos
        // Primary Key, Null
        $describe = $this->fetch_all("SELECT c.colname AS Field, c.coltype AS Type,
                'YES' AS NULL FROM systables t, syscolumns c WHERE
                c.tabid = t.tabid AND t.tabname = '$table' ORDER BY c.colno");
        $final_describe = array();
        foreach ($describe as $field) {
            //Serial
            if ($field['field'] == 'id') {
                $field["key"] = 'PRI';
                $field["null"] = 'NO';
            } else {
                $field["key"] = '';
            }
            if (substr($field['field'], -3) == '_id') {
                $field["null"] = 'NO';
            }
            if ($field['type'] == 262) {
                $field['type'] = "serial";
            }
            if ($field['type'] == 13) {
                $field['type'] = "varchar";
            }
            if ($field['type'] == 7) {
                $field['type'] = "date";
            }
            $final_describe[] = array(
                "Field" => $field["field"],
                "Type" => $field["type"],
                "Null" => $field["null"],
                "Key" => $field["key"]
            );
        }
        return $final_describe;
    }

    /**
     * Realiza una inserci&oacute;n (Sobreescrito para indicar que no devuelve registros)
     *
     * @param string $table
     * @param array $values
     * @param array $fields
     * @return boolean
     */
    public function insert($table, $values, $fields=null)
    {
        $this->set_return_rows(false);
        return parent::insert($table, $values, $fields);
    }

    /**
     * Actualiza registros en una tabla
     *
     * @param string $table
     * @param array $fields
     * @param array $values
     * @param string $where_condition
     * @return boolean
     */
    public function update($table, $fields, $values, $where_condition=null)
    {
        $this->set_return_rows(false);
        return parent::update($table, $fields, $values, $where_condition);
    }

    /**
     * Borra registros de una tabla!
     *
     * @param string $table
     * @param string $where_condition
     */
    public function delete($table, $where_condition)
    {
        $this->set_return_rows(false);
        return parent::delete($table, $where_condition);
    }

    /**
     * Indica internamente si el resultado obtenido es devuelve registros o no
     *
     * @param boolean $value
     */
    public function set_return_rows($value=true)
    {
        $this->return_rows = $value;
    }

    /**
     * Inicia una transacci&oacute;n si es posible
     *
     */
    public function begin()
    {
        $this->set_return_rows(false);
        return $this->query("BEGIN WORK");
    }

    /**
     * Cancela una transacci&oacute;n si es posible
     *
     */
    public function rollback()
    {
        $this->set_return_rows(false);
        return $this->query("ROLLBACK");
    }

    /**
     * Hace commit sobre una transacci&oacute;n si es posible
     *
     */
    public function commit()
    {
        $this->set_return_rows(false);
        return $this->query("COMMIT");
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
