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
 * @package    Acl
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see AclRole
 */
include __DIR__ .'/role/role.php';

/**
 * @see AclResource
 */
include __DIR__ .'/resource/resource.php';

/**
 * Listas ACL (Access Control List)
 *
 * La Lista de Control de Acceso o ACLs (del ingles, Access Control List)
 * es un concepto de seguridad informatica usado para fomentar la separacion
 * de privilegios. Es una forma de determinar los permisos de acceso apropiados
 * a un determinado objeto, dependiendo de ciertos aspectos del proceso
 * que hace el pedido.
 *
 * Cada lista ACL contiene una lista de Roles, unos resources y unas acciones de
 * acceso;
 *
 * $roles = Lista de Objetos Acl_Role de Roles de la Lista
 * $resources = Lista de Objetos Acl_Resource que van a ser controlados
 * $access = Contiene la lista de acceso
 * $role_inherits = Contiene la lista de roles que son heradados por otros
 * $resource_names = Nombres de Resources
 * $roles_names = Nombres de Resources
 *
 * @category   Kumbia
 * @package    Acl
 * @deprecated 1.0 use ACL2
 */
class Acl {

    /**
     * Nombres de Roles en la lista ACL
     *
     * @var array
     */
    private $roles_names = array();
    /**
     * Objetos Roles en lista ACL
     *
     * @var array
     */
    private $roles = array();
    /**
     * Objetos Resources en la lista ACL
     *
     * @var array
     */
    private $resources = array();
    /**
     * Permisos de la Lista de Acceso
     *
     * @var array
     */
    public $access = array();
    /**
     * Herencia entre Roles
     *
     * @var array
     */
    private $role_inherits = array();
    /**
     * Array de Nombres de Recursos
     *
     * @var array
     */
    private $resources_names = array('*');
    /**
     * Lista ACL de permisos
     *
     * @var array
     */
    private $access_list = array('*' => array('*'));

    /**
     * Agrega un Rol a la Lista ACL
     *
     * $roleObject = Objeto de la clase AclRole para agregar a la lista
     * $access_inherits = Nombre del Role del cual hereda permisos ó array del grupo
     * de perfiles del cual hereda permisos
     *
     * Ej:
     * <code>$acl->add_role(new Acl_Role('administrador'), 'consultor');</code>
     *
     * @param AclRole $roleObject
     * @return false|null
     */
    public function add_role(AclRole $roleObject, $access_inherits = '') {
        if (in_array($roleObject->name, $this->roles_names)) {
            return false;
        }
        $this->roles[]                             = $roleObject;
        $this->roles_names[]                       = $roleObject->name;
        $this->access[$roleObject->name]['*']['*'] = 'A';
        if ($access_inherits) {
            $this->add_inherit($roleObject->name, $access_inherits);
        }
    }

    /**
     * Hace que un rol herede los accesos de otro rol
     *
     * @param string $role
     * @param string $role_to_inherit
     */
    public function add_inherit($role, $role_to_inherit) {
        if (!in_array($role, $this->roles_names)) {
            return false;
        }
        if ($role_to_inherit != '') {
            if (is_array($role_to_inherit)) {
                foreach ($role_to_inherit as $rol_in) {
                    if ($rol_in == $role) {
                        return false;
                    }
                    if (!in_array($rol_in, $this->roles_names)) {
                        throw new KumbiaException("El Rol '{$rol_in}' no existe en la lista");

                    }
                    $this->role_inherits[$role][] = $rol_in;
                }
                $this->rebuild_access_list();
            } else {
                if ($role_to_inherit == $role) {
                    return false;
                }
                if (!in_array($role_to_inherit, $this->roles_names)) {
                    throw new KumbiaException("El Rol '{$role_to_inherit}' no existe en la lista");

                }
                $this->role_inherits[$role][] = $role_to_inherit;
                $this->rebuild_access_list();
            }
        } else {
            throw new KumbiaException("Debe especificar un rol a heredar en Acl::add_inherit");

        }
    }

    /**
     *
     * Verifica si un rol existe en la lista o no
     *
     * @param string $role_name
     * @return boolean
     */
    public function is_role($role_name) {
        return in_array($role_name, $this->roles_names);
    }

    /**
     *
     * Verifica si un resource existe en la lista o no
     *
     * @param string $resource_name
     * @return boolean
     */
    public function is_resource($resource_name) {
        return in_array($resource_name, $this->resources_names);
    }

    /**
     * Agrega un resource a la Lista ACL
     *
     * Resource_name puede ser el nombre de un objeto concreto, por ejemplo
     * consulta, buscar, insertar, valida etc o una lista de ellos
     *
     * Ej:
     * <code>
     * //Agregar un resource a la lista:
     * $acl->add_resource(new AclResource('clientes'), 'consulta');
     *
     * //Agregar Varios resources a la lista:
     * $acl->add_resource(new AclResource('clientes'), 'consulta', 'buscar', 'insertar');
     * </code>
     *
     * @param AclResource $resource
     * @return boolean|null
     */
    public function add_resource(AclResource $resource) {
        if (!in_array($resource->name, $this->resources)) {
            $this->resources[]                  = $resource;
            $this->access_list[$resource->name] = array();
            $this->resources_names[]            = $resource->name;
        }
        if (func_num_args() > 1) {
            $access_list = func_get_args();
            unset($access_list[0]);
            $this->add_resource_access($resource->name, $access_list);
        }
    }

    /**
     * Agrega accesos a un Resource
     *
     * @param string $resource
     * @param $access_list
     */
    public function add_resource_access($resource, $access_list) {
        if (is_array($access_list)) {
            foreach ($access_list as $access_name) {
                if (!in_array($access_name, $this->access_list[$resource])) {
                    $this->access_list[$resource][] = $access_name;
                }
            }
        } else {
            if (!in_array($access_list, $this->access_list[$resource])) {
                $this->access_list[$resource][] = $access_list;
            }
        }
    }

    /**
     * Elimina un acceso del resorce
     *
     * @param string $resource
     * @param mixed $access_list
     */
    public function drop_resource_access($resource, $access_list) {
        if (is_array($access_list)) {
            foreach ($access_list as $access_name) {
                if (in_array($access_name, $this->access_list[$resource])) {
                    foreach ($this->access_list[$resource] as $i => $access) {
                        if ($access == $access_name) {
                            unset($this->access_list[$resource][$i]);
                        }
                    }
                }
            }
        } else {
            if (in_array($access_list, $this->access_list[$resource])) {
                foreach ($this->access_list[$resource] as $i => $access) {
                    if ($access == $access_list) {
                        unset($this->access_list[$resource][$i]);
                    }
                }
            }
        }
        $this->rebuild_access_list();
    }

    /**
     * Agrega un acceso de la lista de resources a un rol
     *
     * Utilizar '*' como comodín
     *
     * Ej:
     * <code>
     * //Acceso para invitados a consultar en clientes
     * $acl->allow('invitados', 'clientes', 'consulta');
     *
     * //Acceso para invitados a consultar e insertar en clientes
     * $acl->allow('invitados', 'clientes', array('consulta', 'insertar'));
     *
     * //Acceso para cualquiera a visualizar en productos
     * $acl->allow('*', 'productos', 'visualiza');
     *
     * //Acceso para cualquiera a visualizar en cualquier resource
     * $acl->allow('*', '*', 'visualiza');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param mixed $access
     */
    public function allow($role, $resource, $access) {
        if (!in_array($role, $this->roles_names)) {
            throw new KumbiaException("No existe el rol '$role' en la lista");

        }
        if (!in_array($resource, $this->resources_names)) {
            throw new KumbiaException("No existe el resource '$resource' en la lista");

        }
        if (is_array($access)) {
            foreach ($access as $acc) {
                if (!in_array($acc, $this->access_list[$resource])) {
                    throw new KumbiaException("No existe el acceso '$acc' en el resource '$resource' de la lista");

                }
            }
            foreach ($access as $acc) {
                $this->access[$role][$resource][$acc] = 'A';
            }
        } else {
            if (!in_array($access, $this->access_list[$resource])) {
                throw new KumbiaException("No existe el acceso '$access' en el resource '$resource' de la lista");

            }
            $this->access[$role][$resource][$access] = 'A';
            $this->rebuild_access_list();
        }
    }

    /**
     * Denegar un acceso de la lista de resources a un rol
     *
     * Utilizar '*' como comodín
     *
     * Ej:
     * <code>
     * //Denega acceso para invitados a consultar en clientes
     * $acl->deny('invitados', 'clientes', 'consulta');
     *
     * //Denega acceso para invitados a consultar e insertar en clientes
     * $acl->deny('invitados', 'clientes', array('consulta', 'insertar'));
     *
     * //Denega acceso para cualquiera a visualizar en productos
     * $acl->deny('*', 'productos', 'visualiza');
     *
     * //Denega acceso para cualquiera a visualizar en cualquier resource
     * $acl->deny('*', '*', 'visualiza');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param mixed $access
     */
    public function deny($role, $resource, $access) {
        if (!in_array($role, $this->roles_names)) {
            throw new KumbiaException("No existe el rol '$role' en la lista");

        }
        if (!in_array($resource, $this->resources_names)) {
            throw new KumbiaException("No existe el resource '$resource' en la lista");

        }
        if (is_array($access)) {
            foreach ($access as $acc) {
                if (!in_array($acc, $this->access_list[$resource])) {
                    throw new KumbiaException("No existe el acceso '$acc' en el resource '$resource' de la lista");

                }
            }
            foreach ($access as $acc) {
                $this->access[$role][$resource][$acc] = 'D';
            }
        } else {
            if (!in_array($access, $this->access_list[$resource])) {
                throw new KumbiaException("No existe el acceso '$access' en el resource '$resource' de la lista");

            }
            $this->access[$role][$resource][$access] = 'D';
            $this->rebuild_access_list();
        }
    }

    /**
     * Devuelve true si un $role, tiene acceso en un resource
     *
     * <code>
     * //Andres tiene acceso a insertar en el resource productos
     * $acl->is_allowed('andres', 'productos', 'insertar');
     *
     * //Invitado tiene acceso a editar en cualquier resource?
     * $acl->is_allowed('invitado', '*', 'editar');
     *
     * //Invitado tiene acceso a editar en cualquier resource?
     * $acl->is_allowed('invitado', '*', 'editar');
     * </code>
     *
     * @param string $role
     * @param string $resource
     * @param mixed $access_list
     * @return boolean|null
     */
    public function is_allowed($role, $resource, $access_list) {
        if (!in_array($role, $this->roles_names)) {
            throw new KumbiaException("El rol '$role' no existe en la lista en acl::is_allowed");

        }
        if (!in_array($resource, $this->resources_names)) {
            throw new KumbiaException("El resource '$resource' no existe en la lista en acl::is_allowed");

        }
        if (is_array($access_list)) {
            foreach ($access_list as $access) {
                if (!in_array($access, $this->access_list[$resource])) {
                    throw new KumbiaException("No existe en acceso '$access' en el resource '$resource' en acl::is_allowed");

                }
            }
        } else {
            if (!in_array($access_list, $this->access_list[$resource])) {
                throw new KumbiaException("No existe en acceso '$access_list' en el resource '$resource' en acl::is_allowed");

            }
        }

        /* foreach($this->access[$role] as ){

        } */
        // FIXME: Por lo pronto hacemos esta validación, luego se mejorará
        if (!isset($this->access[$role][$resource][$access_list])) {
            return false;
        }

        if ($this->access[$role][$resource][$access_list] == "A") {
            return true;
        }
    }

    /**
     * Reconstruye la lista de accesos a partir de las herencias
     * y accesos permitidos y denegados
     *
     * @access private
     */
    private function rebuild_access_list() {
        for ($i = 0; $i <= ceil(count($this->roles)*count($this->roles)/2); $i++) {
            foreach ($this->roles_names as $role) {
                if (isset($this->role_inherits[$role])) {
                    foreach ($this->role_inherits[$role] as $role_inherit) {
                        if (isset($this->access[$role_inherit])) {
                            foreach ($this->access[$role_inherit] as $resource_name => $access) {
                                foreach ($access as $access_name                       => $value) {
                                    if (!in_array($access_name, $this->access_list[$resource_name])) {
                                        unset($this->access[$role_inherit][$resource_name][$access_name]);
                                    } else {
                                        if (!isset($this->access[$role][$resource_name][$access_name])) {
                                            $this->access[$role][$resource_name][$access_name] = $value;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
