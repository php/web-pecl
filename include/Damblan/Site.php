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
 * Site-wide utilities
 *
 * @author  Martin Jansen <mj@php.net>
 * @package Damblan
 */
class Damblan_Site {

    /**
     * Factory method
     *
     * @access public
     * @return object Instance of Damblan_Site
     */
    function &factory() {
        static $_instance;

        if (!isset($_instance)) {
            $_instance = new Damblan_Site;
        }

        return $_instance;
    }

    /**
     * Raise a 404 Not Found error
     *
     * @param  mixed
     * @return void
     */
    function error404($error) {
        header("HTTP/1.0 404 Not Found");
        print "<h1>Not Found</h1>\n";
        $this->_errorPrint($error);
        exit();
    }

    function _errorPrint($error) {
        if (is_object($error)) {
            print $error->getMessage();
            if (DEVBOX) {
                print " " . $error->getDebugInfo();
            }
        } else {
            print $error;
        }
    }
}
?>