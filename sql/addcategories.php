<?php

print "Adding categories...\n";

$dbh->expectError(DB_ERROR_NOSUCHTABLE);
$dbh->query('DELETE FROM categories');
$dbh->dropSequence('categories');
$dbh->popExpect();

$categories = '
Authentication;
Benchmarking;
Caching;
Configuration;
Console;
Encryption;
Database;
Date and Time;
File System;
HTML;
HTTP;
Images;
Logging;
Mail;
Math;
Networking;
Numbers;
Payment;
PEAR;PEAR infrastructure
Scheduling;
Science;
XML;
';

foreach (explode("\n", $categories) as $line) {
    if (trim($line) == '') {
	continue;
    }
    list($name, $desc) = explode(";", trim($line));
    add_category(array('name' => $name, 'desc' => $desc));
    print "Category: $name\n";
}

?>