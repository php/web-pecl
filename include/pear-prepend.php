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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "DB.php";
require_once "DB/storage.php";
require_once "pear-config.php";
require_once "pear-auth.php";
require_once "pear-database.php";
require_once "pear-manual.php";
require_once "browser.php";

error_reporting(E_ALL);

if ($_SERVER['SERVER_NAME'] != 'pear.php.net' || isset($_COOKIE['pear_dev'])) {
    define('DEVBOX', true);
    include_once "pear-debug.php";
} else {
    define('DEVBOX', false);
}

if (empty($format)) {
    if (basename($_SERVER['PHP_SELF']) == "xmlrpc.php") {
        $format = "xmlrpc";
    } else {
        $format = "html";
    }
}

include_once "pear-format-$format.php";

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

function get($name)
{
    if (!empty($_GET[$name])) {
        return $_GET[$name];
    } else if (!empty($_POST[$name])) {
        return $_POST[$name];
    } else {
        return "";
    }
}
    
if (empty($dbh)) {
    $dbh = DB::connect(PEAR_DATABASE_DSN, array('persistent' => false));
}

$LAST_UPDATED = date("D M d H:i:s Y T", filectime($_SERVER['SCRIPT_FILENAME']));

if (@$_GET['logout'] === '1') {
    if (isset($_COOKIE['PEAR_USER'])) {
        setcookie('PEAR_USER', '', 0, '/');
        unset($_COOKIE['PEAR_USER']);
    }
    if (isset($_COOKIE['PEAR_PW'])) {
        setcookie('PEAR_PW', '', 0, '/');
        unset($_COOKIE['PEAR_PW']);
    }
}

if (!empty($_COOKIE['PEAR_USER']) && !@auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    $__user = $_COOKIE['PEAR_USER'];
    setcookie('PEAR_USER', '', 0, '/');
    unset($_COOKIE['PEAR_USER']);
    setcookie('PEAR_PW', '', 0, '/');
    unset($_COOKIE['PEAR_PW']);
    $msg = "Invalid username ($__user) or password";
    if ($format == 'html') {
        $msg .= " <a href=\"/?logout=1\">[logout]</a>";
    }
    auth_reject(null, $msg);
}

if (!function_exists('file_get_contents')) {
    function file_get_contents($file, $use_include_path = false) {
        if (!$fp = fopen($file, 'r', $use_include_path)) {
            return false;
        }
        $data = fread($fp, filesize($file));
        fclose($fp);
        return $data;
    }
}

session_start();

/**
* Browser detection
*/
	$_browser = &new browser();

?>
