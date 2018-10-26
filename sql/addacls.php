<?php

require_once "DB.php";

$acl_paths = [];
$acl_users = [];
$group_members = [];
$group_comment = [];

$op = ini_get("include_path");
ini_set("include_path", ".:../../CVSROOT:/repository/CVSROOT");
$m4 = @fopen("gen_acl_file.m4", "r", true);
$avail = @fopen("avail", "r", true);
ini_set("include_path", $op);

if (is_resource($m4)) {
	while ($line = fgets($m4, 10240)) {
		if (preg_match("/^define\(`([^']+)'\s*,\s*`([^']+)'\)/", $line, $m)) {
			list(,$group,$members_str) = $m;
			$group_members[$group] = preg_split('/\s*,\s*/', $members_str);
			$group_comment[$group] = $comment;
			$comment = '';
		} elseif (preg_match('/^dnl\s*(.*)\s*$/', $line, $m)) {
			$comment = $m[1];
		}
	}
	fclose($m4);
} else {
	print "not a resource: \$m4\n";
}

$gh1 = $dbh->prepare("INSERT INTO cvs_groups (groupname,description) ".
					 "VALUES(?,?)");
$gh2 = $dbh->prepare("INSERT INTO cvs_group_membership (groupname,".
					 "username,granted_when,granted_by) VALUES(?,?,?,?)");
$dupes = 0;
foreach ($group_comment as $group => $comment) {
	$dbh->execute($gh1, [$group, $comment]);
	$members = $group_members[$group];
	foreach ($members as $member) {
		$dbh->expectError(DB_ERROR_ALREADY_EXISTS);
		$err = $dbh->execute($gh2, [$group, $member, $now, $me]);
		if (PEAR::isError($err) && $err->getCode() == DB_ERROR_ALREADY_EXISTS)
			$dupes++;
		$dbh->popExpect();
	}
	print "$group ($comment): ";
	print sizeof($members);
	print " members added\n";
}
print "$dupes duplicate memberships\n";

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
	$sth = $dbh->prepare("INSERT INTO cvs_acl (username,usertype,path,access)".
						 " VALUES(?,?,?,?)");
	$ent = 0;
	foreach ($acl_paths as $path => $acldata) {
		foreach ($acldata as $user => $foo) {
			$type = isset($group_comment[$user]) ? 'group' : 'user';
			$dbh->execute($sth, [$user, $type, $path, 1]);
			$ent++;
		}
	}
	print "$ent entries\n";
}

?>
