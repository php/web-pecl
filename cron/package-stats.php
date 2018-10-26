#!/usr/local/bin/php -Cq
<?php
/*
* +----------------------------------------------------------------------+
* | PEAR Web site version 1.0                                            |
* +----------------------------------------------------------------------+
* | Copyright (c) 2001 The PHP Group                                     |
* +----------------------------------------------------------------------+
* | This source file is subject to version 2.02 of the PHP license,      |
* | that is bundled with this package in the file LICENSE, and is        |
* | available at through the world-wide-web at                           |
* | http://www.php.net/license/2_02.txt.                                 |
* | If you did not receive a copy of the PHP license and are unable to   |
* | obtain it through the world-wide-web, please send a note to          |
* | license@php.net so we can mail you a copy immediately.               |
* +----------------------------------------------------------------------+
* | Authors: Richard Heyes <richard@php.net>                             |
* +----------------------------------------------------------------------+
*
* This short script populates the package_stats table and should be run
* via cron.
*/
	require_once('DB.php');
/**
* DSN for pear packages database
*/
	$dsn = "mysql://pear:pear@localhost/pear";
	$dbh = DB::connect($dsn);
	if (DB::isError($db = DB::connect($dsn))) {
		die ("Failed to connect: $dsn\n");
	}
	$dbh->query('SET NAMES utf8');

/**
* Query the packages info and insert the results into
* the package_stats page. First deletes the current
* data.
*/
	$db->query('DELETE FROM package_stats');

	$sql = 'INSERT INTO package_stats SELECT COUNT(d.id) AS dl_number, p.name AS package, r.version AS release, p.id AS pid, r.id AS rid, p.category AS cid
	            FROM downloads d, packages p, releases r
	            WHERE d.package = p.id AND d.release = r.id
	          GROUP BY d.package, d.release ORDER BY dl_number DESC';

	if (DB::isError($db->query($sql))) {
		die('Query failed');
	}
