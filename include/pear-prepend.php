<?php

require_once "DB.php";
require_once "DB/storage.php";
require_once "pear-config.php";
require_once "pear-auth.php";
require_once "pear-database.php";

error_reporting(E_ALL);

if ($HTTP_SERVER_VARS['SERVER_NAME'] != 'pear.php.net' || isset($HTTP_COOKIE_VARS['pear_dev'])) {
    define('DEVBOX', true);
    include_once "pear-debug.php";
} else {
    define('DEVBOX', false);
}

if (empty($format)) {
    if (basename($PHP_SELF) == "xmlrpc.php") {
        $format = "xmlrpc";
    } else {
        $format = "html";
    }
}

include_once "pear-format-$format.php";

if (DEVBOX && empty($dbh)) {
    $dbh = DB::connect(PEAR_DATABASE_DSN, array('persistent' => true));
}

?>
