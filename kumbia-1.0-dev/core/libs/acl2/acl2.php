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
 * Clase Base para gestión de ACL
 *
 * @category   Kumbia
 * @package    Acl
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase Base para gestión de ACL
 *
 * Nueva Clase Base para gestión de ACL (Access Control List) permisos
 *
 * @category   Kumbia
 * @package    Acl
 */
abstract class Acl2
{

    /**
     * Adaptador por defecto
     *
     * @var string
     */
    protected static $_defaultAdapter = 'simple';

    /**
     * Verifica si el usuario puede acceder al recurso
     *
     * @param string $resource recurso al cual se verificará acceso
     * @param string $user usuario de la acl
     * @return boolean
     */
    public function check($resource, $user)
    {
        // Itera en los roles de usuario
        foreach ($this->_getUserRoles($user) as $role) {
            if ($this->_checkRole($role, $resource)) {
                return TRUE;
            }
        }

        // Por defecto se niega el acceso
        return FALSE;
    }

    /**
     * Verifica si un rol puede acceder al recurso
     *
     * @param string $role
     * @param string $resource
     * @return boolean
     */
    private function _checkRole($role, $resource)
    {
        // Verificar si el rol puede acceder al recurso
        if (in_array($resource, $this->_getRoleResources($role))) {
            return TRUE;
        }

        // Verifica si ha heredado el acceso, verificando los recursos de los padres
        foreach ($this->_getRoleParents($role) as $parent) {
            if ($this->_checkRole($parent, $resource)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Obtiene los roles del usuario al que se le valida si puede acceder al recurso
     *
     * @param string $user usuario al que se le valida acceso
     * @return array roles de usuario
     */
    abstract protected function _getUserRoles($user);

    /**
     * Obtiene los recursos al cual el rol puede acceder
     *
     * @param string $role nombre de rol
     * @return array recursos al cual el rol puede acceder
     */
    abstract protected function _getRoleResources($role);

    /**
     * Obtiene los padres del rol
     *
     * @param string $role nombre de rol
     * @return array padres del rol
     */
    abstract protected function _getRoleParents($role);

    /**
     * Obtiene el adaptador para ACL
     *
     * @param string $adapter (simple, model, xml, ini)
     */
    public static function factory($adapter = '')
    {
        if (!$adapter) {
            $adapter = self::$_defaultAdapter;
        }

        require_once __DIR__ . "/adapters/{$adapter}_acl.php";
        $class = $adapter . 'acl';

        return new $class;
    }

    /**
     * Cambia el adaptador por defecto
     *
     * @param string $adapter nombre del adaptador por defecto
     */
    public static function setDefault($adapter)
    {
        self::$_defaultAdapter = $adapter;
    }

}
