<?php

if (empty($what)) {
	$what = basename($HTTP_SERVER_VARS['PATH_INFO']);
}

header("Content-type: text/plain");

if ($what == "avail") {
	print "unavail\n";
	$sth = $dbh->query("SELECT handle,path FROM cvs_acl");
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
	$sth = $dbh->query("SELECT DISTINCT handle FROM cvs_acl WHERE access = 1");
	while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		print "{$row[0]}\n";
	}
}

?>