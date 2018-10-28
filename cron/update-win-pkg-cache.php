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
  | Authors: Anatol Belski <ab@php.net>                                  |
  +----------------------------------------------------------------------+
*/

require_once __DIR__ . '/../include/pear-prepend.php';

/*require_once "PEAR.php";
require_once "DB.php";

require_once __DIR__ . '/../include/pear-win-package.php';*/

$dbh = DB::connect("mysql://pear:pear@localhost/pear");
if (DB::isError($dbh)) {
	die("could not connect to database");
}
$dbh->query('SET NAMES utf8');

$data = $dbh->getAll("SELECT packages.name, releases.version, releases.releasedate
						FROM packages, releases
						WHERE packages.id = releases.package",
					NULL,
					DB_FETCHMODE_ASSOC);

if (package_dll::isResetOverdue()) {
	if (!package_dll::resetDllDownloadCache()) {
		/* some reset running, just sleep and do our thing*/
		sleep(10);
	}
}

foreach ($data as $pkg) {
	//$urls = package_dll::getDllDownloadUrls($pkg['name'], $pkg['version'], $pkg['releasedate'], true);
	if (!package_dll::updateDllDownloadCache($pkg['name'], $pkg['version'])) {
		echo "Failed to update cache for $pkg[name]-$pkg[version]\n";
	}
}
