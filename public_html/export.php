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
*/

exit;

if (empty($what)) {
	$what = basename($_SERVER['PATH_INFO']);
}

header("Content-type: text/plain");

if ($what == "avail") {
	print "unavail\n";
	$sth = $dbh->query("SELECT username,path FROM cvs_acl");
	while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED) === DB_OK) {
		$acl_paths[$row[1]][$row[0]] = true;
	}
	foreach ($acl_paths as $path => $acldata) {
		$users = implode(",", array_keys($acldata));
		print "avail|$users|$path\n";
	}
} elseif ($what == "cvsusers") {
	$sth = $dbh->query("SELECT handle,name,email FROM users");
	while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		print implode(":", $row) . "\n";
	}
} elseif ($what == "passwd") {
	$sth = $dbh->query("SELECT handle,password FROM users");
	while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		print implode(":", $row) . ":cvs\n";
	}
} elseif ($what == "writers") {
	$sth = $dbh->query("SELECT DISTINCT username FROM cvs_acl WHERE access = 1");
	while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		print "{$row[0]}\n";
	}
}
