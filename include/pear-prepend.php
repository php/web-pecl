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

if (@$_GET['logout'] === '1') {
    if (isset($_COOKIE['PEAR_USER'])) {
        setcookie('PEAR_USER', '', 0, '/');
        unset($_COOKIE['PEAR_USER']);
    }
    if (isset($_COOKIE['PEAR_PW'])) {
        setcookie('PEAR_PW', '', 0, '/');
        unset($_COOKIE['PEAR_PW']);
    }
}

if (!empty($_COOKIE['PEAR_USER']) && !@auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    $__user = $_COOKIE['PEAR_USER'];
    setcookie('PEAR_USER', '', 0, '/');
    unset($_COOKIE['PEAR_USER']);
    setcookie('PEAR_PW', '', 0, '/');
    unset($_COOKIE['PEAR_PW']);
    $msg = "Invalid username ($__user) or password";
    if ($format == 'html') {
        $msg .= " <a href=\"/?logout=1\">[logout]</a>";
    }
    auth_reject(null, $msg);
}

if (!function_exists('file_get_contents')) {
    function file_get_contents($file, $use_include_path = false) {
        if (!$fp = fopen($file, 'r', $use_include_path)) {
            return false;
        }
        $data = fread($fp, filesize($file));
        fclose($fp);
        return $data;
    }
}

/**
* Returns an absolute URL using Net_URL
*
* @param  string $url All/part of a url
* @return string      Full url
*/
function getURL($url)
{
	include_once('Net/URL.php');
	$obj = new Net_URL($url);
	return $obj->getURL();
}

/**
* Redirects to the given full or partial URL.
* will turn the given url into an absolute url
* using the above getURL() function. This function
* does not return.
*
* @param string $url Full/partial url to redirect to
*/
function redirect($url)
{
	header('Location: ' . getURL($url));
	exit;
}
?>
