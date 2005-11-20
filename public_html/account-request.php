<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "HTML/Form.php";
include 'posttohost.php';

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#CC0000\" size=\"+1\">$msg</font><br />\n";
}

$display_form = true;
$width = 60;
$errorMsg = "";
$jumpto = "handle";
if (isset($_POST['submit'])) {

    $fields  = array(
                    'handle',
                    'firstname',
                    'lastname',
                    'email',
                    'purpose',
                    'password',
                    'password2',
                    'email',
                    'moreinfo',
                    'homepage',
                    'needcvs'
                    );

    foreach ($fields as $field) {
        $$field = isset($_POST[$field]) ? htmlspecialchars(strip_tags($_POST[$field])) : null;
    }

    do {

            $required = array("handle"    => "your desired username",
                              "firstname" => "your first name",
                              "lastname"  => "your last name",
                              "email"     => "your email address",
                              "purpose"   => "the purpose of your PECL account");

            $name = $_POST['firstname'] . " " . $_POST['lastname'];

            foreach ($required as $field => $desc) {
                if (empty($_POST[$field])) {
                    display_error("Please enter $desc!");
                    $jumpto = $field;
                    break 2;
                }
            }

            if (!preg_match(PEAR_COMMON_USER_NAME_REGEX, $handle)) {
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

            PEAR::setErrorHandling(PEAR_ERROR_RETURN);

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

            $needcvs = @(bool)$needcvs;

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

            $msg = "Requested from:   {$_SERVER['REMOTE_ADDR']}\n".
                   "Username:         {$handle}\n".
                   "Real Name:        {$name}\n".
                   "Email:            {$email}".
                   (@$showemail ? " (show address)" : " (hide address)") . "\n".
                   "Need CVS Account: " . (@$needcvs ? "yes" : "no") . "\n".
                   "Purpose:\n".
                   "$purpose\n\n".
                   "To handle: http://" . PEAR_CHANNELNAME . "/admin/?acreq={$handle}\n";

            if ($moreinfo) {
                $msg .= "\nMore info:\n$moreinfo\n";
            }

            $xhdr = "From: $name <$email>";
            $subject = "PECL Account Request: {$handle}";
            $ok = mail("pecl-dev@lists.php.net", $subject, $msg, $xhdr, "-f pear-sys@php.net");
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
                      "days, please drop a mail about it to the <i>pecl-dev</i> ".
                      "mailing list.";
            }

            /* Now do the CVS stuff */
            if ($needcvs) {
                $error = posttohost(
                    'http://master.php.net/entry/cvs-account.php',
                    array(
                        "username" => $handle,
                        "name"     => $name,
                        "email"    => $email,
                        "passwd"   => $password,
                        "note"     => $purpose
                    )
                );

                if ($error) {
                    print "<h2>Problem submitting CVS account request</h2>\n";
                    print "<p class=\"error\">$error</p>\n";
                }
            }

            print "<br />Click the top-left PECL logo to go back to the front page.\n";

    } while (0);
}
if ($display_form) {

    response_header("Request Account");

    $cs_link = make_link('http://cvs.php.net/co.php/php-src/CODING_STANDARDS', 'PHP Coding Standards');
    $lic_link = make_link('http://www.php.net/license/3_0.txt', 'PHP License 3.0');
	$dochowto_link = make_link('http://php.net/manual/howto/index.html', 'Documentation HOWTO');
    $opl_link = make_link('http://php.net/manual/en/opl.license.php', 'Open Publication License');

    print "<h1>Publishing in PECL</h1>

<p>If you have written your own PHP extension and would like to list it in our
directory, or use our hosting facilities, or help look after existing extensions,
you need to apply for a PECL account.</p>

<p>You do <b>not</b> need an account if you want to download, install and/or
use PECL packages.</p>

<p>Before filling out this form, please make yourself known on the
<i>pecl-dev@lists.php.net</i> mailing list. PECL extensions around GPL'ed
libraries will not be accepted, and extensions around LGPL'ed libraries are
strongly discouraged. LGPL'ed libraries as required by extensions may not be
bundled into PECL either.  We strongly encourage contributors to choose the
$lic_link for their extensions to avoid trouble for the end-user of the
extension.  We also encourage you to use the $cs_link for your code, as it will
help the QA team help you to maintain your extension. Although documentation
for extensions is not required, it does make alot of sense if potential users
know how to use your extension, see $dochowto_link for information on how to
document your extension. Sumbitted documentation will always be under the
$opl_link.</p>

<p>Bogus, incomplete or incorrect requests will be summarily denied.</p>

<p>
	<strong>Confirm your reason for a PECL account:</strong>
</p>

<script language=\"JavaScript\" type=\"text/javascript\" defer=\"defer\">
<!--
	function reasonClick(option)
	{
		if (option == 'pkg') {
			enableForm(true);

			// Lose border
			if (document.getElementById) {
				document.getElementById('reason_table').style.border = '2px dashed green';
			}
		} else {
			// Gain border
			if (document.getElementById) {
				document.getElementById('reason_table').style.border = '2px dashed red';
			}

			alert('You do not need an account - PLEASE DO NOT SUBMIT THE FORM');
			enableForm(false);
		}
	}

	function enableForm(disabled)
	{
		for (var i=0; i<document.forms['request_form'].elements.length; i++) {
			document.forms['request_form'].elements[i].disabled = !disabled;
			//document.forms['request_form'].elements[i].style.backgroundColor = '#c0c0c0';
		}
	}

	enableForm(false);
//-->
</script>

<table border=\"0\" style=\"border: 2px #ff0000 dashed; padding: 0px\" id=\"reason_table\">
	<tr>
		<td valign=\"top\"><input type=\"radio\" name=\"reason\" value=\"pkg\" id=\"reason_pkg\" onclick=\"reasonClick('pkg')\" /></td>
		<td>
			<label for=\"reason_pkg\">
				You have announced a new PECL package to the PECL-Dev mailing list, it's name has been OKed, and you
				wish to register/upload a release to the pecl website.
			</label>
		</td>
	</tr>

	<tr>
		<td valign=\"top\"><input type=\"radio\" name=\"reason\" value=\"other\" id=\"reason_other\" onclick=\"reasonClick('other')\" /></td>
		<td>
			<label for=\"reason_other\">Other reason</label>
		</td>
	</tr>
</table>
";

    if (isset($errorMsg)) {
        print "<table>\n";
        print " <tr>\n";
        print "  <td>&nbsp;</td>\n";
        print "  <td><b>$errorMsg</b></td>\n";
        print " </tr>\n";
        print "</table>\n";
    }

    print "<form action=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\" method=\"post\" name=\"request_form\">\n";
    $bb = new BorderBox("Request account", "90%", "", 2, true);
    $bb->horizHeadRow("Username:", HTML_Form::returnText("handle", @$_POST['handle'], 12));
    $bb->horizHeadRow("First Name:", HTML_Form::returnText("firstname", @$_POST['firstname']));
    $bb->horizHeadRow("Last Name:", HTML_Form::returnText("lastname", @$_POST['lastname']));
    $bb->horizHeadRow("Password:", HTML_Form::returnPassword("password", null, 10) . "   Again: " . HTML_Form::returnPassword("password2", null, 10));
    $bb->horizHeadRow("Need a CVS account?", HTML_Form::returnCheckbox("needcvs", @$_POST['needcvs']));

    $bb->horizHeadRow("Email address:", HTML_Form::returnText("email", @$_POST['email']));
    $bb->horizHeadRow("Show email address?", HTML_Form::returnCheckbox("showemail", @$_POST['showemail']));
    $bb->horizHeadRow("Homepage", HTML_Form::returnText("homepage", @$_POST['homepage']));
    $bb->horizHeadRow("Purpose of your PECL account<br />(No account is needed for using PECL or PECL packages):", HTML_Form::returnTextarea("purpose", stripslashes(@$_POST['purpose'])));
    $bb->horizHeadRow("More relevant information<br />about you (optional):", HTML_Form::returnTextarea("moreinfo", stripslashes(@$_POST['moreinfo'])));
    $bb->horizHeadRow("Requested from IP address:", $_SERVER['REMOTE_ADDR']);
    $bb->horizHeadRow("<input type=\"submit\" name=\"submit\" />&nbsp;<input type=\"reset\" />");
    $bb->end();
    print "</form>";

    if ($jumpto) {
        print "<script language=\"JavaScript\" type=\"text/javascript\">\n<!--\n";
        print "if (!document.forms[1].$jumpto.disabled) document.forms[1].$jumpto.focus();\n";
        print "\n// -->\n</script>\n";
    }
}

response_footer();

?>
