<?php

require_once "DB.php";
require_once "DB/storage.php";
require_once "pear-config.php";
require_once "pear-auth.php";
require_once "pear-database.php";

error_reporting(E_ALL);

if (empty($format)) {
    if (basename($PHP_SELF) == "xmlrpc.php") {
	$format = "xmlrpc";
    } else {
	$format = "html";
    }
}

include_once "pear-format-$format.php";
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

if ($SERVER_NAME != 'pear.php.net' && empty($dbh)) {
    $dbh = DB::connect(PEAR_DATABASE_DSN, array('persistent' => true));
}

?>
