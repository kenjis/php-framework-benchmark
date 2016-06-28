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
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Esta clase permite autenticar usuarios usando
 * Radius Authentication (RFC 2865) y Radius Accounting (RFC 2866).
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @link http://web.mit.edu/kerberos/www/krb5-1.2/krb5-1.2.8/doc/admin_toc.html.
 */
class RadiusAuth implements AuthInterface
{

    /**
     * Nombre del archivo (si es utilizado)
     *
     * @var string
     */
    private $filename;
    /**
     * Servidor de autenticaci�n (si es utilizado)
     *
     * @var string
     */
    private $server;
    /**
     * Nombre de usuario para conectar al servidor de autenticacion (si es utilizado)
     *
     * @var string
     */
    private $username;
    /**
     * Password de usuario para conectar al servidor de autenticacion (si es utilizado)
     *
     * @var string
     */
    private $password;
    /**
     * Resource Radius
     */
    private $resource;
    /**
     * Puerto de Radius
     */
    private $port = 1812;
    /**
     * Secreto Radius
     *
     * @var string
     */
    private $secret;
    /**
     * Timeout para conectarse al servidor
     *
     * @var integer
     */
    private $timeout = 3;
    /**
     * Numero maximo de intentos
     *
     * @var integer
     */
    private $max_retries = 3;

    /**
     * Constructor del adaptador
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {

        if (!extension_loaded("radius")) {
            throw new KumbiaException("Debe cargar la extensión de php llamada radius");
        }

        foreach (array('server', 'secret') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("Debe especificar el parámetro '$param'");
            }
        }

        foreach (array('username', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

    /**
     * Obtiene los datos de identidad obtenidos al autenticar
     *
     */
    public function get_identity()
    {
        if (!$this->resource) {
            new KumbiaException("La conexión al servidor Radius es inválida");
        }
        $identity = array("username" => $this->username, "realm" => $this->username);
        return $identity;
    }

    /**
     * Autentica un usuario usando el adaptador
     *
     * @return boolean
     */
    public function authenticate()
    {

        $radius = radius_auth_open();
        if (!$radius) {
            throw new KumbiaException("No se pudo crear el autenticador de Radius");
        }

        if (!radius_add_server($radius, $this->server, $this->port, $this->secret,
                        $this->timeout, $this->max_retries)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_create_request($radius, RADIUS_ACCESS_REQUEST)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_put_string($radius, RADIUS_USER_NAME, $this->username)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_put_string($radius, RADIUS_USER_PASSWORD, $this->password)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        if (!radius_put_int($radius, RADIUS_AUTHENTICATE_ONLY, 1)) {
            throw new KumbiaException(radius_strerror($radius));
        }

        $this->resource = $radius;

        if (radius_send_request($radius) == RADIUS_ACCESS_ACCEPT) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Limpia el objeto cerrando la conexion si esta existe
     *
     */
    public function __destruct()
    {
        if ($this->resource) {
            radius_close($this->resource);
        }
    }

    /**
     * Asigna los valores de los parametros al objeto autenticador
     *
     * @param array $extra_args
     */
    public function set_params($extra_args)
    {
        foreach (array('server', 'secret', 'username', 'principal',
    'password', 'port', 'max_retries') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

}
