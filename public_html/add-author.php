<?php

require_once "html_form.php";

authRequire();

$display_form = true;

function displayError($msg)
{
    global $errorMsg;
    $errorMsg .= "<FONT COLOR=\"#CC0000\">$msg</FONT><BR>\n";
}

$jumpto = "handle";

do {
    if ($submit) {
	$required = array("handle", "name", "email");
	while (list($i, $field) = each($required)) {
	    if (!$$field) {
		displayError("Please enter $field");
		$jumpto = $field;
		break 2;
	    }
	}

	if (!preg_match("/^[a-z][a-z0-9]+$/i", $handle)) {
	    displayError("Handle must start with a letter and contain ".
			 "only letters and digits.");
	    break;
	}

	if ($password != $password2) {
	    displayError("Passwords did not match");
	    $password = $password2 = "";
	    break;
	}

	if (!$password) {
	    displayError("Empty passwords not allowed");
	    break;
	}

	$handle = strtoupper($handle);
	$obj = new Author(&$dbh);
	$err = $obj->insert($handle);
	if (DB::isError($err)) {
	    displayError("$handle: " . DB::errorMessage($err));
	    $jumpto = "handle";
	    break;
	}

	$display_form = false;
	$obj->name = $name;
	$obj->email = $email;
	$obj->homepage = $homepage;
	$obj->showemail = (bool)$showemail;
	$obj->admin = (bool)$admin;
	$obj->password = md5($password);
    }
} while (0);

if ($display_form) {

    pageHeader("PEAR: Add an author");

?>
<H1>Add an author</H1>
<FORM ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
<TABLE>
<?php

     if ($errorMsg) {
	 print " <TR>\n";
	 print "  <TD>&nbsp;</TD>\n";
	 print "  <TD><B>$errorMsg</B></TD>\n";
	 print " </TR>\n";
     }

    $width = 60;

    formInputRow("Handle", "handle", $handle, 12);
    formInputRow("Name", "name", $name, $width);
    formInputRow("Email", "email", $email, $width);
    formInputRow("Homepage", "homepage", $homepage, $width);
    formCheckboxRow("Show Email Address?", "showemail", (bool)$showemail);
    formCheckboxRow("Administrator?", "admin", (bool)$admin);
    formPasswordRow("Password", "password", $password);
    formTextareaRow("More info", "info", '', $width, 5);
    formSubmitRow("Add Author");

?></TABLE>
</FORM>
<?php

    if ($jumpto) {
	print "<SCRIPT LANGUAGE=\"JavaScript\">\n<!--\n";
	print "document.forms[0].$jumpto.focus();\n";
	print "\n// -->\n</SCRIPT>\n";
    }
    pageFooter();

}

?>
