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
 * @category   extensions
 * @package    Auth
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

abstract class KumbiaAuthBase {

    /**
     * Namespace of session
     * @var string
     */
    static public $namespace = 'Addssfsweds';

    /**
     * Verifica si el usuario está logueado
     * @return bool
     */
    public function isLogin() {
        return (bool) Session::get('login', self::$namespace);
    }

    /**
     * Desloguea a un usuario
     */
    public function logout() {
        Session::set('login', FALSE, self::$namespace);
    }

    /**
     * It makes a login
     * @param  array $array params
     * @return bool        do it had success?
     */
    abstract public function login(Array $array);

    /**
     * Set the status of the login
     * @param boolean $status [description]
     */
    protected function setStatus($status){
        Session::set('login', $status, self::$namespace);
    }

    /**
     * Set the Auth data
     * @param Array $data object
     */
    protected function setData($data){
        Session::set('store', $data, self::$namespace);
    }

    /**
     * Get information of auth
     * @param  string $var name
     * @return mixed
     */
    public function get($var) {
        $store = Session::set('store', self::$namespace);
        return isset($store[$var])?$store[$var]:null;
    }
}
