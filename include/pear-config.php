<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
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
    define('PEAR_TMPDIR', '/tmp/pear');
    define('PEAR_CVS_TMPDIR', '/tmp/pear');
    define('PEAR_UPLOAD_TMPDIR', '/tmp/pear');
}

if (isset($_SERVER['PEAR_DATABASE_DSN'])) {
    define('PEAR_DATABASE_DSN', $_SERVER['PEAR_DATABASE_DSN']);
} else {
    define('PEAR_DATABASE_DSN', 'mysql://alan@localhost/pear');
}
if (isset($_SERVER['PEAR_AUTH_REALM'])) {
    define('PEAR_AUTH_REALM', $_SERVER['PEAR_AUTH_REALM']);
} else {
    define('PEAR_AUTH_REALM', 'PEAR');
}
if (isset($_SERVER['PEAR_TARBALL_DIR'])) {
    define('PEAR_TARBALL_DIR', $_SERVER['PEAR_TARBALL_DIR']);
} else {
    define('PEAR_TARBALL_DIR', '/tmp/pear');
}
if (isset($_SERVER['PHP_CVS_REPO_DIR'])) {
    define('PHP_CVS_REPO_DIR', $_SERVER['PHP_CVS_REPO_DIR']);
} else {
    define('PHP_CVS_REPO_DIR', '/tmp/pear');
}

?>
