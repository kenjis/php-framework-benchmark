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
 * @package    Upload
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Sube archivos al servidor.
 *
 * @category   Kumbia
 * @package    Upload
 */
abstract class Upload {

    /**
     * Nombre de archivo subido por método POST
     *
     * @var string
     */
    protected $_name;

    /**
     * Ruta donde se guardara el archivo
     *
     * @var string
     */
    protected $_path;

    /**
     * Permitir subir archivos de scripts ejecutables
     *
     * @var boolean
     */
    protected $_allowScripts = FALSE;

    /**
     * Tamaño mínimo del archivo
     *
     * @var string
     */
    protected $_minSize = '';

    /**
     * Tamaño máximo del archivo
     *
     * @var string
     */
    protected $_maxSize = '';

    /**
     * Tipos de archivo permitidos utilizando mime
     *
     * @var array
     */
    protected $_types = array();

    /**
     * Extensión de archivo permitida
     *
     * @var array
     */
    protected $_extensions = array();

    /**
     * Permitir sobrescribir ficheros
     *
     * @var bool Por defecto FALSE
     */
    protected $_overwrite = FALSE;

    /**
     * Constructor
     *
     * @param string $name nombre de archivo por método POST
     */
    public function __construct($name) {
        $this->_name = $name;
    }

    /**
     * Indica si se permitirá guardar archivos de scripts ejecutables
     *
     * @param boolean $value
     */
    public function setAllowScripts($value) {
        $this->_allowScripts = $value;
    }

    /**
     * Asigna el tamaño mínimo permitido para el archivo
     *
     * @param string $size
     */
    public function setMinSize($size) {
        $this->_minSize = trim($size);
    }

    /**
     * Asigna el tamaño máximo permitido para el archivo
     *
     * @param string $size
     */
    public function setMaxSize($size) {
        $this->_maxSize = trim($size);
    }

    /**
     * Asigna los tipos de archivos permitido (mime)
     *
     * @param array|string $value lista de tipos de archivos permitidos (mime) si es string separado por |
     */
    public function setTypes($value) {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }

        $this->_types = $value;
    }

    /**
     * Asigna las extensiones de archivos permitidas
     *
     * @param array|string $value lista de extensiones para archivos, si es string separado por |
     */
    public function setExtensions($value) {
        if (!is_array($value)) {
            $value = explode('|', $value);
        }

        $this->_extensions = $value;
    }

    /**
     * Permitir sobrescribir el fichero
     *
     * @param bool $value
     */
    public function overwrite($value) {
        $this->_overwrite = (bool) $value;
    }

    /**
     * Acciones antes de guardar
     *
     * @param string $name nombre con el que se va a guardar el archivo
     * @return  boolean|null
     */
    protected function _beforeSave($name) {
    }

    /**
     * Acciones después de guardar
     *
     * @param string $name nombre con el que se guardo el archivo
     * @return  boolean|null
     */
    protected function _afterSave($name) {
    }

    /**
     * Guarda el archivo subido
     *
     * @param string $name nombre con el que se guardara el archivo
     * @return boolean|string Nombre de archivo generado con la extensión o FALSE si falla
     */
    public function save($name = '') {
        if (!$this->isUploaded()) {
            return FALSE;
        }
        if (!$name) {
            $name = $_FILES[$this->_name]['name'];
        } else {
            $name = $name . $this->_getExtension();
        }

        // Guarda el archivo
        if ($this->_beforeSave($name) !== FALSE && $this->_overwrite($name) && $this->_validates() && $this->_saveFile($name)) {
            $this->_afterSave($name);
            return $name;
        }
        return FALSE;
    }

    /**
     * Guarda el archivo con un nombre aleatorio
     *
     * @return string|false Nombre de archivo generado o FALSE si falla
     */
    public function saveRandom() {

        // Genera el nombre de archivo
        $name = md5(time());

        // Guarda el archivo
        if ($this->save($name)) {
            return $name . $this->_getExtension();
        }

        return FALSE;
    }

    /**
     * Verifica si el archivo esta subido en el servidor y listo para guardarse
     *
     * @return boolean
     */
    public function isUploaded() {

        // Verifica si ha ocurrido un error al subir
        if ($_FILES[$this->_name]['error'] > 0) {
            $error = array(UPLOAD_ERR_INI_SIZE => 'el archivo excede el tamaño máximo (' . ini_get('upload_max_filesize') . 'b) permitido por el servidor', UPLOAD_ERR_FORM_SIZE => 'el archivo excede el tamaño máximo permitido', UPLOAD_ERR_PARTIAL => 'se ha subido el archivo parcialmente', UPLOAD_ERR_NO_FILE => 'no se ha subido ningún archivo', UPLOAD_ERR_NO_TMP_DIR => 'no se encuentra el directorio de archivos temporales', UPLOAD_ERR_CANT_WRITE => 'falló al escribir el archivo en disco', UPLOAD_ERR_EXTENSION => 'una extensión de php ha detenido la subida del archivo');

            Flash::error('Error: ' . $error[$_FILES[$this->_name]['error']]);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Valida el archivo antes de guardar
     *
     * @return boolean
     */
    protected function _validates() {
        $validations = array('allowScripts', 'types', 'extensions', 'maxSize', 'minSize');
        foreach ($validations as $value) {
            $func = "_{$value}";
            if ($this->$func && !$this->$func()) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Devuelve la extensión
     *
     * @return string
     */
    protected function _getExtension() {
        if ($ext = pathinfo($_FILES[$this->_name]['name'], PATHINFO_EXTENSION)) {
            return '.' . $ext;
        }
    }

    /**
     * Valida si puede sobrescribir el archivo
     *
     * @return boolean
     */
    protected function _overwrite($name) {
        if ($this->_overwrite) {
            return TRUE;
        }
        if (file_exists("$this->_path/$name")) {
            Flash::error('Error: ya existe este fichero. Y no se permite reescribirlo');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Convierte de tamaño legible por humanos a bytes
     *
     * @param string $size
     * @return int
     */
    protected function _toBytes($size) {
        if (is_int($size) || ctype_digit($size)) {
            return (int) $size;
        }

        $tipo = strtolower(substr($size, -1));
        $size = (int) $size;

        switch ($tipo) {
            case 'g':

                //Gigabytes
                $size *= 1073741824;
                break;

            case 'm':

                //Megabytes
                $size *= 1048576;
                break;

            case 'k':

                //Kilobytes
                $size *= 1024;
                break;

            default:
                $size = -1;
                Flash::error('Error: el tamaño debe ser un int para bytes, o un string terminado con K, M o G. Ej: 30k , 2M, 2G');
        }

        return $size;
    }

    /**
     * Guardar el archivo en el servidor
     *
     * @param string $name nombre con el que se guardará el archivo
     * @return boolean
     */
    protected abstract function _saveFile($name);

    /**
     * Obtiene el adaptador para Upload
     *
     * @param string $name nombre de archivo recibido por POST
     * @param string $adapter (file, image, model)
     * @return Upload
     */
    public static function factory($name, $adapter = 'file') {
        require_once __DIR__ . "/adapters/{$adapter}_upload.php";
        $class = $adapter . 'upload';

        return new $class($name);
    }

    /**
     * @param boolean $cond
     */
    protected function _cond($cond, $message) {
        if ($cond) {
            Flash::error("Error: $message");
            return FALSE;
        }
        return TRUE;
    }

    protected function _allowScripts() {
        return $this->_cond(
            !$this->_allowScripts && preg_match('/\.(php|phtml|php3|php4|js|shtml|pl|py|rb|rhtml)$/i', $_FILES[$this->_name]['name']),
            'no esta permitido subir scripts ejecutables'
        );
    }

    /**
     * Valida que el tipo de archivo
     *
     * @return boolean
     */
    protected function _types() {
        return $this->_cond(
            !in_array($_FILES[$this->_name]['type'], $this->_types),
            'el tipo de archivo no es válido'
        );
    }

    protected function _extensions() {
        return $this->_cond(
            !preg_match('/\.(' . implode('|', $this->_extensions) . ')$/i', $_FILES[$this->_name]['name']),
            'la extensión del archivo no es válida'
        );
    }

    protected function _maxSize() {
        return $this->_cond(
            $_FILES[$this->_name]['size'] > $this->_toBytes($this->_maxSize),
            "no se admiten archivos superiores a $this->_maxSize b"
        );
    }

    protected function _minSize() {
        return $this->_cond(
            $_FILES[$this->_name]['size'] < $this->_toBytes($this->_minSize),
            "Error: no se admiten archivos inferiores a $this->_minSize b"
        );
    }
}
