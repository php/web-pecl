<?php // -*- C++ -*-

require_once "HTML/Form.php";

$display_form = true;

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<FONT COLOR=\"#CC0000\" SIZE=\"+1\">$msg</FONT><BR>\n";
}

$jumpto = "handle";

do {
    if (isset($submit)) {
	$required = array("handle" => "your desired username",
			  "name" => "your real name",
			  "email" => "your email address",
			  "purpose" => "the purpose of your PEAR account");
	foreach ($required as $field => $desc) {
	    if (!$$field) {
		display_error("Please enter $desc!");
		$jumpto = $field;
		break 2;
	    }
	}

	if (!preg_match("/^[a-z][a-z0-9]+$/i", $handle)) {
	    display_error("Username must start with a letter and contain ".
			 "only letters and digits.");
	    break;
	}

	if ($password != $password2) {
	    display_error("Passwords did not match");
	    $password = $password2 = "";
	    $jumpto = "password";
	    break;
	}

	if (!$password) {
	    display_error("Empty passwords not allowed");
	    $jumpto = "password";
	    break;
	}

	$handle = strtolower($handle);
	$obj = new PEAR_User(&$dbh, $handle);
	if (isset($obj->created)) {
	    display_error("Sorry, that username is already taken");
	    $jumpto = "handle";
	    break;
	}
	$err = $obj->insert($handle);
	if (DB::isError($err)) {
	    display_error("$handle: " . DB::errorMessage($err));
	    $jumpto = "handle";
	    break;
	}

	$display_form = false;
	$obj->name = $name;
	$obj->email = $email;
	$obj->homepage = $homepage;
	$obj->showemail = (bool)@$showemail;
	$obj->password = md5($password);
	$obj->registered = false;

	$admins = $dbh->getCol("SELECT email FROM users WHERE admin = 1");
	$oks = 0;
	$msg = "Username:         {$obj->handle}\n".
	     "Email:            {$obj->email}".
	     ($obj->showemail ? " (show address)" : " (hide address)") . "\n".
	     "Password (MD5):   {$obj->password}\n\n".
	     "Purpose:\n".
	     "$purpose\n\n".
	     "More info:\n".
	     "$moreinfo\n";
	$xhdr = "From: PEAR Web Site <pear-dev@lists.php.net>";
	foreach ($admins as $email) {
	    $oks += mail($email, "PEAR Account Application", $msg, $xhdr);
	}
	response_header("Account Application Submitted");
	if ($oks != sizeof($admins)) {
	    print "<H2>Possible Problem!</H2>\n";
	    print "Your account application has been submitted, but there ".
		"was problems mailing one or more administrators.  ".
		"If you don't hear anything about your account in a few ".
		"days, drop a mail about it to the <i>pear-dev</i> mailing ".
		"list.";
	} else {
	    print "<H2>Account Application Submitted</H2>\n";
	    print "Your account application has been submitted, it will ".
		"be reviewed by a human.  This may take from two minutes ".
		"to several days, depending on how much time people have.  ".
		"You will get an email when your account is open.";
	}
    }
} while (0);

if ($display_form) {

    response_header("Apply for Account");

    print "<H1>Apply for Account</H1>

Please note that you <b>not</b> need an account for
<b>downloading</b>.<br> You only need an account if you have a package
you would like to release through PEAR.

";

    if (isset($errorMsg)) {
	print "<TABLE>\n";
	print " <TR>\n";
	print "  <TD>&nbsp;</TD>\n";
	print "  <TD><B>$errorMsg</B></TD>\n";
	print " </TR>\n";
	print "</TABLE>\n";
    }

    $width = 60;
    $form = new HTML_Form($PHP_SELF, "POST");
    $form->addText("handle", "Username", null, 12);
    $form->addText("name", "Real Name", null, $width);
    $form->addPassword("password", "Password", null, 10);
    $form->addText("email", "Email Address", null, $width);
    $form->addCheckbox("showemail", "Show Email Address?", null);
    $form->addText("homepage", "Homepage", null, $width);
    $form->addTextarea("purpose",
		       "Purpose of your PEAR account",
		       null, $width, 3);
    $form->addTextarea("moreinfo",
		       "More relevant information about you (optional)",
		       null, $width, 3);
    $form->addSubmit("submit", "Submit Application");

    $form->display();

    if ($jumpto) {
	print "<SCRIPT LANGUAGE=\"JavaScript\">\n<!--\n";
	print "document.forms[0].$jumpto.focus();\n";
	print "\n// -->\n</SCRIPT>\n";
    }
}

response_footer();

?>
