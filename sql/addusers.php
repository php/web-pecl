<?php

print "Adding users...\n";

$users = '
alexmerz;*;Alexander Merz;;0
chregu;*;Christian Stocker;;0
cox;124854bf5ca680411fca8676e6014819;Tomas V.V.Cox;;1
jmcastagnetto;*;Jesus M. Castagnetto;;0
jon;*;Jon Parise;;0
kaltroft;*;Martin Kaltroft;;0
mj;4f5c2e35084da4469fb82cc494eeb847;Martin Jansen;;1
sebastian;*;Sebastian Bergmann;;0
sn;*;Sebastian Nohn;sebastian@nohn.net;0
ssb;aaXRbnur6Ub86;Stig S. Bakken;;1
zyprexia;*;Dave Mertens;dmertens@zyprexia.com;0
jimw;aai6p0orwS6qE;Jim Winstead;jimw@apache.org;1
andi;5rPeqa6EffAsk;Andi Gutmans;andi@zend.com;1
';

$sth = $dbh->prepare("INSERT INTO users ".
		     "(handle,password,name,email,registered,showemail," .
		     "created,createdby,admin)".
		     " VALUES(?,?,?,?,1,1,?,?,?)");
$me = getenv("USER");
$now = gmdate("Y-m-d H:i:s");
foreach (explode("\n", $users) as $line) {
    if (trim($line) == '') {
	continue;
    }
    list($user,$pw,$name,$email,$admin) = explode(";", trim($line));
    if (empty($email)) {
	$email = "{$user}@php.net";
    }
    $dbh->execute($sth, array($user,$pw,$name,$email,$now,$me,$admin));
    print "User: $user\n";
}

?>
