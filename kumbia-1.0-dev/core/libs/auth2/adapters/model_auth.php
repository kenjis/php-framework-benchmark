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
 * Clase de Autenticacón por BD
 *
 * @category   Kumbia
 * @package    Auth
 * @subpackage Adapters
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase de Autenticacón por BD
 *
 * @category   Kumbia
 * @package    Auth
 * @subpackage Adapters
 */
class ModelAuth extends Auth2
{

    /**
     * Modelo a utilizar para el proceso de autenticacion
     *
     * @var String
     */
    protected $_model = 'users';
    /**
     * Namespace de sesion donde se cargaran los campos del modelo
     *
     * @var string
     */
    protected $_sessionNamespace = 'default';
    /**
     * Campos que se cargan del modelo
     *
     * @var array
     */
    protected $_fields = array('id');
     /**
     *
     *
     * @var string
     */
    protected $_algos ;
     /**
     *
     *
     * @var string
     */
    protected $_key;
    /**
     * Asigna el modelo a utilizar
     *
     * @param string $model nombre de modelo
     */
    public function setModel($model)
    {
        $this->_model = $model;
    }

    /**
     * Asigna el namespace de sesion donde se cargaran los campos de modelo
     *
     * @param string $namespace namespace de sesion
     */
    public function setSessionNamespace($namespace)
    {
        $this->_sessionNamespace = $namespace;
    }

    /**
     * Indica que campos del modelo se cargaran en sesion
     *
     * @param array $fields campos a cargar
     */
    public function setFields($fields)
    {
        $this->_fields = $fields;
    }

    /**
     * Check
     *
     * @param $username
     * @param $password
     * @return bool
     */
    protected function _check($username, $password)
    {
        // TODO $_SERVER['HTTP_HOST'] puede ser una variable por si quieren ofrecer autenticacion desde cualquier host indicado
        if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === FALSE) {
            self::log('INTENTO HACK IP ' . $_SERVER['HTTP_REFERER']);
            $this->setError('Intento de Hack!');
            return FALSE;
        }

        // TODO: revisar seguridad
        $password = hash($this->_algos, $password);
        //$username = addslashes($username);
        $username = filter_var($username, FILTER_SANITIZE_MAGIC_QUOTES);

        $Model = new $this->_model;
        if ($user = $Model->find_first("$this->_login = '$username' AND $this->_pass = '$password'")) {
            // Carga los atributos indicados en sesion
            foreach ($this->_fields as $field) {
                Session::set($field, $user->$field, $this->_sessionNamespace);
            }

            Session::set($this->_key, TRUE);
            return TRUE;
        }

        $this->setError('Error Login!');
        Session::set($this->_key, FALSE);
        return FALSE;
    }

}
