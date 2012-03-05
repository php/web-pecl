<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2012 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Pierre Joye <pierre@php.net>                                |
   +----------------------------------------------------------------------+
   $Id$
*/

/* only support package maintainer for now, needed for bugs.php.net */
$package = filter_input(INPUT_GET, 'package', FILTER_SANITIZE_STRING);

if (!$package) {
	header("HTTP/1.0 404 Not Found");
	echo "$package not found";
	exit();
}


include PECL_INCLUDE_DIR . '/pear-database-package.php';

// Package data
$pkg   = package::info($package);
if (!$pkg) {
	header("HTTP/1.0 404 Not Found");
	echo "$package not found";
	exit();
}
$pacid = $pkg['packageid'];
$dbh->setFetchmode(DB_FETCHMODE_OBJECT);
$sth = $dbh->query("SELECT u.handle".
                   " FROM maintains m, users u".
                   " WHERE m.package = $pacid".
                   " AND m.handle = u.handle");
$maintainers = array();
while ($row = $sth->fetchRow()) {
	$maintainers[] = $row->handle;
}
echo json_encode($maintainers);
