<?php

require_once "DB.php";
require_once "DB/storage.php";

require "pear-auth.php";
require "pear-database.php";

switch ($format) {
/*
    case "nativephp":
        break;
    case "xmlrpc":
        break;
*/
    case "html":
        break;
    default:
        $format = "html";
        break;
}

include "pear-format-$format.php";

$DSN = "mysql://pear:pear@localhost/pear";

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

if (!is_object($dbh)) {
    $dbh = DB::connect($DSN, array("debug" => 2));
}

if (DB::isError($dbh)) {
    error_handler($dbh);
}
$dbh->setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

?>
