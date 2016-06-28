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
 * Clase para guardar archivo subido
 *
 * @category   Kumbia
 * @package    Upload
 */
class FileUpload extends Upload
{
    /**
     * Constructor
     *
     * @param string $name nombre de archivo por metodo POST
     */
    public function __construct($name)
    {
        parent::__construct($name);

        // Ruta donde se guardara el archivo
        $this->_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/files/upload';
    }

    /**
     * Asigna la ruta al directorio de destino para el archivo
     *
     * @param string $path ruta al directorio de destino (Ej: /home/usuario/data)
     */
    public function setPath($path)
    {
        $this->_path = $path;
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

        return parent::_validates();
    }

}
