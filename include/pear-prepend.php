<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
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
require_once "pear-config.php";

// silence the notices for production
if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] != 'pecl.php.net') {
    define('DEVBOX', true);
} else {
    error_reporting(error_reporting()&~E_NOTICE);
    define('DEVBOX', false);
}

require_once "PEAR.php";
include_once "pear-format-html.php";

include_once "DB.php";
include_once "DB/storage.php";
include_once "pear-auth.php";
include_once "pear-database.php";
include_once "pear-rest.php";
include_once "pear-win-package.php";

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
    $options = array(
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    );
    $GLOBALS['_NODB'] = true;
    $dbh = DB::connect(PEAR_DATABASE_DSN, $options);
    $dbh->query('SET NAMES utf8');
    $GLOBALS['_NODB'] = false;
}
if (!isset($pear_rest)) {
    if (!DEVBOX) {
        $pear_rest = new pear_rest(PEAR_REST_DIR);
    } else {
        $pear_rest = new pear_rest(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'public_html' .
            DIRECTORY_SEPARATOR . 'rest');
    }
}

$tmp = filectime($_SERVER['SCRIPT_FILENAME']);
$LAST_UPDATED = date('D M d H:i:s Y', $tmp - date('Z', $tmp)) . ' UTC';

// set the session cookie lifetime to 2038 (this is the old behaviour, maybe we should change it to something shorter :))
if(!empty($_COOKIE['REMEMBER_ME'])){
	call_user_func_array('session_set_cookie_params', array_merge(session_get_cookie_params(), array('lifetime' => time()+86400)));
}
else{
	call_user_func_array('session_set_cookie_params', array_merge(session_get_cookie_params(), array('lifetime' => null)));
}

session_start();
init_auth_user();
if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}
