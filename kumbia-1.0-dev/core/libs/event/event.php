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
 * @package    Event
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see Hook
 */
require CORE_PATH.'libs/event/hook.php';

/**
 * Manejador de eventos
 *
 * @category   Kumbia
 * @package    Event
 * @deprecated 1.0 Use Ashrey Events
 */
class Event {

    /**
     * Datos compartidos
     *
     * @var mixed
     */
    public static $data = null;
    /**
     * Eventos
     *
     * @var array
     */
    protected static $_events = array();

    /**
     * Verifica si un evento ya tiene manejador
     *
     * @param string $event
     * @return boolean
     */
    public static function hasHandler($event) {
        return isset(self::$_events[$event]) && count(self::$_events[$event]);
    }

    /**
     * Enlaza un handler con un evento
     *
     * @param string $event evento
     * @param mixed $handler retrollamada
     */
    public static function bind($event, $handler) {
        self::setEvent($event);
        self::$_events[$event][] = $handler;
    }

    /**
     * Enlaza en el evento el handler2 antes del handler1
     *
     * @param string $event evento
     * @param mixed $handler1
     * @param mixed $handler2
     */
    public static function before($event, $handler1, $handler2) {
        self::setEvent($event);
        self::addHandler($event, $handler1, $handler2);
    }

    /**
     * Enlaza en el evento el handler2 despues del handler1
     *
     * @param string $event evento
     * @param mixed $handler1
     * @param mixed $handler2
     */
    public static function after($event, $handler1, $handler2) {
        self::setEvent($event);
        self::addHandler($event, $handler1, $handler2, true);
    }

    /**
     * Desenlaza los manejadores
     *
     * @param string $event evento
     * @param mixed $handler manejador
     */
    public static function unbind($event, $handler = false) {
        if ($handler && isset(self::$_events[$event])) {
            $i = array_search($handler, self::$_events[$event]);
            if ($i !== false) {
                unset(self::$_events[$event][$i]);
            }
        } else {
            self::$_events[$event] = array();
        }
    }

    /**
     * Remplaza un handler por otro
     *
     * @param string $event evento
     * @param mixed $handler1 handler a remplazar
     * @param mixed $handler2 nuevo handler
     */
    public static function replace($event, $handler1, $handler2) {
        if (isset(self::$_events[$event])) {
            $i = array_search($handler1, self::$_events[$event]);
            if ($i !== false) {
                self::$_events[$event][$i] = $handler2;
                return true;
            }
        }
        return false;
    }

    /**
     * Ejecuta los handlers asociados al evento
     *
     * @param string $event evento
     * @param array $args argumentos
     * @return mixed
     */
    public static function trigger($event, $args = array()) {
        $value = false;
        if (isset(self::$_events[$event])) {
            foreach (self::$_events[$event] as $handler) {
                $value = call_user_func_array($handler, $args);
            }
        }

        self::$data = null;
        return $value;
    }
    
    /**
     * Crea el array de eventos si no existe
     *
     * @param string $event evento
     */
    private static function setEvent($event) {
        if (!isset(self::$_events[$event])) {
            self::$_events[$event] = array();
        }
    }
    
    /**
     * Añade un handler 
     *
     * @param string $event     evento
     * @param mixed  $handler1
     * @param mixed  $handler2
     * @param bool   $after    Añadir antes o despues (por defecto antes)
     */
    private static function addHandler($event, $handler1, $handler2, $after = false) {
        $i = array_search($handler1, self::$_events[$event]);
        if ($i === false) {
            self::$_events[$event][] = $handler2;
            return;
        }
        if ($after) ++$i;

        array_splice(self::$_events[$event], $i, 0, $handler2); 
    }
}
