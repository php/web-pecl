<?php

require_once "DB.php";
require_once "DB/storage.php";
require_once "pear-config.php";
require_once "pear-auth.php";
require_once "pear-database.php";

if (empty($format) && basename($PHP_SELF) == "xmlrpc.php") {
    $format = "xmlrpc";
}

switch ($format) {
    case "xmlrpc":
        break;
    case "html":
        break;
    default:
        $format = "html";
        break;
}

include_once "pear-format-$format.php";
PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

if (!is_object($dbh)) {
    $dbh = DB::connect(PEAR_DATABASE_DSN,
                       array('persistent' => true));
}

?>
