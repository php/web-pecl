<?php

if ($what == "avail") {
	header("Content-type: text/plain");
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
	header("Content-type: text/plain");
	$sth = $dbh->query("SELECT handle,name,email FROM users");
	while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		print implode(":", $row) . "\n";
	}
} elseif ($what == "passwd") {
	header("Content-type: text/plain");
	$sth = $dbh->query("SELECT handle,password FROM users");
	while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED)) {
		print implode(":", $row) . ":cvs\n";
	}
}

?>