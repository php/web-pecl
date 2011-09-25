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

if (isset($_SERVER['PEAR_TMPDIR'])) {
    define('PEAR_TMPDIR', $_SERVER['PEAR_TMPDIR']);
    define('PEAR_CVS_TMPDIR', $_SERVER['PEAR_TMPDIR'].'/cvs');
    define('PEAR_UPLOAD_TMPDIR', $_SERVER['PEAR_TMPDIR'].'/uploads');
} else {
    define('PEAR_TMPDIR', '/var/tmp/pear');
    define('PEAR_CVS_TMPDIR', '/var/tmp/pear/cvs');
    define('PEAR_UPLOAD_TMPDIR', '/var/tmp/pear/uploads');
}

/**
 * The PEAR::DB DSN connection string
 *
 * To override default, set the value in $_ENV['PEAR_DATABASE_DSN']
 * before this file is included.
 */
if (isset($_SERVER['PEAR_DATABASE_DSN'])) {
    define('PEAR_DATABASE_DSN', $_SERVER['PEAR_DATABASE_DSN']);
} else {
    define('PECL_DB_USER', 'pear');
    define('PECL_DB_PASSWORD', 'pear');
    define('PECL_DB_HOST', 'localhost');
    define('PECL_DB_NAME', 'pear');

    define('PEAR_DATABASE_DSN', 'mysqli://' . PECL_DB_USER . ':' . PECL_DB_PASSWORD. '@' . PECL_DB_HOST. '/' . PECL_DB_NAME);
    define('PECL_DB_DSN', 'mysql:host=' . PECL_DB_HOST . ';dbname=' . PECL_DB_NAME); 
}

if (isset($_SERVER['PEAR_AUTH_REALM'])) {
    define('PEAR_AUTH_REALM', $_SERVER['PEAR_AUTH_REALM']);
} else {
    define('PEAR_AUTH_REALM', 'PEAR');
}
if (isset($_SERVER['PEAR_TARBALL_DIR'])) {
    define('PEAR_TARBALL_DIR', $_SERVER['PEAR_TARBALL_DIR']);
} else {
    define('PEAR_TARBALL_DIR', '/var/lib/pear'); 
}
if (isset($_SERVER['PEAR_REST_DIR'])) {
    define('PEAR_REST_DIR', $_SERVER['PEAR_REST_DIR']);
} else {
    define('PEAR_REST_DIR', '/var/lib/peclweb/rest'); 
}

if (isset($_SERVER['PEAR_PATCHES'])) {
    define('PEAR_PATCHES', $_SERVER['PEAR_PATCHES']);
} else {
    define('PEAR_PATCHES', '/var/lib/pear/patches/');
}
if (isset($_SERVER['PEAR_CVS'])) {
    define('PEAR_CVS', $_SERVER['PEAR_CVS']);
} else {
    define('PEAR_CVS', '/var/lib/pear/patches/cvs/');
}
if (isset($_SERVER['PHP_CVS_REPO_DIR'])) {
    define('PHP_CVS_REPO_DIR', $_SERVER['PHP_CVS_REPO_DIR']);
} else {
    define('PHP_CVS_REPO_DIR', '/repository/pear'); 
}

define('PEAR_COMMON_USER_NAME_REGEX', '/^[a-z][a-z0-9]+$/i');
define('PEAR_CHANNELNAME', 'pecl.php.net');

define('DAMBLAN_RSS_CACHE_DIR', PEAR_TMPDIR . '/rss_cache');
define('DAMBLAN_RSS_CACHE_TIME', 1800);
define('SVN_USERLIST', realpath(__DIR__.'/../data/svnusers.json'));

define('PECL_INCLUDE_DIR', __DIR__);