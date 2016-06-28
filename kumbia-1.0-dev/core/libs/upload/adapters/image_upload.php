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
 * Clase para guardar imagen subida
 *
 * @category   Kumbia
 * @package    Upload
 */
class ImageUpload extends Upload
{
    /**
     * Información de la imagen
     *
     * @var array|boolean
     */
    protected $_imgInfo;
    /**
     * Ancho mínimo de la imagen
     *
     * @var int
     */
    protected $_minWidth = NULL;
    /**
     * Ancho máximo de la imagen
     *
     * @var int
     */
    protected $_maxWidth = NULL;
    /**
     * Alto mínimo de la imagen
     *
     * @var int
     */
    protected $_minHeight = NULL;
    /**
     * Alto máximo de la imagen
     *
     * @var int
     */
    protected $_maxHeight = NULL;

    /**
     * Constructor
     *
     * @param string $name nombre de archivo por metodo POST
     */
    public function __construct($name)
    {
        parent::__construct($name);

        $this->_imgInfo = getimagesize($_FILES[$name]['tmp_name']);

        // Ruta donde se guardara el archivo
        $this->_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/img/upload';
    }

    /**
     * Asigna la ruta al directorio de destino para la imagen
     *
     * @param string $path ruta al directorio de destino (Ej: /home/usuario/data)
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * Asigna el ancho mínimo de la imagen
     *
     * @param int $value
     */
    public function setMinWidth($value)
    {
        $this->_minWidth = $value;
    }

    /**
     * Asigna el ancho máximo de la imagen
     *
     * @param int $value
     */
    public function setMaxWidth($value)
    {
        $this->_maxWidth = $value;
    }

    /**
     * Asigna el alto mínimo de la imagen
     *
     * @param int $value
     */
    public function setMinHeight($value)
    {
        $this->_minHeight = $value;
    }

    /**
     * Asigna el alto máximo de la imagen
     *
     * @param int $value
     */
    public function setMaxHeight($value)
    {
        $this->_maxHeight = $value;
    }

    /**
     * Valida el archivo antes de guardar
     *
     * @return boolean
     */
    protected function _validates()
    {
        // Verifica que se pueda escribir en el directorio
        if (!is_writable($this->_path)) {
            Flash::error('Error: no se puede escribir en el directorio');
            return FALSE;
        }


        $image = $this->_imgInfo;
        // Verifica que sea un archivo de imagen
        if (!$image){
            Flash::error('Error: el archivo debe ser una imagen');
            return FALSE;
        }

        // Verifica ancho minimo de la imagen
        if ($this->_minWidth !== NULL) {
            if ($image[0] < $this->_minWidth) {
                Flash::error("Error: el ancho de la imagen debe ser superior o igual a {$this->_minWidth}px");
                return FALSE;
            }
        }

        // Verifica ancho maximo de la imagen
        if ($this->_maxWidth !== NULL) {
            if ($image[0] > $this->_maxWidth) {
                Flash::error("Error: el ancho de la imagen debe ser inferior o igual a {$this->_maxWidth}px");
                return FALSE;
            }
        }

        // Verifica alto minimo de la imagen
        if ($this->_minHeight !== NULL) {
            if ($image[1] < $this->_minHeight) {
                Flash::error("Error: el alto de la imagen debe ser superior o igual a {$this->_minHeight}px");
                return FALSE;
            }
        }

        // Verifica alto maximo de la imagen
        if ($this->_maxHeight !== NULL) {
            if ($image[1] > $this->_maxHeight) {
                Flash::error("Error: el alto de la imagen debe ser inferior o igual a {$this->_maxHeight}px");
                return FALSE;
            }
        }

        // Validaciones
        return parent::_validates();
    }

    /**
     * Valida que el tipo de archivo
     *
     * @return boolean
     */
    protected function _validatesTypes()
    {
        // Verifica que sea un archivo de imagen
        if (!$this->_imgInfo) return FALSE;

        foreach ($this->_types as $type) {
            if ($this->_imgInfo['mime'] == "image/$type") return TRUE;
        }

        return FALSE;
    }

    /**
     * Guardar el archivo en el servidor
     *
     * @param string $name nombre con el que se guardará el archivo
     * @return boolean
     */
    protected function _saveFile($name)
    {
        return move_uploaded_file($_FILES[$this->_name]['tmp_name'], "$this->_path/$name");
    }

}
