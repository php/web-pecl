<?php

function response_header($title = null, $style = null)
{
    static $called;
    if ($called) {
        return;
    }
    $called = true;
    print "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
    print "<PearResponse>\n";
}

function response_footer($style = false)
{
    static $called;
    if ($called) {
	return;
    }
    $called = true;
    print "</PearResponse>\n";
}

function menu_link($text, $url) {
}

function report_error($error)
{
    if (PEAR::isError($error)) {
        $error = $error->getMessage();
    }
    print "<Error><Message>$error</Message></Error>\n";
}

function error_handler($errobj)
{
    if (PEAR::isError($errobj)) {
        $msg = $errobj->getMessage();
        $info = $errobj->getUserInfo();
    } else {
        $msg = $errobj;
        $info = '';
    }
    response_header();
    $report = "$msg";
    if ($info) {
        $report .= ": $info";
    }
    print "<Error><Message>$report</Message></Error>\n";
    response_footer();
    exit;
}

?>
