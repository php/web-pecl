<?php

require_once "DB.php";
require_once "DB/storage.php";
require_once "pear-config.php";
require_once "pear-auth.php";
require_once "pear-database.php";
require_once "pear-manual.php";

error_reporting(E_ALL);

if ($_SERVER['SERVER_NAME'] != 'pear.php.net' || isset($_COOKIE['pear_dev'])) {
    define('DEVBOX', true);
    include_once "pear-debug.php";
} else {
    define('DEVBOX', false);
}

if (empty($format)) {
    if (basename($_SERVER['PHP_SELF']) == "xmlrpc.php") {
        $format = "xmlrpc";
    } else {
        $format = "html";
    }
}

include_once "pear-format-$format.php";

/**
 * Interface to uptime
 *
 * Tell how long the system has been running.
 *
 * @return string
 */
function uptime()
{
    $result = exec("uptime");

    $elements = split(" ", $result);

    foreach ($elements as $key => $value) {
        if ($value == "up") {
            $uptime = $elements[$key+1] . " " . str_replace(",", "", $elements[$key+2]);
            break;
        }
    }

    return $uptime;
}

if (empty($dbh)) {
    $dbh = DB::connect(PEAR_DATABASE_DSN, array('persistent' => true));
}

$LAST_UPDATED = date("D M d H:i:s Y T", filectime($_SERVER['SCRIPT_FILENAME']));

if (isset($_COOKIE['PEAR_USER']) && !auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    unset($_COOKIE['PEAR_USER']);
    unset($_COOKIE['PEAR_PW']);
    auth_reject(null, "Invalid username or password");
}

?>
