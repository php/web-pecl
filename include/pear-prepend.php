<?php

require_once "DB.php";
require_once "DB/storage.php";

$pear_green = "#00b03b";

$DSN = "mysql://pear@localhost/pear";

function pageHeader($title = "PEAR: PHP Extension and Application Repository",
		    $style = false)
{
    global $_style, $pear_green;
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
    print "<FONT COLOR=\"#ffffff\" SIZE=\"-1\">";
    print "&nbsp;links<BR>";
    print "&nbsp;go<BR>";
    print "&nbsp;here<BR>";
    print "</TD><TD ALIGN=\"center\" VALIGN=\"middle\" WIDTH=\"100%\">";
    print "<FONT COLOR=\"#ffffff\" SIZE=\"+1\"><B>";
    print $title;
    print "</B></FONT></TD></TR></TABLE>\n";
    print "<BR><BR>\n";
    register_shutdown_function("pageFooter");
}

function pageFooter($style = false)
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

function validate($entity, $field, $value) {
    switch ("$entity/$field") {
	case "authors/handle":
	    if (!preg_match('/^[a-z][a-z0-9]+$/i', $value)) {
		return false;
	    }
	    break;
	case "authors/name":
	    if (!$value) {
		return false;
	    }
	    break;
	case "authors/email":
	    if (!preg_match('/[a-z0-9_\.\+%]@[a-z0-9\.]+\.[a-z]+$', $email)) {
		return false;
	    }
	    break;
    }
    return true;
}

function invalidHandle($handle) {
    if (preg_match('/^[a-z]+-?[a-z]+$/i', $handle)) {
	return false;
    }
    return 'handle must be all letters, optionally with one dash';
}

function invalidName($name) {
    if (trim($name)) {
	return $false;
    }
    return 'name must not be empty';
}

function invalidEmail($email) {
    if (preg_match('/[a-z0-9_\.\+%]@[a-z0-9\.]+\.[a-z]+$', $email)) {
	return false;
    }
    return 'invalid email address';
}

function invalidHomepage($homepage) {
    if (preg_match('!http://.+!i', $homepage)) {
	return false;
    }
    return 'invalid URL';
}

class Author extends DB_storage
{
    function Author(&$dbh, $handle = false) {
	$this->DB_storage("authors", "handle", &$dbh);
	if ($handle) {
	    $this->setup($handle);
	}
    }
}

function menuLink($text, $url) {
    print "<H3><A HREF=\"$url\"><IMG ALIGN=\"left\" SRC=\"/gifs/onlypear.gif\" WIDTH=19 HEIGHT=24 BORDER=0>$text</A></H3>\n";
}

function authReject($realm, $login_file = false)
{
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"$realm\"");
    if ($login_file) {
	include($login_file);
    } else {
	print "access denied";
    }
    exit;
}

function authRequire($level = 0)
{
    global $PHP_AUTH_USER, $PHP_AUTH_PW, $DSN, $dbh, $authorObject;

    $authorObject = new Author(&$dbh, strtoupper($PHP_AUTH_USER));
    if (DB::isError($authorObject) || md5($PHP_AUTH_PW) != $authorObject->password) {
	if ($level > 0) {
	    authReject("PEAR administrator");
	} else {
	    authReject("PEAR maintainer");
	}
    }
}

if (!is_object($dbh)) {
    $dbh = DB::connect($DSN);
}
if (DB::isError($dbh)) {
    pageHeader("Database Error");
    print "DB::connect error: ";
    print DB::errorMessage($dbh);
    print "<BR>\n";
    pageFooter();
    exit;
}

?>
