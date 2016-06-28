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
 * Implementacion de ACL con definicion de reglas en PHP
 *
 * @category   Kumbia
 * @package    Acl
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Implementacion de ACL con definicion de reglas en PHP
 *
 * @category   Kumbia
 * @package    Acl
 */
class SimpleAcl extends Acl2
{

    /**
     * Definicion de Roles con sus respectivos padres y recursos a los que pueden acceder
     *
     * @var array
     *
     * @example SimpleAcl-roles
     *   protected $_roles = array(
     *       'rol1' => array(
     *           'resources' => array('recurso1', 'recurso2')
     *       ),
     *       'rol2' => array(
     *           'resources' => array('recurso2'),
     *           'parents' => array('rol1')
     *       )
     *   );
     */
    protected $_roles = array();
    /**
     * Usuarios del sistema con sus respectivos roles
     *
     * @var array
     *
     * @example SimpleAcl-users
     * protected $_users = array(
     *     'usuario1' => array('rol1', 'rol2'),
     *     'usuario2' => array('rol3')
     * );
     */
    protected $_users = array();

    /**
     * Establece los recursos a los que el rol puede acceder
     *
     * @param string $role nombre de rol
     * @param array $resources recursos a los que puede acceder el rol
     */
    public function allow($role, $resources)
    {
        $this->_roles[$role]['resources'] = $resources;
    }

    /**
     * Establece los padres del rol
     *
     * @param string $role nombre de rol
     * @param array $parents padres del rol
     */
    public function parents($role, $parents)
    {
        $this->_roles[$role]['parents'] = $parents;
    }

    /**
     * Adiciona un usuario a la lista con sus respectivos roles
     *
     * @param string $user
     * @param array $roles
     */
    public function user($user, $roles)
    {
        $this->_users[$user] = $roles;
    }

    /**
     * Obtiene los roles del usuario al que se le valida si puede acceder al recurso
     *
     * @param string $user usuario al que se le valida acceso
     * @return array roles de usuario
     */
    protected function _getUserRoles($user)
    {
        if (isset($this->_users[$user])) {
            return $this->_users[$user];
        }

        return array();
    }

    /**
     * Obtiene los recursos al cual el rol puede acceder
     *
     * @param string $role nombre de rol
     * @return array recursos al cual el rol puede acceder
     */
    protected function _getRoleResources($role)
    {
        if (isset($this->_roles[$role]['resources'])) {
            return $this->_roles[$role]['resources'];
        }

        return array();
    }

    /**
     * Obtiene los padres del rol
     *
     * @param string $role nombre de rol
     * @return array padres del rol
     */
    protected function _getRoleParents($role)
    {
        if (isset($this->_roles[$role]['parents'])) {
            return $this->_roles[$role]['parents'];
        }

        return array();
    }

}
