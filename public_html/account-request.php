<?php // -*- C++ -*-

require_once "HTML/Form.php";

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#CC0000\" size=\"+1\">$msg</font><br />\n";
}

$display_form = true;
$width = 60;
$errorMsg = "";
$jumpto = "handle";

do {
    if (isset($submit)) {
	    $required = array("handle"  => "your desired username",
                          "name"    => "your real name",
			              "email"   => "your email address",
			              "purpose" => "the purpose of your PEAR account");

    	foreach ($required as $field => $desc) {
	        if (empty($$field)) {
		        display_error("Please enter $desc!");
		        $jumpto = $field;
		        break 2;
	        }
	    }

	    if (!preg_match("/^[a-z][a-z0-9]+$/i", $handle)) {
	        display_error("Username must start with a letter and contain only letters and digits.");
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
                print "<br />\n";
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
        $subject = "PEAR Account Request: {$handle}";
        $ok = mail_pear_admins($subject, $msg, $xhdr);
        response_header("Account Request Submitted");
                
        if ($ok) {
            print "<h2>Account Request Submitted</h2>\n";
            print "Your account request has been submitted, it will ".
                  "be reviewed by a human shortly.  This may take from two ".
                  "minutes to several days, depending on how much time people ".
                  "have.  ".
                  "You will get an email when your account is open, or if ".
                  "your request was rejected for some reason.";
        } else {
            print "<h2>Possible Problem!</h2>\n";
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

    print "<h1>Request Account</h1>

Please note that you <b>not</b> need an account for
<b>downloading</b>.<br /> You only need an account if you have a package
you would like to release through PEAR.

";

    if (isset($errorMsg)) {
	    print "<table>\n";
	    print " <tr>\n";
	    print "  <td>&nbsp;</td>\n";
	    print "  <td><b>$errorMsg</b></td>\n";
	    print " </tr>\n";
	    print "</table>\n";
    }
    
    print "<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\">\n";
    print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\">\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">Username</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayText("handle", @$HTTP_POST_VARS['handle'], 12);
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">Real Name</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayText("name", @$HTTP_POST_VARS['name']);
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">Password</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayPassword("password", null, 10);
    print "   Again: ";
    HTML_Form::displayPassword("password2", null, 10);
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">Email Address</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayText("email", @$HTTP_POST_VARS['email']);
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">Show Email Address?</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayCheckbox("showemail", @$HTTP_POST_VARS['showemail']);
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">Homepage</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayText("homepage", @$HTTP_POST_VARS['homepage']);
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">Purpose of your PEAR account</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayTextarea("purpose", stripslashes(@$HTTP_POST_VARS['purpose']));
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">More relevant information about you (optional)</th>\n";
    print "  <td bgcolor=\"#e8e8e8\">";
    HTML_Form::displayTextarea("moreinfo", stripslashes(@$HTTP_POST_VARS['moreinfo']));
    print "  </td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "  <th bgcolor=\"#cccccc\">&nbsp;</th>\n";
    print "  <td bgcolor=\"#e8e8e8\"><input type=\"submit\" name=\"submit\" />&nbsp;<input type=\"reset\" /></td>\n";
    print "</tr>\n";

    print "</table>\n";
    
    if ($jumpto) {
	    print "<script language=\"JavaScript\">\n<!--\n";
	    print "document.forms[1].$jumpto.focus();\n";
	    print "\n// -->\n</script>\n";
    }
}

response_footer();

?>
