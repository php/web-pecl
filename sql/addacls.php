<?php

require_once "DB.php";

PEAR::setErrorHandling(PEAR_ERROR_DIE, "%s\n");
$dbh = DB::connect("mysql://pear:pear@localhost/pear");

$acl_paths = array();
$acl_users = array();

$op = ini_get("include_path");
ini_set("include_path", ".:../../CVSROOT:/repository/CVSROOT");
$m4 = @fopen("gen_acl_file.m4", "r", true);
$avail = @fopen("avail", "r", true);
ini_set("include_path", $op);

if (is_resource($m4)) {
	while ($line = fgets($m4, 10240)) {
		if (preg_match("/^define\(`([^']+)'\s*,\s*`([^']+)'\)/", $line, $m)) {
			list(,$group,$members) = $m;
			print "$group ($comment): ";
			print sizeof(preg_split('/\s*,\s*/', $members));
			print " members\n";
		} elseif (preg_match('/^dnl\s*(.*)\s*$/', $line, $m)) {
			$comment = $m[1];
		}
	}
	fclose($m4);
} else {
	print "not a resource: \$m4\n";
}

if (is_resource($avail)) {
	while ($line = fgets($avail, 10240)) {
		if (substr($line, 0, 6) != "avail|") {
			continue;
		}
		list(,$user,$path) = explode("|", trim($line));
		$ua = explode(",", $user);
		$pa = explode(",", $path);
		foreach ($ua as $u) {
			foreach ($pa as $p) {
				$acl_paths[$p][$u] = true;
				$acl_users[$u][$p] = true;
			}
		}
	}
	fclose($avail);
	print "Setting up CVS ACLs...";
	$sth = $dbh->prepare("INSERT INTO cvs_acl (username,path,access) ".
						 "VALUES(?,?,?)");
	$ent = 0;
	foreach ($acl_paths as $path => $acldata) {
		foreach ($acldata as $user => $foo) {
			$dbh->execute($sth, array($user, $path, 1));
			$ent++;
		}
	}
	print "$ent entries\n";
}

?>