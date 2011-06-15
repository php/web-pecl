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
include '../include/posttohost.php';

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#CC0000\" size=\"+1\">$msg</font><br />\n";
}

$display_form = true;
$width = 60;
$errorMsg = "";
$jumpto = "handle";

$fields  = array('handle',
                 'firstname',
                 'lastname',
                 'email',
                 'purpose',
                 'password',
                 'password2',
                 'email',
                 'moreinfo',
                 'homepage',
                 'needsvn', 
                 'showemail');

foreach ($fields as $field) {
    $$field = isset($_POST[$field]) ? htmlspecialchars(strip_tags($_POST[$field]),ENT_QUOTES) : null;
}

if (isset($_POST['submit'])) {
    do {

            $required = array("handle"    => "your desired username",
                              "firstname" => "your first name",
                              "lastname"  => "your last name",
                              "email"     => "your email address",
                              "purpose"   => "the purpose of your PECL account");

            $name = $firstname . " " . $lastname;

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

            $md5pw = md5($password);
            $showemail = @(bool)$showemail;

            $needsvn = @(bool)$needsvn;

            // hack to temporarily embed the "purpose" in
            // the user's "userinfo" column
            $userinfo = serialize(array($purpose, $moreinfo));
            $sth = $dbh->prepare("INSERT INTO users 
                    (handle, name, email, password, registered, showemail, homepage, userinfo, from_site, active)
                    VALUES(?, ?, ?, ?, 0, ?, ?, ?, 'pecl', 0)");
            $res = $dbh->execute($sth, array($handle, $name, $email, $md5pw, $showemail, $homepage, $userinfo));

            if (DB::isError($res)) {
                //constraint violation, only email and handle(username) is unique
                if($res->getCode() == -3){
                    display_error("Username or Email already taken");
                }
                else{
                    display_error("$handle: " . DB::errorMessage($res));
                }
                $jumpto = "handle";
                break;
            }

            if ($errors > 0) {
                break;
            }

            /* Now do the SVN stuff */
            if ($needsvn) {
                $error = posttohost(
                    'http://master.php.net/entry/svn-account.php',
                    array(
                        "username" => $handle,
                        "name"     => $name,
                        "email"    => $email,
                        "passwd"   => $password,
                        "note"     => $purpose
                    )
                );

                if ($error) {
                    display_error("Problem submitting the SVN account request: $error");
                    break;
                }
            }

            $display_form = false;

            $msg = "Requested from:   {$_SERVER['REMOTE_ADDR']}\n".
                   "Username:         {$handle}\n".
                   "Real Name:        {$name}\n".
                   "Email:            {$email}".
                   (@$showemail ? " (show address)" : " (hide address)") . "\n".
                   "Need SVN Account: " . (@$needsvn ? "yes" : "no") . "\n".
                   "Purpose:\n".
                   "$purpose\n\n".
                   "To handle: http://" . PEAR_CHANNELNAME . "/admin/?acreq={$handle}\n";

            if ($moreinfo) {
                $msg .= "\nMore info:\n$moreinfo\n";
            }

            $xhdr = "From: $name <$email>";
            $subject = "PECL Account Request: {$handle}";
            $ok = mail("pecl-dev@lists.php.net", $subject, $msg, $xhdr, "-f bounces-ignored@php.net");
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

            print "<br />Click the top-left PECL logo to go back to the front page.\n";

    } while (0);
}
if ($display_form) {

    response_header("Request Account");

    $cs_link        = make_link('http://svn.php.net/viewvc/php/php-src/trunk/CODING_STANDARDS', 'PHP Coding Standards');
    $lic_link_pecl  = make_link('http://www.php.net/license/3_01.txt', 'PHP License 3.01');
    $lic_link_doc   = make_link('http://www.php.net/manual/en/cc.license.php', 'Creative Commons Attribution License');
    $doc_howto_pecl = make_link('http://wiki.php.net/doc/howto/pecldocs', 'PECL Docs Howto');

    print "<h1>Publishing in PECL</h1>

<p>
 A few reasons why you might apply for a PECL account:
</p>
<ul>
 <li>You have written a PHP extension and would like it listed within the PECL directory</li>
 <li>You would like to use php.net for version control and hosting</li>
 <li>You would like to help maintain a current PECL extension</li>
</ul>
<p>

<p>
 You do <b>not</b> need an account if you want to download, install and/or use PECL packages.
</p>

<p>
 Before filling out this form, you must write the public <i>pecl-dev@lists.php.net</i> mailing list and:
</p>
<ul>
 <li>Introduce yourself</li>
 <li>Introduce your new extension or the extension you would like to help maintain</li>
 <li>Link to the code, if applicable</li>
</ul>

<p>
 Also, here is a list of suggestions:
</p>
<ul>
 <li>
  We strongly encourage contributors to choose the $lic_link_pecl for their extensions,
  in order to avoid possible troubles for end-users of the extension. Other solid
  options are BSD and Apache type licenses.
 </li>
 <li>
  We strongly encourage you to use the $cs_link for your code, as it will help
  the QA team (and others) help maintain the extension.
 </li>
 <li>
  We strongly encourage you to commit documentation for the extension, as it will
  make the extension more visible (in the official PHP manual) and also teach
  users how to use it. See the $doc_howto_pecl for more information.
  Submitted documentation will always be under the $lic_link_doc.
 </li>
 <li>
  Note: wrappers for GPL (all versions) or LGPLv3 libraries will not be accepted.
  Wrappers for libraries licensed under LGPLv2 are however allowed while being discouraged.
 </li>
 <li>
  Note: Wrappers for libraries with license fees or closed sources libraries without licenses fees
  are allowed.
 </li>

</ul>

<p>
 And after submitting the form:
</p>
<ul>
 <li>
  If approved, you will also need to <a href='http://php.net/svn-php.php'>apply for a php.net SVN account</a>
  in order to commit the code to the php.net SVN repository. Select 'PECL Group' within that form when applying.
 </li>
</ul>

<p>
 <strong>Please confirm the reason for this PECL account request:</strong>
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

			alert('Reminder: please only request a PECL account if you will maintain a PECL extension, and have followed the guidelines above.');
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
				I have already discussed the topic of maintaining and/or adding a PECL extension on the
				pecl-dev@lists.php.net mailing list, and we determined it's time for me to have a PECL account.
			</label>
		</td>
	</tr>

	<tr>
		<td valign=\"top\"><input type=\"radio\" name=\"reason\" value=\"other\" id=\"reason_other\" onclick=\"reasonClick('other')\" /></td>
		<td>
			<label for=\"reason_other\">I desire this PECL account for another reason.</label>
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
    $bb = new BorderBox("Request a PECL account", "90%", "", 2, true);
    $bb->horizHeadRow("Username:", HTML_Form::returnText("handle", $handle, 12));
    $bb->horizHeadRow("First Name:", HTML_Form::returnText("firstname", $firstname));
    $bb->horizHeadRow("Last Name:", HTML_Form::returnText("lastname", $lastname));
    $bb->horizHeadRow("Password:", HTML_Form::returnPassword("password", null, 10) . "   Again: " . HTML_Form::returnPassword("password2", null, 10));
    $bb->horizHeadRow("Need a SVN account?", HTML_Form::returnCheckbox("needsvn", $needsvn));

    $bb->horizHeadRow("Email address:", HTML_Form::returnText("email", $email));
    $bb->horizHeadRow("Show email address?", HTML_Form::returnCheckbox("showemail", $showemail));
    $bb->horizHeadRow("Homepage", HTML_Form::returnText("homepage", $homepage));
    $bb->horizHeadRow("Purpose of your PECL account<br />(No account is needed for using PECL or PECL packages):", HTML_Form::returnTextarea("purpose", stripslashes($purpose)));
    $bb->horizHeadRow("More relevant information<br />about you (optional):", HTML_Form::returnTextarea("moreinfo", stripslashes($moreinfo)));
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
