<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:                                                             |
  +----------------------------------------------------------------------+
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
    $options = [
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    ];
    $GLOBALS['_NODB'] = true;
    $dbh = DB::connect(PEAR_DATABASE_DSN, $options);
    $dbh->query('SET NAMES utf8');
    $GLOBALS['_NODB'] = false;
}
if (!isset($pear_rest)) {
    if (!DEVBOX) {
        $pear_rest = new pear_rest(PEAR_REST_DIR);
    } else {
        $pear_rest = new pear_rest(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public_html' .
            DIRECTORY_SEPARATOR . 'rest');
    }
}

$tmp = filectime($_SERVER['SCRIPT_FILENAME']);
$LAST_UPDATED = date('D M d H:i:s Y', $tmp - date('Z', $tmp)) . ' UTC';

// Extend the session cookie lifetime
$params = session_get_cookie_params();
session_set_cookie_params(
    (!empty($_COOKIE['REMEMBER_ME'])) ? time()+86400 : null,
    $params['path'],
    $params['domain'],
    $params['secure'],
    $params['httponly']
);

session_start();
init_auth_user();
if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}
