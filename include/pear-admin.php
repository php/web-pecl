<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
 * Interface to uptime
 *
 * Tell how long the system has been running.
 *
 * @return string
 */
function uptime()
{
    $result = exec("uptime");

    $elements = split(" ", $result);

    foreach ($elements as $key => $value) {
        if ($value == "up") {
            $uptime = $elements[$key+1] . " " . str_replace(",", "", $elements[$key+2]);
            break;
        }
    }

    return $uptime;
}
?>