<?php

print "Adding categories...\n";

$dbh->expectError(DB_ERROR_NOSUCHTABLE);
$dbh->query('DELETE FROM categories');
$dbh->dropSequence('categories');
$dbh->popExpect();

$categories = '
Authentication;;
Benchmarking;;
Caching;;
Configuration;;
Console;;
Encryption;;
Database;;
Date and Time;;
File System;;
HTML;;
HTTP;;
Images;;
Logging;;
Mail;;
Math;;
Networking;;
Numbers;;
Payment;;
PEAR;PEAR infrastructure;
Scheduling;;
Science;;
XML;;
XML-RPC;;XML
';

$catids = [];
foreach (explode("\n", $categories) as $line) {
	if (trim($line) == '') {
		continue;
    }
	list($name, $desc, $parent) = explode(";", trim($line));
	$params = ['name' => $name, 'desc' => $desc];
	if (!empty($parent)) {
		$params['parent'] = $catids[$parent];
	}
	$catid = category::add($params);
	$catids[$name] = $catid;
	print "Category: $name\n";
}

?>
