<?php

$pear_green = "#00b03b";

function response_header($title = "PEAR: the PHP Extension and Application Repository",
                         $style = false)
{
    global $_style, $pear_green, $PHP_AUTH_NAME, $PHP_AUTH_PW;
    global $_header_done;
    if ($_header_done) {
        return;
    }
    $_header_done = true;
    $_style = $style;
    print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
    print "<HTML><HEAD>\n";
    print " <TITLE>$title</TITLE>\n";
    print "</HEAD>\n";
    print "<BODY BGCOLOR=\"#ffffff\" TEXT=\"#000000\">\n";
    print "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=\"100%\">\n";
    print "<TR BGCOLOR=\"$pear_green\"><TD ALIGN=\"left\">";
    print "<IMG SRC=\"/gifs/pearsmall.gif\" WIDTH=96 HEIGHT=48 ALT=\"\">";
    print "</TD><TD ALIGN=\"left\" VALIGN=\"top\">";
    print "<FONT SIZE=\"-1\">";
    if ($PHP_AUTH_NAME) {
	print '&nbsp;<A HREF="logout.php"><FONT COLOR="#ffffff">';
	print "logout</FONT></A><BR>\n";
    } else {
	print '&nbsp;<A HREF="login.php"><FONT COLOR="#ffffff">';
	print "login</FONT></A><BR>\n";
    }
    print "</TD><TD ALIGN=\"center\" VALIGN=\"middle\" WIDTH=\"100%\">";
    print "<FONT COLOR=\"#ffffff\" SIZE=\"+1\"><B>";
    print $title;
    print "</B></FONT></TD></TR></TABLE>\n";
    print "<BR><BR>\n";
}

function response_footer($style = false)
{
    static $called;
    if ($called) {
	return;
    }
    $called = true;
    if (!$style) {
	$style = $GLOBALS['_style'];
    }
    print "</BODY></HTML>\n";
}

function menu_link($text, $url) {
    print "<H3><A HREF=\"$url\"><IMG ALIGN=\"left\" SRC=\"/gifs/onlypear.gif\" WIDTH=19 HEIGHT=24 BORDER=0>$text</A></H3>\n";
}

function report_error($error)
{
    if (PEAR::isError($error)) {
        $error = $error->getMessage();
    }
    print "<FONT COLOR=\"#990000\"><B>$error</B></FONT><BR>\n";
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
    response_header("Error");
    $report = "Error: $msg";
    if ($info) {
        $report .= ": $info";
    }
    for ($i = 0; $i < 3; $i++) {
        $report .= "</TD></TR></TABLE>";
    }
    print "<FONT COLOR=\"#990000\"><B>$report</B></FONT><BR>\n";
    response_footer();
    exit;
}

?>
