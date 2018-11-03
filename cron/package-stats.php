#!/usr/local/bin/php -Cq
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
  | Authors: Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

/**
 * This short script populates the package_stats table and should be run
 * via cron.
 */

require_once 'DB.php';

/**
* DSN for PECL packages database
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
