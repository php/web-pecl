<?php

define('PEAR_CRED_DOWNLOAD', 0);
define('PEAR_CRED_UPLOAD', 1);
define('PEAR_CRED_ADMIN', 2);

/*
function use($package)
{
    global $USED_PACKAGES;
    if ($USED_PACKAGES[$package]) {
	return;
    }
    if (@include($package . '.php')) {
	$USED_PACKAGES[$package] = true;
    } else {
	die("Unable to load package \"$package\"!");
    }
}
*/

function pageHeader($title = "PEAR: PHP Extension and Add-on Repository",
		    $style = false)
{
    $GLOBALS['_style'] = $style;
    print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
    print "<HTML><HEAD>\n";
    print " <TITLE>$title</TITLE>\n";
    print "</HEAD>\n";
    print "<BODY BGCOLOR=\"#ffffff\" TEXT=\"#000000\">\n";
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

function formMultipleInputRow($data) {
    reset($data);
    while (list($var, $title) = each($data)) {
	global $$var;
	formInputRow($title, $var, $$var);
    }
}

function formInputCell($name, $default = '', $size = 20) {
    print "  <TD><INPUT NAME=\"$name\" VALUE=\"$default\" SIZE=\"$size\"></TD>\n";
}

function formInputRow($title, $name, $default = '', $size = 20) {
    print " <TR>\n";
    print "  <TH ALIGN=\"right\">$title</TH>";
    formInputCell($name, $default, $size);
    print " </TR>\n";
}

function formCheckbox($name, $default = false) {
    print "<INPUT TYPE=\"checkbox\" NAME=\"$name\"";
    if ($default && $default != 'off') {
	print " CHECKED";
    }
    print ">";
}

function formCheckboxCell($name, $default = false) {
    print "  <TD>";
    formCheckbox($name, $default);
    print "</TD>\n";
}

function formCheckboxRow($title, $name, $default = false) {
    print " <TR>\n";
    print "  <TH ALIGN=\"right\">$title</TH>";
    formCheckboxCell($name, $default);
    print " </TR>\n";
}

function formSubmit($title = 'Submit Changes') {
    print "<INPUT TYPE=\"submit\" VALUE=\"$title\">";
}

function formSubmitRow($title = 'Submit Changes') {
    print " <TR>\n";
    print "  <TD>&nbsp</TD>\n";
    print "  <TD>";
    formSubmit($title);
    print "</TD>\n";
    print " </TR>\n";
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

class Author {
    var $handle, $password, $name, $email, $homepage, $created,
	$homepage, $modified, $createdby, $showemail, $registered,
	$credentials, $authorinfo;
    function Author() {
	
    }
}

function add_author(&$dbh, $handle, $name, $email, $homepage, $createdby,
		    $showemail, $credentials, $authorinfo)
{
    $query = 'INSERT INTO authors (handle,password,name,email,homepage,'.
	'created,modified,createdby,showemail,registered,credentials,'.
	'authorinfo) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $dbh->prepare($query);
    // XXX not finished
    return $dbh->execute($stmt, array($handle, $passwd, $name, $email,
				      $homepage, $created, $modified,
				      $modifiedy, $showemail, $registered,
				      $credentials, $authorinfo));
}

?>
