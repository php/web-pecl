<?php

print "Adding users...\n";

$hardcoded_users = '
alexmerz;*;Alexander Merz;;0
chregu;*;Christian Stocker;;0
cox;124854bf5ca680411fca8676e6014819;Tomas V.V.Cox;;1
jmcastagnetto;*;Jesus M. Castagnetto;;0
jon;*;Jon Parise;;0
kaltroft;*;Martin Kaltroft;;0
mj;4f5c2e35084da4469fb82cc494eeb847;Martin Jansen;;1
sebastian;*;Sebastian Bergmann;;0
sn;*;Sebastian Nohn;sebastian@nohn.net;0
ssb;aaXRbnur6Ub86;Stig S. Bakken;ssb@fast.no;1
zyprexia;*;Dave Mertens;dmertens@zyprexia.com;0
jimw;aai6p0orwS6qE;Jim Winstead;jimw@apache.org;1
andi;5rPeqa6EffAsk;Andi Gutmans;andi@zend.com;1
rasmus;;;;1
zeev;;;;1
jimw;;;;1
andrei;;;;1
thies;;;;1
';

$users = array(); // array(user=>array(user,pw,name,email,admin),...)
foreach (explode("\n", $hardcoded_users) as $line) {
	$line = trim($line);
    if (empty($line)) {
		continue;
    }
	$tmp = explode(";", trim($line));
	$users[$tmp[0]]['user'] = $tmp[0];
	$users[$tmp[0]]['pw'] = $tmp[1];
	$users[$tmp[0]]['name'] = $tmp[2];
	$users[$tmp[0]]['email'] = $tmp[3];
	$users[$tmp[0]]['admin'] = $tmp[4];
}

$fp = @fopen("cvsusers", "r");
if (is_resource($fp)) {
	while ($line = fgets($fp, 1024)) {
		if (!trim($line)) continue;
		list($user,$name,$email) = explode(":", trim($line));
		$name = preg_replace('/\s\s+/', ' ', $name);
		$users[$user]['user'] = $user;
		$users[$user]['name'] = $name;
		$users[$user]['email'] = $email;
	}
	fclose($fp);
}

$fp = @fopen("passwd", "r");
if (is_resource($fp)) {
	while ($line = fgets($fp, 1024)) {
		if (!trim($line)) continue;
		list($user,$pw,$groups) = explode(":", trim($line));
		$users[$user]['pw'] = $pw;
	}
	fclose($fp);
}

//print_r($users);exit;

$sth = $dbh->prepare("INSERT INTO users ".
		     "(handle,password,name,email,registered,showemail," .
		     "created,createdby,admin)".
		     " VALUES(?,?,?,?,1,1,?,?,?)");
$me = getenv("USER");
$now = gmdate("Y-m-d H:i:s");
$users_added = 0;
foreach ($users as $username => $info) {
	$user = $username;
	$pw = $info['pw'];
	$name = $info['name'];
	if (empty($info['email'])) {
		$email = "$user@php.net";
	} else {
		$email = $info['email'];
	}
	$admin = (bool)$info['admin'];
    if (empty($email)) {
		$email = "{$user}@php.net";
    }
    $dbh->execute($sth, array($user,$pw,$name,$email,$now,$me,$admin));
	$users_added++;
}
print "$users_added users added.\n";

?>
