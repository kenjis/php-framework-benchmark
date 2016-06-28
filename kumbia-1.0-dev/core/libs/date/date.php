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
 * @package    Date
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase para el manejo de fechas
 *
 * @category   Kumbia
 * @package    Date
 * @deprecated 0.9 use native class
 */
class Date {

    /**
     * Valor interno de fecha
     *
     * @var string
     */
    private $date;
    /**
     * Valor interno del Dia
     *
     * @var int|string
     */
    private $day;
    /**
     * Valor interno del Año
     *
     * @var int|string
     */
    private $year;
    /**
     * Valor interno del Mes
     *
     * @var int|string
     */
    private $month;
    /**
     * Valor interno del Mes
     *
     * @var int
     */
    private $timestamp;

    /**
     * Crea un objeto de fecha Date
     *
     */
    public function __construct($date = "") {
        if ($date) {
            $date_parts      = explode("-", $date);
            $this->year      = (int) $date_parts[0];
            $this->month     = (int) $date_parts[1];
            $this->day       = (int) $date_parts[2];
            $this->date      = $date;
            $this->timestamp = mktime(0, 0, 0, $this->month, $this->day, $this->year);
        } else {
            $this->year      = date("Y");
            $this->month     = date("m");
            $this->day       = date("d");
            $this->timestamp = time();
            $this->date      = $this->year."-".sprintf("%02s", $this->month)."-".sprintf("%02s", $this->day);
        }
    }

    /**
     * Devuelve el nombre del mes de la fecha interna
     *
     * @return string
     */
    public function getMonthName() {
        return ucfirst(strftime("%B", $this->timestamp));
    }

    /**
     * Devuelve el nombre abreviado del mes de la fecha interna
     *
     * @return string
     */
    public function getAbrevMonthName() {
        return ucfirst(strftime("%b", $this->timestamp));
    }

    /**
     * Devuelve el dia interno de la fecha
     *
     * @return string
     */
    public function getDay() {
        return $this->day;
    }

    /**
     * Devuelve el mes interno de la fecha
     *
     * @return string
     */
    public function getMonth() {
        return $this->month;
    }

    /**
     * Devuelve el a#o interno de la fecha
     *
     * @return string
     */
    public function getYear() {
        return $this->year;
    }

    /**
     * Devuelve el timestamp de la fecha interna
     *
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /**
     * Devuelve la fecha en string
     *
     * @return string
     */
    public function __toString() {
        return $this->date;
    }

    /**
     * Suma meses a la fecha interna
     *
     */
    public function addMonths($month) {
        if ($this->month+$month > 12) {
            $this->month = ($month%12)+1;
            $this->year += ((int) ($month/12));
        } else {
            $this->month++;
        }
        $this->date = $this->year."-".sprintf("%02s", $this->month)."-".sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Resta meses a la fecha interna
     *
     */
    public function diffMonths($month) {
        if ($this->month-$month < 1) {
            $this->month = 12-(($month%12)+1);
            $this->year -= ((int) ($month/12));
        } else {
            $this->month--;
        }
        $this->date = $this->year."-".sprintf("%02s", $this->month)."-".sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Suma numero dias a la fecha actual
     *
     * @param integer $days
     * @return string
     */
    public function addDays($days) {
        $this->date = date("Y-m-d", $this->timestamp+$days*86400);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Resta numero dias a la fecha actual
     *
     * @param integer $days
     * @return string
     */
    public function diffDays($days) {
        $this->date = date("Y-m-d", $this->timestamp-$days*86400);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Suma un numero de a#os a la fecha interna
     *
     * @param numeric $years
     * @return string
     */
    public function addYears($years) {
        $this->year += $years;
        $this->date = $this->year."-".sprintf("%02s", $this->month)."-".sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Resta un numero de a#os a la fecha interna
     *
     * @param numeric $years
     * @return string
     */
    public function diffYears($years) {
        $this->year -= $years;
        $this->date = $this->year."-".sprintf("%02s", $this->month)."-".sprintf("%02s", $this->day);
        $this->consolideDate();
        return $this->date;
    }

    /**
     * Obtener usando un formato
     *
     * @param $format
     */
    public function getUsingFormat($format) {
        $datetime = new DateTime($this->date);
        return $datetime->format($format);
    }

    /**
     * Devuelve el nombre del dia de la semana
     *
     * @return string
     */
    public function getDayOfWeek() {
        $datetime = new DateTime($this->date);
        return $datetime->format("l");
    }

    /**
     * Resta una fecha de otra
     *
     */
    public function diffDate($date) {
        $date_parts = explode("-", $date);
        $year       = (int) $date_parts[0];
        $month      = (int) $date_parts[1];
        $day        = (int) $date_parts[2];
        $timestamp  = mktime(0, 0, 0, $month, $day, $year);
        return (int) (($this->timestamp-$timestamp)/86400);
    }

    /**
     * Devuelve true si la fecha interna es la de hoy
     *
     * @return boolean
     */
    public function isToday() {
        if ($this->date == date("Y-m-d")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Devuelve true si la fecha interna es la de ayer
     *
     * @return boolean
     */
    public function isYesterday() {
        $time = mktime(0, 0, 0, date("m"), date("d"), date("Y"))-86400;

        if ($this->timestamp == $time) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Devuelve true si la fecha interna es la de mañana
     *
     * @return boolean
     */
    public function isTomorrow() {
        $time = mktime(0, 0, 0, date("m"), date("d"), date("Y"))+86400;

        if ($this->timestamp == $time) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Consolida los valores internos de la fecha
     *
     */
    private function consolideDate() {
        $date_parts      = explode("-", $this->date);
        $this->year      = (int) $date_parts[0];
        $this->month     = (int) $date_parts[1];
        $this->day       = (int) $date_parts[2];
        $this->timestamp = mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }

}
