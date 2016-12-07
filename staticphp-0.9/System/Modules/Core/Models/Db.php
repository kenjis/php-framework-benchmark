<?php

namespace Core\Models;

use Core\Models\Load;

/**
 * Database wrapper for pdo.
 */
class Db
{
    /**
     * Holds references to database links.
     *
     * @var mixed[][]
     * @access private
     * @static
     */
    private static $db_links;

    /**
     * Cache for last statment.
     *
     * @var resource
     * @access private
     * @static
     */
    private static $last_statement;

    /**
     * Init connection to the database.
     *
     * Connection can be made by passing configuration array to $config parameter or
     * by passing a name of the connection that has been set up in Application/Config/Db.php (see example in System/Config/Db.php).
     *
     * @example models\Db::init();
     * @example models\Db::init(null, 'second');
     * @example models\Db::init([
     *              'string' => 'pgsql:host=localhost;dbname=',
     *              'username' => 'username',
     *              'password' => 'password',
     *              'charset' => 'UTF8',
     *              'persistent' => true,
     *              'wrap_column' => '`', // ` - for mysql, " - for postgresql
     *              'fetch_mode_objects' => false,
     *              'debug' => true,
     *          ], 'pgsql1');
     * @access public
     * @static
     * @param  mixed    $config (default: null)
     * @param  string   $name   (default: 'default')
     * @return resource Returns pdo instance.
     */
    public static function init($config = null, $name = 'default')
    {
        // Check if there is such configuration
        if (empty($config)) {
            if (empty(Load::$config['db']['pdo'][$name])) {
                return false;
            }

            $config = Load::$config['db']['pdo'][$name];
        }

        // Don't make a new connection if there is one connected with the name
        if (!empty(self::$db_links[$name]['link'])) {
            return self::$db_links[$name]['link'];
        }

        // Set config
        self::$db_links[$name]['config'] = $config;

        // Options
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_DEFAULT_FETCH_MODE => (!empty($config['fetch_mode_objects']) ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC),

        ];
        if (isset($config['persistent'])) {
            $options[\PDO::ATTR_PERSISTENT] = $config['persistent'];
        }

        // Open new connection to DB
        self::$db_links[$name]['link'] = new \PDO($config['string'], $config['username'], $config['password'], $options);

        // Set encoding - mysql only
        if (!empty($config['charset']) && self::$db_links[$name]['link']->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql') {
            self::$db_links[$name]['link']->exec('SET NAMES '.$config['charset'].';');
        }

        return self::$db_links[$name]['link'];
    }

    /**
     * Make a query.
     *
     * Should be used for insert and update queries, but also can be used as iterator for select queries.
     *
     * @example models\Db::query('INSERT INTO posts (title) VALUES (?)', ['New post title'], 'pgsql1');
     * @example $query = models\Db::query('SELECT * FROM posts', null, 'pgsql1');<br />
     *          foreach ($query as $item)<br />
     *          {<br />
     *              // Do something with the $item<br />
     *          }
     * @access public
     * @static
     * @param  string       $query
     * @param  mixed[]      $data  (default: null)
     * @param  string       $name  (default: 'default')
     * @return PDOStatement Returns statement created by query.
     */
    public static function query($query, $data = null, $name = 'default')
    {
        $db_link = &self::$db_links[$name]['link'];

        if (empty($query)) {
            return null;
        }

        if (empty($db_link)) {
            throw new \Exception('No connection to database');
        }

        // Do request
        if (!empty(self::$db_links[$name]['config']['debug'])) {
            Load::startTimer();
        }

        self::$last_statement = $db_link->prepare($query);
        self::$last_statement->execute((array) $data);

        if (!empty(self::$db_links[$name]['config']['debug'])) {
            $log = $query;
            if (!empty($data)) {
                $log_data = array_map(function($item) {
                    return (is_integer($item) == true ? $item : "'".$item."'");
                }, (array)$data);
                $log = str_replace(array_pad([], substr_count($query, '?'), '?'), $log_data, $query);
            }
            Load::stopTimer($log);
        }

        // Return last statement
        return self::$last_statement;
    }

    /**
     * Fetch one row of query. Useful if you need only one record returned.
     *
     * @example models\Db::fetch('SELECT * FROM posts WHERE id = ?', [$post_id], 'pgsql1');
     * @access public
     * @static
     * @param  string       $query
     * @param  mixed[]      $data  (default: [])
     * @param  string       $name  (default: 'default')
     * @return array|object Returns array or object of the one record from database.
     */
    public static function fetch($query, $data = [], $name = 'default')
    {
        return self::query($query, $data, $name)->fetch();
    }

    /**
     * Fetch all rows.
     *
     * @access public
     * @static
     * @param  string           $query
     * @param  mixed[]          $data  (default: [])
     * @param  string           $name  (default: 'default')
     * @return array[]|object[] Returns array of arrays or objects containing all rows returned by database.
     */
    public static function fetchAll($query, $data = [], $name = 'default')
    {
        return self::query($query, $data, $name)->fetchAll();
    }

    /**
     * Make insert sql string and exeute it from associative array of data..
     *
     * @example models\Db::insert('posts', ['title' => 'Different title', '!active' => 1]);
     *          will make and execute query: INSERT INTO posts (title, active) VALUES ('Different title', 1).
     * @access public
     * @static
     * @param  string       $table
     * @param  mixed        $data
     * @param  string       $name  (default: 'default')
     * @return PDOStatement Returns statement created by query.
     */
    public static function insert($table, $data, $name = 'default')
    {
        foreach ((array) $data as $key => $value) {
            if ($key[0] == '!') {
                $keys[] = self::$db_links[$name]['config']['wrap_column'].substr($key, 1).self::$db_links[$name]['config']['wrap_column'];
                $values[] = $value;
            } else {
                $keys[] = self::$db_links[$name]['config']['wrap_column'].$key.self::$db_links[$name]['config']['wrap_column'];
                $values[] = '?';
                $params[] = $value;
            }
        }

        // Compile KEYS and VALUES
        $keys = implode(', ', $keys);
        $values = implode(', ', $values);

        // Run Query
        return self::query("INSERT INTO {$table} ({$keys}) VALUES ({$values})", $params, $name);
    }

    /**
     * Make update sql string and exeute it from associative array of data.
     *
     * @example models\Db::update('posts', ['title' => 'Different title', '!active' => 1], ['id' => $post_id]);
     *          will make and execute query: UPDATE posts SET title = 'Different title', active = 1 WHERE id = 2.
     * @access public
     * @static
     * @param  string       $table
     * @param  mixed        $data
     * @param  mixed        $where
     * @param  string       $name  (default: 'default')
     * @return PDOStatement Returns statement created by query.
     */
    public static function update($table, $data, $where, $name = 'default')
    {
        // Make SET
        foreach ((array) $data as $key => $value) {
            if ($key[0] == '!') {
                $set[] = self::$db_links[$name]['config']['wrap_column'].substr($key, 1).self::$db_links[$name]['config']['wrap_column']." = {$value}";
            } else {
                $set[] = self::$db_links[$name]['config']['wrap_column'].$key.self::$db_links[$name]['config']['wrap_column'].' = ?';
                $params[] = $value;
            }
        }

        // Make WHERE
        foreach ((array) $where as $key => $value) {
            $c = '=';
            $expl = explode(' ', $key);
            if (count($expl) > 1) {
                $key = $expl[0];
                $c = $expl[1];
            }

            if ($key[0] == '!') {
                $cond[] = self::$db_links[$name]['config']['wrap_column'].substr($key, 1).self::$db_links[$name]['config']['wrap_column']." {$c} {$value}";
            } else {
                $cond[] = self::$db_links[$name]['config']['wrap_column'].$key.self::$db_links[$name]['config']['wrap_column']." {$c} ?";
                $params[] = $value;
            }
        }

        // Compile SET and WHERE
        $set = implode(', ', $set);
        if (!empty($cond)) {
            $cond = 'WHERE '.implode(' AND ', $cond);
        }

        // Run Query
        return self::query("UPDATE {$table} SET {$set} {$cond};", $params, $name);
    }

    /**
     * Initiates a database transaction on a database link by $name.
     *
     * Turns off autocommit mode. While autocommit mode is turned off,
     * changes made to the database via the PDO object instance are not
     * committed until you end the transaction by calling Db::commit().
     * Calling Db::rollBack() will roll back all changes to the database
     * and return the connection to autocommit mode.
     *
     * @see Db::commit()
     * @access public
     * @static
     * @param string $name (default: 'default')
     * @return bool Returns true on success or false on failure.
     */
    public static function beginTransaction($name = 'default')
    {
        $db_link = &self::$db_links[$name]['link'];
        return $db_link->beginTransaction();
    }

    /**
     * Commit a transaction on a database link by $name.
     *
     * @access public
     * @static
     * @param string $name (default: 'default')
     * @return bool Returns true on success or false on failure.
     */
    public static function commit($name = 'default')
    {
        $db_link = &self::$db_links[$name]['link'];
        return $db_link->commit();
    }

    /**
     * Rolls back a transaction on a database link by $name.
     *
     * @access public
     * @static
     * @param string $name (default: 'default')
     * @return bool Returns true on success or false on failure.
     */
    public static function rollBack($name = 'default')
    {
        $db_link = &self::$db_links[$name]['link'];
        return $db_link->rollBack();
    }

    /**
     * Get PDO object connection link to the database by $name.
     *
     * @access public
     * @static
     * @param  string $name (default: 'default')
     * @return PDO    Returns php's PDO object.
     */
    public static function &dbLink($name = 'default')
    {
        return self::$db_links[$name]['link'];
    }

    /**
     * Get last statement that was run on database through this (models\db) class.
     *
     * @access public
     * @static
     * @return PDOStatement Returns statement created by query.
     */
    public static function &lastStatement()
    {
        if (!empty(self::$last_statement)) {
            return self::$last_statement;
        }
    }

    /**
     * Get last query that was run on database through this (models\db) class.
     *
     * @access public
     * @static
     * @return string Returns string of the query.
     */
    public static function lastQuery()
    {
        return empty(self::$last_statement) ? null : self::$last_statement->queryString;
    }

    /**
     * Get the last insert id created by database.
     *
     * Id can be returned by pdo in-built method by setting $sql to false or by querying database.
     * If $sequence_name is provided, it will aptempt to only get last value for that sequence.
     *
     * @access public
     * @static
     * @param  string   $sequence_name (default: '')
     * @param  bool     $sql           (default: false)
     * @param  string   $name          (default: 'default')
     * @return int|null Returns last insert id on success or null on failure.
     */
    public static function lastInsertId($sequence_name = '', $sql = false, $name = 'default')
    {
        if (empty($sql)) {
            return self::$db_links[$name]['link']->lastInsertId($sequence_name);
        } else {
            if (empty($sequence_name)) {
                $res = self::query('SELECT LAST_INSERT_ID() as id');
            } else {
                $res = self::query('SELECT currval(?) as id', $sequence_name);
            }

            return (empty($res->id) ? null : $res->id);
        }
    }
}
