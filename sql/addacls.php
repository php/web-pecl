<?php

$acl_paths = array();
$acl_users = array();

$fp = @fopen("avail", "r");
if (empty($fp)) {
	$fp = @fopen("../../CVSROOT/avail", "r");
}

if (is_resource($fp)) {
	while ($line = fgets($fp, 10240)) {
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
	print "Setting up CVS ACLs...";
	$sth = $dbh->prepare("INSERT INTO cvs_acl (handle,path,access) ".
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