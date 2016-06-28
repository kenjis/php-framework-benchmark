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
 * Microsoft SQL Server Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbMsSQL extends DbBase implements DbBaseInterface  {

    /**
     * Resource de la Conexión a MsSQL
     *
     * @var resource
     */
    public $id_connection;

    /**
     * Último Resultado de una Query
     *
     * @var resource
     */
    public $last_result_query;

    /**
     * Última sentencia SQL enviada a MsSQL
     *
     * @var string
     */
    protected $last_query;

    /**
     * Último error generado por MsSQL
     *
     * @var string
     */
    public $last_error;

    /**
     * Resultado de Array Asociativo
     *
     */
    const DB_ASSOC = MSSQL_ASSOC;

    /**
     * Resultado de Array Asociativo y Numérico
     *
     */
    const DB_BOTH = MSSQL_BOTH;

    /**
     * Resultado de Array Numérico
     *
     */
    const DB_NUM = MSSQL_NUM;

    /**
     * Tipo de Dato Integer
     *
     */
    const TYPE_INTEGER = 'INT';

    /**
     * Tipo de Dato Date
     *
     */
    const TYPE_DATE = 'SMALLDATETIME';

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
     * Hace una conexión a la base de datos de MsSQL
     *
     * @param array $config
     * @return bool
     */
    public function connect($config){
        if(!extension_loaded('mssql')){
            throw new KumbiaException('Debe cargar la extensión de PHP llamada php_mssql');
        }
        if(!isset($config['port']) || !$config['port']) {
            $config['port'] = 1433;
        }
        //if($this->id_connection = mssql_connect("{$config['host']},{$config['port']}", $config['username'], $config['password'], true)){
        if($this->id_connection = mssql_connect($config['host'], $config['username'], $config['password'], true)){
            if($config['name']!=='') {
                if(!mssql_select_db($config['name'], $this->id_connection)){
                    throw new KumbiaException($this->error());
                }
            }
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
    public function query($sql_query){
        $this->debug($sql_query);
        if($this->logger){
            Logger::debug($sql_query);
        }

        $this->last_query = $sql_query;
        if($result_query = mssql_query($sql_query, $this->id_connection)){
            $this->last_result_query = $result_query;
            return $result_query;
        }else{
            throw new KumbiaException($this->error(" al ejecutar <em>\"$sql_query\"</em>"));
        }
    }
    /**
     * Cierra la Conexión al Motor de Base de datos
     */
    public function close(){
        if($this->id_connection) {
            return mssql_close();
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
    public function fetch_array($result_query='', $opt=MSSQL_BOTH){

        if(!$result_query){
            $result_query = $this->last_result_query;
            if(!$result_query){
                return false;
            }
        }
        return mssql_fetch_array($result_query, $opt);
    }
    /**
     * Constructor de la Clase
     *
     * @param array $config
     */
    public function __construct($config){
        $this->connect($config);
    }
    /**
     * Devuelve el número de filas de un select
     */
    public function num_rows($result_query=''){

        if(!$result_query){
            $result_query = $this->last_result_query;
            if(!$result_query){
                return false;
            }
        }
        if(($number_rows = mssql_num_rows($result_query))!==false){
            return $number_rows;
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
    public function field_name($number, $result_query=''){

        if(!$result_query){
            $result_query = $this->last_result_query;
            if(!$result_query){
                return false;
            }
        }
        if(($fieldName = mssql_field_name($result_query, $number))!==false){
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
    public function data_seek($number, $result_query=''){
        if(!$result_query){
            $result_query = $this->last_result_query;
            if(!$result_query){
                return false;
            }
        }
        if(($success = mssql_data_seek($result_query, $number))!==false){
            return $success;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Número de Filas afectadas en un insert, update o delete
     *
     * @param resource $result_query
     * @return int
     */
    public function affected_rows($result_query=''){
        if(($numberRows = mssql_affected_rows())!==false){
            return $numberRows;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el error de MsSQL
     *
     * @return string
     */
    public function error($err=''){
        if(!$this->id_connection){
            $this->last_error = mssql_get_last_message() ? mssql_get_last_message() : "[Error Desconocido en MsSQL: $err]";
            if($this->logger){
                Logger::error($this->last_error);
            }
            return $this->last_error;
        }
        $this->last_error = mssql_get_last_message() ? mssql_get_last_message() : "[Error Desconocido en MsSQL: $err]";
        $this->last_error.= $err;
        if($this->logger){
            Logger::error($this->last_error);
        }
        return $this->last_error;
    }
    /**
     * Devuelve el no error de MsSQL
     *
     * @return int
     */
    public function no_error(){
        return mssql_errno();
    }
    /**
     * Devuelve el último id autonumérico generado en la BD
     *
     * @return int
     */
    public function last_insert_id($table='', $primary_key=''){

        //$id = false;
        $result = mssql_query("select max({$primary_key}) from $table");
        if ($row = mssql_fetch_row($result)) {
            $this->id_connection = trim($row[0]);
        }
        mssql_free_result($result);
        return $this->id_connection;
    }
    /**
     * Verifica si una tabla existe o no
     *
     * @param string $table
     * @return boolean
     */
    public function table_exists($table, $schema=''){
        $table = addslashes("$table");
        if($schema==''){
            $num = $this->fetch_one("SELECT COUNT(*) FROM
                        INFORMATION_SCHEMA.TABLES
                        WHERE TABLE_NAME = '$table'");
        } else {
            $schema = addslashes("$schema");
            $num = $this->fetch_one("SELECT COUNT(*) FROM
                        INFORMATION_SCHEMA.TABLES
                        WHERE TABLE_NAME = '$table'
                        AND TABLE_SCHEMA = '$schema'");
        }
        return $num[0];
    }
    /**
     * Devuelve un LIMIT válido para un SELECT del RBDM
     *
     * @param string $sql consulta sql
     * @return string
     */
    public function limit($sql){
        $params = Util::getParams(func_get_args());

        //TODO: añadirle el offset
        if(isset($params['limit'])){
            $sql = str_ireplace("SELECT ", "SELECT TOP $params[limit] ", $sql);
        }
        return $sql;
    }

    /**
     * Borra una tabla de la base de datos
     *
     * @param string $table
     * @return resource
     */
    public function drop_table($table, $if_exists=true){
        if($if_exists){
            $sql = "IF EXISTS(SELECT TABLE_NAME FROM
            INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table')
            DROP TABLE $table;";
            return $this->query($sql);
        } else {
            return $this->query("DROP TABLE $table");
        }
    }
    /**
     * Listar las tablas en la base de datos
     *
     * @return array
     */
    public function list_tables(){
        return $this->fetch_all("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES");
    }

    /**
     * Listar los campos de una tabla
     *
     * @param string $table
     * @return array
     */
    /*
    public function describe_table($table, $schema=''){
        $sql = "SELECT A.name as Field,
            (case when A.isnullable=0 then 'NO' when A.isnullable=1 then 'YES' end) as 'Null',
            (case when A.colorder=1 then 'PRI' when A.colorder>1 then '' end ) as 'Key',
            convert(varchar, C.name) + '(' + convert(varchar, (A.length))  + ')' as 'Type',
            (case when A.cdefault=0 then 'NULL' when A.cdefault<>0 then '0' end) as 'Default'
            FROM syscolumns A
            left join  sysobjects B on A.id = B.id
            left join systypes C on C.xtype = A.xtype
            WHERE  B.name = '$table'";
        return $this->fetch_all($sql);
    }
    */
        public function describe_table($table, $schema=''){
                $describeTable = $this->fetch_all("exec sp_columns @table_name = '$table'");
                $finalDescribe = array();
                foreach($describeTable as $field){
                        $finalDescribe[] = array(
                                'Field' => $field['COLUMN_NAME'],
                                'Type' => $field['LENGTH'] ? $field['TYPE_NAME'] : $field['TYPE_NAME'].'('.$field['LENGTH'].')',
                                'Null' => $field['NULLABLE'] == 1 ? 'YES' : 'NO'
                        );
                }
                $describeKeys = $this->fetch_all("exec sp_pkeys @table_name = '$table'");
                foreach($describeKeys as $field){
                        for($i=0;$i<=count($finalDescribe)-1;++$i){
                                if($finalDescribe[$i]['Field']==$field['COLUMN_NAME']){
                                        $finalDescribe[$i]['Key'] = 'PRI';
                                } else {
                                        $finalDescribe[$i]['Key'] = "";
                                }
                        }
                }
                return $finalDescribe;
        }


    /**
     * Devuelve fila por fila el contenido de un select
     *
     * @param resource $result_query
     * @return object
     */
    public function fetch_object($result_query = NULL){
        if(!$result_query){
            $result_query = $this->last_result_query;
        }
        return mssql_fetch_object($result_query);
    }

    public function create_table ($table, $definition, $index = array()){

    }
}
