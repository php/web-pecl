<?php

require_once "XML/RPC.php";
require_once "XML/RPC/Server.php";

$GLOBALS['_return_value'] = null;

function response_header($title = null, $style = null)
{
    static $called;
    if ($called) {
        return;
    }
    $called = true;
}

function response_footer($style = false)
{
    global $_return_value;
    static $called;
    if ($called) {
	return;
    }
    $called = true;
}

function menu_link($text, $url) {
}

function &xmlrpc_error($str) {
    global $XML_RPC_erruser;
    $error = new XML_RPC_Response(0, $XML_RPC_erruser, $str);
    return $error;
}

function report_error($error)
{
    global $_return_value, $XML_RPC_erruser;
    if (PEAR::isError($error)) {
        $error = $error->getMessage();
    }
    $_return_value = new XML_RPC_Response(0, $XML_RPC_erruser, $error);
}

function error_handler($errobj)
{
    global $_return_value, $XML_RPC_erruser;
    if (PEAR::isError($errobj)) {
        $msg = $errobj->getMessage();
        $info = $errobj->getUserInfo();
    } else {
        $msg = $errobj;
        $info = '';
    }
    $report = "$msg";
    if ($info) {
        $report .= " ** $info";
    }
    report_error($report);
}

?>
