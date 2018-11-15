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

/**
 * Application bootstrap and session initialization.
 */

use App\Utils\Filesystem;
use App\Utils\FormatDate;
use App\Utils\ImageSize;
use App\Rest;
use \DB as DB;

require_once __DIR__.'/bootstrap.php';

// silence the notices for production
if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] != 'pecl.php.net') {
    define('DEVBOX', true);
} else {
    error_reporting(error_reporting()&~E_NOTICE);
    define('DEVBOX', false);
}

require_once __DIR__.'/pear-format-html.php';
require_once __DIR__.'/pear-auth.php';

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

$filesystem = new Filesystem();
$formatDate = new FormatDate();
$imageSize = new ImageSize();

if (!isset($rest)) {
    $rest = new Rest($dbh, $filesystem);
    $rest->setDirectory($config->get('rest_dir'));
    $rest->setScheme($config->get('scheme'));
    $rest->setHost($config->get('host'));
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
