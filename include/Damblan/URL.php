<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * URL Parsing class
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 */
class Damblan_URL {

    var $_parameters    = array();
    var $_path_elements = array();

    /**
     * Constructor
     */
    function Damblan_URL() {
        if (count($_GET) > 0) {
            $this->_parameters = $_GET;
        }

        $path = preg_replace("=^" . preg_quote($_SERVER['SCRIPT_NAME']) . "=", "", $_SERVER['REQUEST_URI']);
        if ($path{0} == "/") {
            $path = substr($path, 1);

            $elements = explode("/", $path);
            foreach ($elements as $e) {
                $this->_path_elements[] = $e;
            }
        }
    }

    /**
     * Extract elements from the URL
     *
     * This method will first check if the parameter has been passed
     * to the script via the GET method. If that is not the case, it
     * will try to find them in the PATH_INFO. Thus using
     *
     *    /scripts.php?bar=hello
     *
     * and
     * 
     *    /scripts.php/hello
     *
     * will both have the same effect
     */
    function getElements(&$field) {
        $i = 0;

        foreach ($field as $key => $value) {
            if (strstr($key, "|")) {
                foreach (explode("|", $key) as $k) {
                    $res = $this->_find($k);
                    if (!empty($res)) {
                        break;
                    }
                }
            } else {
                $res = $this->_find($key);
            }

            $field[$key] = $res;
        }
    }

    function _find($name) {
        static $i = 0;

        $retVal = "";
        if (!empty($this->_parameters[$name])) {
            $retVal = $this->_parameters[$name];
        } else if (!empty($this->_path_elements[$i])) {
            $retVal = $this->_path_elements[$i];
            $i++;
        }

        return $retVal;
    }
}
?>