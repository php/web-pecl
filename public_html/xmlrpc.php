<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

if (!isset($HTTP_RAW_POST_DATA)) {
    die('invalid XML RPC request');
}
set_error_handler("xmlrpc_error_handler");

require_once "xmlrpc-methods.php";
require_once "xmlrpc-cache.php";

$xs = xmlrpc_server_create();
pear_register_xmlrpc_methods($xs);

$cache = new XMLRPC_Cache;

$method   = "";
$response = null;
$params   = xmlrpc_decode_request($HTTP_RAW_POST_DATA, &$method);

// Read cache
if (isset($_GET['maxAge']) && ((int)$_GET['maxAge']) > 0) {
    $maxAge = $_GET['maxAge'];
} else {
    $maxAge = null;
};

if ($method == "package.listAll")
{
    if (!isset($params[0])) {
        $params = array(true);
    };
    $response = $cache->get($method, $params, $maxAge);
};
if ($method == "package.info" && (!isset($params[1]) || $params[1] === null))
{
    if (!isset($params[1])) {
        $params[1] = null;
    };
    $response = $cache->get($method, $params, $maxAge);
};

if ($response !== null) {
    if (strlen($response) > 0) {
        $response .= '<!-- Used Cache -->';
        header('Content-type: text/xml');
        header('Content-length: '.strlen($response));
        print $response;
    } else {
        header('HTTP/1.0 304 Not Modified');
    };
    exit;
};

$response = xmlrpc_server_call_method($xs, $HTTP_RAW_POST_DATA, null,
                                      array('output_type' => 'xml'));

// Save cache
if ($method == "package.listAll")
{
    if (!isset($params[0])) {
        $params = array(true);
    };
    $cache->save($method, $params, $response);
};
if ($method == "package.info" && (!isset($params[1]) || $params[1] === null))
{
    if (!isset($params[1])) {
        $params[1] = null;
    };
    $cache->save($method, $params, $response);
};

header('Content-type: text/xml');
header('Content-length: '.strlen($response));
print $response;

if (!defined('E_STRICT')) {
    define('E_STRICT', 2048);
}
function xmlrpc_error_handler($errno, $errmsg, $file, $line, $vars)
{
    if (error_reporting() == 0 || $errno == E_STRICT) {
        return;
    }
    static $errortype = array (
        1   =>  "Error",
        2   =>  "Warning",
        4   =>  "Parsing Error",
        8   =>  "Notice",
        16  =>  "Core Error",
        32  =>  "Core Warning",
        64  =>  "Compile Error",
        128 =>  "Compile Warning",
        256 =>  "User Error",
        512 =>  "User Warning",
        1024=>  "User Notice"
    );
    $prefix = $errortype[$errno];
    $file = basename($file);
    $error_message = "$file:$line: $prefix: $errmsg";
    $response = "<?xml version='1.0' encoding='iso-8859-1' ?>
<methodResponse>
<fault>
 <value>
  <struct>
   <member>
    <name>faultString</name>
    <value>
     <string>$error_message</string>
    </value>
   </member>
   <member>
    <name>faultCode</name>
    <value>
     <int>$errno</int>
    </value>
   </member>
  </struct>
 </value>
</fault>
</methodResponse>
";
    header("Content-length: " . strlen($response));
    header("Content-type: text/xml");
    print $response;
    exit;
}

?>
