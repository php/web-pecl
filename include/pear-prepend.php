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
 * Application bootstrap.
 */

require_once __DIR__.'/bootstrap.php';

require_once __DIR__.'/pear-config.php';

// silence the notices for production
if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] != 'pecl.php.net') {
    define('DEVBOX', true);
} else {
    error_reporting(error_reporting()&~E_NOTICE);
    define('DEVBOX', false);
}

// Set application default time zone to UTC for all dates.
date_default_timezone_set('UTC');

require_once 'PEAR.php';
require_once 'DB.php';
require_once 'DB/storage.php';
require_once __DIR__.'/pear-format-html.php';
require_once __DIR__.'/pear-auth.php';
require_once __DIR__.'/pear-database.php';
require_once __DIR__.'/../src/Rest.php';
require_once __DIR__.'/../src/PackageDll.php';
require_once __DIR__.'/../src/Utils/Filesystem.php';
require_once __DIR__.'/../src/Utils/FormatDate.php';
require_once __DIR__.'/../src/Utils/ImageSize.php';

use App\Utils\Filesystem;
use App\Utils\FormatDate;
use App\Utils\ImageSize;

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
