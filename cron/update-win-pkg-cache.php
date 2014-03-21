#!/usr/local/bin/php -Cq
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4; */
// +---------------------------------------------------------------------+
// |  Authors:  Anatol Belski <ab@php.net>                               |
// +---------------------------------------------------------------------+
//

require_once dirname(__FILE__) . '/../include/pear-prepend.php';

/*require_once "PEAR.php";
require_once "DB.php";

require_once dirname(__FILE__) . '/../include/pear-win-package.php';*/

$dbh = DB::connect("mysql://pear:pear@localhost/pear");
if (DB::isError($dbh)) {
	die("could not connect to database");
}

$data = $dbh->getAll("SELECT packages.name, releases.version, releases.releasedate 
						FROM packages, releases
						WHERE packages.id = releases.package",
					NULL,
					DB_FETCHMODE_ASSOC);

if (package_dll::isResetOverdue()) {
	package_dll::resetDllDownloadCache();
}

foreach ($data as $pkg) {
	//$urls = package_dll::getDllDownloadUrls($pkg['name'], $pkg['version'], $pkg['releasedate'], true);
	if (!package_dll::updateDllDownloadCache($pkg['name'], $pkg['version'])) {
		echo "Failed to update cache for $pkg[name]-$pkg[version]\n";
	}
}

