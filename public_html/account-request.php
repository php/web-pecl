<?php // -*- C++ -*-

require_once "HTML/Form.php";

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<FONT COLOR=\"#CC0000\" SIZE=\"+1\">$msg</FONT><BR>\n";
}

$display_form = true;
$width = 60;
$errorMsg = "";
$jumpto = "handle";

do {
    if (isset($submit)) {
	$required = array("handle" => "your desired username",
			  "name" => "your real name",
			  "email" => "your email address",
			  "purpose" => "the purpose of your PEAR account");
	foreach ($required as $field => $desc) {
	    if (empty($$field)) {
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
	$obj =& new PEAR_User($dbh, $handle);
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
        $md5pw = md5($password);
        $showemail = @(bool)$showemail;
        // hack to temporarily embed the "purpose" in
        // the user's "userinfo" column
        $userinfo = serialize(array($purpose, $moreinfo));
        $set_vars = array('name' => $name,
                          'email' => $email,
                          'homepage' => $homepage,
                          'showemail' => $showemail,
                          'password' => $md5pw,
                          'registered' => 0,
                          'userinfo' => $userinfo);
        $errors = 0;
        foreach ($set_vars as $var => $value) {
            $err = $obj->set($var, $value);
            if (PEAR::isError($err)) {
                print "Failed setting $var: ";
                print $err->getMessage();
                print "<BR>\n";
                $errors++;
            }
        }
        if ($errors > 0) {
            break;
        }

	$msg = "Username:         {$handle}\n".
             "Real Name:        {$name}\n".
	     "Email:            {$email}".
	     (@$showemail ? " (show address)" : " (hide address)") . "\n".
	     "Password (MD5):   {$md5pw}\n\n".
	     "Purpose:\n".
	     "$purpose\n\n".
             "To handle: http://{$SERVER_NAME}/admin.php?acreq={$handle}\n";
        if ($moreinfo) {
            $msg .= "\nMore info:\n$moreinfo\n";
        }
	$xhdr = "From: $name <$email>";
        $subject = "PEAR Account Request";
        $ok = mail_pear_admins($subject, $msg, $xhdr);
	response_header("Account Request Submitted");
        if ($ok) {
	    print "<H2>Account Request Submitted</H2>\n";
	    print "Your account request has been submitted, it will ".
		"be reviewed by a human shortly.  This may take from two ".
                "minutes to several days, depending on how much time people ".
                "have.  ".
		"You will get an email when your account is open, or if ".
		"your request was rejected for some reason.";
	} else {
	    print "<H2>Possible Problem!</H2>\n";
	    print "Your account request has been submitted, but there ".
		"were problems mailing one or more administrators.  ".
		"If you don't hear anything about your account in a few ".
		"days, please drop a mail about it to the <i>pear-dev</i> ".
                "mailing list.";
	}
        print "<br />Click the top-left PEAR logo to go back to the front page.\n";
    }
} while (0);

if ($display_form) {

    response_header("Request Account");

    print "<H1>Request Account</H1>

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

    $form =& new HTML_Form($PHP_SELF, "POST");
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
    $form->addSubmit("submit", "Submit Request");

    $form->display();

    if ($jumpto) {
	print "<SCRIPT LANGUAGE=\"JavaScript\">\n<!--\n";
	print "document.forms[0].$jumpto.focus();\n";
	print "\n// -->\n</SCRIPT>\n";
    }
}

response_footer();

?>
