<?php

require_once "DB.php";

PEAR::setErrorHandling(PEAR_ERROR_DIE);
list($progname, $type, $user, $pass, $db) = $argv;
$dbh = DB::connect("$type://$user:$pass@localhost/$db");

$dbh->query("INSERT INTO users VALUES('ssb', '738aa8d3bc02eb8712acd0eb2cf6dfd5', 'Stig S. Bakken', 'stig@php.net', 'http://www.pvv.org/~ssb/', '2001-04-18', 'ssb', NULL, 1, 1, 1, NULL)");
$dbh->query("INSERT INTO users VALUES('mj', '4f5c2e35084da4469fb82cc494eeb847', 'Martin Jansen', 'mj@php.net', 'http://martin-jansen.de/', '2001-05-24', 'mj', NULL, 1, 1, 1, NULL)");
$dbh->query("INSERT INTO users VALUES('cox', '124854bf5ca680411fca8676e6014819', 'Tomas V.V.Cox', 'cox@php.net', 'http://vulcanonet.com', '2001-08-31', 'cox', NULL, 1, 1, 1, NULL)");

?>