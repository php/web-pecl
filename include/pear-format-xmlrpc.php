<?php

require_once "xmlrpc.inc";
require_once "xmlrpcs.inc";

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
    global $xmlrpcerruser;
    $error = new xmlrpcresp(0, $xmlrpcerruser, $str);
    return $error;
}

function report_error($error)
{
    global $_return_value, $xmlrpcerruser;
    if (PEAR::isError($error)) {
        $error = $error->getMessage();
    }
    $_return_value = new xmlrpcresp(0, $xmlrpcerruser, $error);
}

function error_handler($errobj)
{
    global $_return_value, $xmlrpcerruser;
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
