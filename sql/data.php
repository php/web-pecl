<?php

require_once "DB.php";
require_once "../include/pear-database.php";

PEAR::setErrorHandling(PEAR_ERROR_DIE);
list($progname, $type, $user, $pass, $db) = $argv;
$dbh = DB::connect("$type://$user:$pass@localhost/$db");

include "./addusers.php";
include "./addcategories.php";
include "./addpackages.php";

?>
