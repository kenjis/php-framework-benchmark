<?php


namespace Core\Models;

/**
 * LogLevel class.
 *
 * Defines multiple error level constants.
 */
class LogLevel
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
}

/**
 * Core class for loading resources, setting timers for profiling and error handling.
 */
class Load
{
    /**
     * Global configuration array of mixed data.
     *
     * (default value: [])
     *
     * @var array
     * @access public
     * @static
     */
    public static $config = [];

    /**
     * Array for started timers.
     *
     * (default value: [])
     *
     * @var array
     * @access protected
     * @static
     */
    protected static $started_timers = [];

    /**
     * Array for finished timers.
     *
     * (default value: [])
     *
     * @var array
     * @access protected
     * @static
     */
    protected static $finished_timers = [];

    /**
     * Array for log entries.
     *
     * (default value: [])
     *
     * @var array
     * @access protected
     * @static
     */
    protected static $logs = [];

    /*
    |--------------------------------------------------------------------------
    | Configuration Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get value from config by $key.
     *
     * Optionally set default value if there are no config value by $key found.
     *
     * @access public
     * @static
     * @param  string     $key
     * @param  mixed|null $default (default: null)
     * @return mixed      Returns mixed data
     */
    public static function &get($key, $default = null)
    {
        return (isset(self::$config[$name]) ? self::$config[$name] : $default);
    }

    /**
     * Set configuration value.
     *
     * @access public
     * @static
     * @param  string $name
     * @param  mixed  $value
     * @return mixed  Returns new value
     */
    public static function set($name, $value)
    {
        return (self::$config[$name] = $value);
    }

    /**
     * Merge configuration values.
     *
     * Merge configuration value by $name with $value. If $overwrite is set to true, same key values will be overwritten.
     *
     * @access public
     * @static
     * @param  string $name
     * @param  mixed  $value
     * @param  bool   $owerwrite (default: true)
     * @return void
     */
    public static function merge($name, $value, $owerwrite = true)
    {
        if (!isset(self::$config[$name])) {
            return (self::$config[$name] = $value);
        }

        switch (true) {
            case is_array(self::$config[$name]):
                if (empty($owerwrite)) {
                    return (self::$config[$name] += $value);
                } else {
                    return (self::$config[$name] = array_merge((array) self::$config[$name], (array) $value));
                }
                break;

            case is_object(self::$config[$name]):
                if (empty($owerwrite)) {
                    return (self::$config[$name] = (object) ((array) self::$config[$name] + (array) $value));
                } else {
                    return (self::$config[$name] = (object) array_merge((array) self::$config[$name], (array) $value));
                }
                break;

            case is_int(self::$config[$name]):
            case is_float(self::$config[$name]):
                return (self::$config[$name] += $value);
                break;

            case is_string(self::$config[$name]):
            default:
                return (self::$config[$name] .= $value);
                break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Filesystem Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Generate UUID v4.
     *
     * @author http://php.net/manual/en/function.uniqid.php#94959
     * @access public
     * @static
     * @return void
     */
    public static function uuid4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate sha1 hash from random v4 uuid.
     *
     * @see Load::uuid4()
     * @access public
     * @static
     * @return void
     */
    public static function randomHash()
    {
        return sha1(self::uuid4());
    }

    /**
     * Generate hashed path.
     *
     * Generate hashed path to avoid reaching files per directory limit ({@link http://stackoverflow.com/a/466596}).
     * By default it will create directories 2 levels deep and 2 symbols long, for example, for a filename /www/upload/files/image.jpg,
     * it will generate filename /www/upload/files/ge/ma/image.jpg and optionally create all directories. It is also suggested
     * for cases where image name is not important to set $randomize to true. This way generated filename becomes a sha1 hash
     * and will provide better file distribution between directories.
     *
     * @see Load::randomHash()
     * @access public
     * @static
     * @param  string   $filename
     * @param  bool     $randomize             (default: false)
     * @param  bool     $create_directories    (default: false)
     * @param  int      $levels_deep           (default: 2)
     * @param  int      $directory_name_length (default: 2)
     * @return string[] An array of string objects:
     *                  <ul>
     *                  <li>'hash_dir' Contains only hashed directory (e.g. ge/ma);</li>
     *                  <li>'hash_file' hash_dir + filename (ge/ma/image.jpg);</li>
     *                  <li>'filename' Filename without extension;</li>
     *                  <li>'ext' File extension;</li>
     *                  <li>'dir' Absolute path to file's containing directory, including hashed directories (/www/upload/files/ge/ma/);</li>
     *                  <li>'file' Full path to a file.</li>
     *                  </ul>
     */
    public static function hashedPath($filename, $randomize = false, $create_directories = false, $levels_deep = 2, $directory_name_length = 2)
    {
        // Explode path to get filename
        $parts = explode(DIRECTORY_SEPARATOR, $filename);

        // Predefine array elements
        $data['hash_dir'] = '';
        $data['hash_file'] = '';

        // Get filename and extension
        $data['filename'] = explode('.', array_pop($parts));
        $data['ext'] = (count($data['filename']) > 1 ? array_pop($data['filename']) : '');
        $data['filename'] = (empty($randomize) ? implode('.', $data['filename']) : self::randomHash());

        if (strlen($data['filename']) < $levels_deep * $directory_name_length) {
            throw new Exception('Filename length too small to satisfy how much sub-directories and how long each directory name should be made.');
        }

        // Put directory together
        $dir = (empty($parts) ? '' : implode('/', $parts).'/');

        // Create hashed directory
        for ($i = 1; $i <= $levels_deep; ++$i) {
            $data['hash_dir'] .= substr($data['filename'], -1 * $directory_name_length * $i, $directory_name_length).'/';
        }

        // Put other stuff together
        $data['dir'] = str_replace($data['hash_dir'], '', $dir).$data['hash_dir'];
        $data['file'] = $data['dir'].$data['filename'].(empty($data['ext']) ? '' : '.'.$data['ext']);
        $data['hash_file'] = $data['hash_dir'].$data['filename'].(empty($data['ext']) ? '' : '.'.$data['ext']);

        // Create directories
        if (!empty($create_directories) && !is_dir($data['dir'])) {
            mkdir($data['dir'], 0777, true);
        }

        return $data;
    }

    /**
     * Delete file and directories created by Load::hashedPath.
     *
     * @see Load::hashedPath
     * @access public
     * @static
     * @param  string $filename
     * @return void
     */
    public static function deleteHashedFile($filename)
    {
        $path = self::hashedPath($filename);

        // Trim off / from end
        $path['hash_dir'] = rtrim($path['hash_dir'], '/');
        $path['dir'] = rtrim($path['dir'], '/');

        // Explode hash directories to get the count of them
        $expl = explode('/', $path['hash_dir']);

        // Unlink the file
        if (is_file($path['file'])) {
            unlink($path['file']);
        }

        // Remove directories
        foreach ($expl as $null) {
            if (!@rmdir($path['dir'])) {
                break;
            }

            $path['dir'] = dirname($path['dir']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | File Loading
    |--------------------------------------------------------------------------
    */

    /**
     * Load configuration files.
     *
     * Load configuration files from current application's config directory (APP_PATH/config) or
     * from other application by providing name in $project parameter.
     *
     * @access public
     * @static
     * @param  string|array $files
     * @param  string|null  $project (default: null)
     * @return void
     */
    public static function config($files, $module = null, $project = null)
    {
        $config = & self::$config;
        foreach ((array) $files as $key => $name) {
            $project1 = $project;
            if (is_numeric($key) === false) {
                $project1 = $name;
                $name = $key;
            }

            $file = '';
            if (!empty($module)) {
                $file = (empty($project1) ? APP_MODULES_PATH : BASE_PATH.$project1.DS.'Modules'.DS);
                $file .= $module.DS;
            } else {
                $file = (empty($project1) ? APP_PATH : BASE_PATH.$project1.DS);
            }
            $file .= 'Config'.DS.$name.'.php';

            require($file);
        }
    }

    /**
     * Load controller files.
     *
     * Load controller files from current application's $module/controllers directory or
     * from other $project/$module/controllers by providing $project name.
     *
     * @access public
     * @static
     * @param  string|array $files
     * @param  string|null  $project (default: null)
     * @return void
     */
    public static function controller($files, $module, $project = null)
    {
        foreach ((array) $files as $key => $name) {
            $project1 = $project;
            if (is_numeric($key) === false) {
                $project1 = $name;
                $name = $key;
            }
            require((empty($project1) ? APP_MODULES_PATH : BASE_PATH.$project1.DS.'Modules'.DS).'Controllers'.DS.$name.'.php');
        }
    }

    /**
     * Load model files.
     *
     * Load model files from current application's $module/models directory or
     * from other $project/$module/models by providing $project name.
     *
     * @access public
     * @static
     * @param  string|array $files
     * @param  string|null  $project (default: null)
     * @return void
     */
    public static function model($files, $module, $project = null)
    {
        foreach ((array) $files as $key => $name) {
            $project1 = $project;
            if (is_numeric($key) === false) {
                $project1 = $name;
                $name = $key;
            }
            require((empty($project1) ? APP_MODULES_PATH : BASE_PATH.$project1.DS.'Modules'.DS).'Models'.DS.$name.'.php');
        }
    }

    /**
     * Load helper files.
     *
     * Load helper files from current application's $module/helpers directory or
     * from other $project/$module/helpers by providing $project name.
     *
     * @access public
     * @static
     * @param  string|array $files
     * @param  string|null  $project (default: null)
     * @return void
     */
    public static function helper($files, $module, $project = null)
    {
        foreach ((array) $files as $key => $name) {
            $project1 = $project;
            if (is_numeric($key) === false) {
                $project1 = $name;
                $name = $key;
            }

            require((empty($project1) ? APP_MODULES_PATH : BASE_PATH.$project1.DS.'Modules'.DS).$module.DS.'Helpers'.DS.$name.'.php');
        }
    }

    /**
     * Render a view or multiple views.
     *
     * Render views from current application's view directory (APP_PATH/views).
     * Setting $return to true, instead of outputing, rendered view's html will be returned.
     *
     * @access public
     * @static
     * @param  strign|array $files
     * @param  array        &$data  (default: [])
     * @param  bool         $return (default: false)
     * @return void|string
     */
    public static function view($files, &$data = [], $return = false)
    {
        static $globals_added = false;

        // Check for global views variables, can be set, for example, by controller's constructor
        if (!empty(self::$config['view_data'])) {
            $data = (array) $data + (array) self::$config['view_data'];
        }

        if (empty(Load::$config['view_engine'])) {
            if (!empty($return)) {
                return false;
            }

            $config = self::$config;
            foreach ((array) $files as $key => $file) {
                require APP_MODULES_PATH.$file;
            }

            return true;
        }

        // Add default view data
        if (empty($globals_added)) {
            Load::$config['view_engine']->addGlobal('config', self::$config);
            Load::$config['view_engine']->addGlobal('base_url', Router::$base_url);
            Load::$config['view_engine']->addGlobal('namespace', Router::$namespace);
            Load::$config['view_engine']->addGlobal('class', Router::$class);
            Load::$config['view_engine']->addGlobal('method', Router::$method);
            Load::$config['view_engine']->addGlobal('segments', Router::$segments);
            $globals_added = true;
        }

        // Load view data
        $contents = '';
        foreach ((array) $files as $key => $file) {
            $contents .= Load::$config['view_engine']->render($file, (array) $data);
        }

        // Output or return view data
        if (empty($return)) {
            echo $contents;

            return true;
        }

        return $contents;
    }

    /*
    |--------------------------------------------------------------------------
    | Timer methods
    |--------------------------------------------------------------------------
    */

    /**
     * Start timer. Timers are started incrementally, i.e. if two timers are started, first second timer needs to be stopped, then first one.
     *
     * @access public
     * @static
     * @return void
     */
    public static function startTimer()
    {
        self::$started_timers[] = microtime(true);
    }

    /**
     * Stop timer by providing name of the timer.
     *
     * @access public
     * @static
     * @param  string $name
     * @return float  Returns time in microseconds it took timer to execute.
     */
    public static function stopTimer($name)
    {
        self::$finished_timers[$name] = round(microtime(true) - array_shift(self::$started_timers), 5);

        return self::$finished_timers[$name];
    }

    /**
     * Mark time with a name.
     *
     * @access public
     * @static
     * @param  string $name
     * @return float  Returns time in microseconds it took to execute from startup to the time the method was called.
     */
    public static function markTime($name)
    {
        global $microtime;
        self::$finished_timers['*'.$name] = round(microtime(true) - $microtime, 5);

        return self::$finished_timers['*'.$name];
    }

    /**
     * Generate debug output for all timers.
     *
     * @access protected
     * @static
     * @return void
     */
    protected static function executionTime()
    {
        global $microtime;

        self::info('Total execution time: '.round(microtime(true) - $microtime, 5)." seconds;");
        self::info('Memory used: '.round(memory_get_usage() / 1024 / 1024, 4)." MB;\n");

        if (!empty(self::$finished_timers)) {
            krsort(self::$finished_timers);
            foreach (self::$finished_timers as $key => $value) {
                self::info("[{$value}s] {$key}");
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Logger methods
    |--------------------------------------------------------------------------
    */

    /**
     * System is unusable.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function emergency($message, array $context = array())
    {
        self::log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function alert($message, array $context = array())
    {
        self::log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function critical($message, array $context = array())
    {
        self::log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function error($message, array $context = array())
    {
        self::log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function warning($message, array $context = array())
    {
        self::log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function notice($message, array $context = array())
    {
        self::log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function info($message, array $context = array())
    {
        self::log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function debug($message, array $context = array())
    {
        self::log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return void
     */
    public static function log($level, $message, array $context = array())
    {
        self::$logs[] = ['level' => $level, 'message' => $message, 'context' => $context];
    }

    /*
    |--------------------------------------------------------------------------
    | Debug Output
    |--------------------------------------------------------------------------
    */

    /**
     * Generate debug output.
     *
     * @see Load::emergency()
     * @see Load::alert()
     * @see Load::critical()
     * @see Load::error()
     * @see Load::warning()
     * @see Load::notice()
     * @see Load::info()
     * @access public
     * @static
     * @return string Returns formatted html string of debug information, including timers, but also custom messages logged using logger interface.
     */
    public static function debugOutput()
    {
        // Log execution time
        self::executionTime();

        // Generate debug output
        $output = '';
        foreach (self::$logs as $item) {
            $class = '';
            switch ($item['level']) {
                case LogLevel::EMERGENCY:
                case LogLevel::ALERT:
                case LogLevel::CRITICAL:
                    $class = 'danger';
                    break;

                case LogLevel::ERROR:
                case LogLevel::WARNING:
                    $class = 'warning';
                    break;

                case LogLevel::NOTICE:
                case LogLevel::INFO:
                case LogLevel::DEBUG:
                    $class = 'info';
                    break;
            }

            $output .= '<span class="text-'.$class.'">'.strtoupper($item['level']).': </span>';
            $output .= $item['message'];
            $output .= (!empty($item['context']) ? " [".implode(',', $item['context'])."]\n" : "\n");
        }

        // Return it
        return $output;
    }
}

// Autoload function
spl_autoload_register(
    function ($classname) {
        $classname = str_replace('\\', DS, $classname);
        $classname = ltrim($classname, DS);

        if (is_file(APP_MODULES_PATH.$classname.'.php')) {
            require APP_MODULES_PATH.$classname.'.php';
        }
        elseif (is_file(APP_PATH.$classname.'.php')) {
            require APP_PATH.$classname.'.php';
        }
        elseif (is_file(SYS_MODULES_PATH.$classname.'.php')) {
            require SYS_MODULES_PATH.$classname.'.php';
        }
        elseif (is_file(SYS_PATH.$classname.'.php')) {
            require SYS_PATH.$classname.'.php';
        }
        elseif (is_file(BASE_PATH.$classname.'.php')) {
            require BASE_PATH.$classname.'.php';
        }
    },
    true
);
