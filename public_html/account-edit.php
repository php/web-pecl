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

auth_require();

require_once "HTML/Form.php";

if (isset($HTTP_GET_VARS['handle'])) {
    $handle = $HTTP_GET_VARS['handle'];
} elseif (isset($HTTP_POST_VARS['handle'])) {
    $handle = $HTTP_POST_VARS['handle'];
} else {
    $handle = "";
}

ob_start();
response_header("Edit Account: $handle");

$admin = user::isAdmin($_COOKIE['PEAR_USER']);
$user = ($_COOKIE['PEAR_USER'] === $handle);

if (!$admin && !$user) {
    PEAR::raiseError("Only the user himself or PEAR administrators can edit the account information.");
    response_footer();
    exit();
}

if (empty($handle) && !isset($HTTP_POST_VARS['command'])) {
    PEAR::raiseError("No valid handle found!");
}

if (!isset($HTTP_POST_VARS['command'])) {
    $HTTP_POST_VARS['command'] = "display";
}

switch ($HTTP_POST_VARS['command']) {

    case "update" : {

        $query = sprintf("UPDATE users SET name = '%s', email = '%s', homepage = '%s', userinfo = '%s', wishlist = '%s', showemail = '%s', admin = '%s'",
                         $HTTP_POST_VARS['name'],
                         $HTTP_POST_VARS['email'],
                         $HTTP_POST_VARS['homepage'],
                         addslashes($HTTP_POST_VARS['userinfo']),
                         $HTTP_POST_VARS['wishlist'],
                         isset($HTTP_POST_VARS['showemail']) ? 1 : 0,
                         $admin && isset($HTTP_POST_VARS['admin']) ? 1 : 0);
        $query .= " WHERE handle = '" . $HTTP_POST_VARS['handle'] . "'";
        $sth = $dbh->query($query);

        $old_acl = $dbh->getCol("SELECT path FROM cvs_acl ".
                                "WHERE username = ? AND access = 1", 0,
                                array($handle));
        $new_acl = preg_split("/[\r\n]+/", trim($cvs_acl));
        $lost_entries = array_diff($old_acl, $new_acl);
        $new_entries = array_diff($new_acl, $old_acl);
        if (sizeof($lost_entries) > 0) {
            $sth = $dbh->prepare("DELETE FROM cvs_acl WHERE username = ? ".
                                 "AND path = ?");
            foreach ($lost_entries as $ent) {
                $del = $dbh->affectedRows();
                print "Removing CVS access to $ent for $handle...<br />\n";
                $dbh->execute($sth, array($handle, $ent));
            }
        }
        if (sizeof($new_entries) > 0) {
            $sth = $dbh->prepare("INSERT INTO cvs_acl (username,path,access) ".
                                 "VALUES(?,?,?)");
            foreach ($new_entries as $ent) {
                print "Adding CVS access to $ent for $handle...<br />\n";
                $dbh->execute($sth, array($handle, $ent, 1));
            }
        }

        print "<i>The update has been executed successfully.</i>";

        print "<br /><br />";
        print "<a href=\"account-info.php?handle=$handle\">";
        print "[Back to info page]</a>";

        $handle = $HTTP_POST_VARS['handle'];

        response_footer();
        return;
    }

    case "change_password" : {
        $user = &new PEAR_User($dbh, $handle);
        $execute = true;

        if (empty($_POST['password_old']) || empty($_POST['password1']) ||
            empty($_POST['password2'])) {

            PEAR::raiseError("Please fill out all password fields.");
            $execute = false;
        }

        if ($user->get("password") != md5($_POST['password_old'])) {
            PEAR::raiseError("You provided a wrong old password.");
            $execute = false;
        }

        if ($_POST['password1'] != $_POST['password2']) {
            PEAR::raiseError("The new passwords do not match.");
            $execute = false;
        }

        if ($execute === true) {
            $user->set("password", md5($_POST['password1']));
            if ($user->store()) {
                auth_logout();
                localRedirect("/login.php");
            }
        }
    }

    default : {
        $dbh->setFetchmode(DB_FETCHMODE_ASSOC);
        $row = $dbh->getRow("SELECT * FROM users WHERE handle = ?",
                            array($handle));
        $cvs_acl_arr = $dbh->getCol("SELECT path FROM cvs_acl ".
                                    "WHERE username = ? AND access = 1", 0,
                                    array($handle));
        $cvs_acl = implode("\n", $cvs_acl_arr);
        if ($row === null) {
            PEAR::raiseError("No account information found!");
        }


        print "<form action=\"" . $HTTP_SERVER_VARS['PHP_SELF'] . "?handle=" . $handle . "\" method=\"post\">\n";
        print "<input type=\"hidden\" name=\"command\" value=\"update\" />\n";
        print "<input type=\"hidden\" name=\"handle\" value=\"$handle\" />\n";

        print "<h1>Editing account \"$handle\"</h1>\n";

        print "<ul><li><a href=\"#password\">Manage your password</a></li></ul>";

        $bb = new BorderBox("Edit your information");

        print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\" width=\"100%\">\n";
        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Handle:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">$handle</td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Name:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayText("name", $row['name']);
        print "  </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Email:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayText("email", $row['email']);
        print "  </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Homepage:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayText("homepage", $row['homepage']);
        print "   </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Additional user information:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayTextarea("userinfo", $row['userinfo']);
        print "   </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">CVS Access:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayTextarea("cvs_acl", $cvs_acl);
        print "   </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">URL to wishlist:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayText("wishlist", $row['wishlist']);
        print "   </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Show Email address:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayCheckbox("showemail", $row['showemail']);
        print "   </td>\n";
        print " </tr>\n";
        
        if ($admin)    { /* show the admin checkbox only when the visitor is admin */
            print " <tr>\n";
            print "  <th bgcolor=\"#CCCCCC\">Administrator:</th>\n";
            print "  <td bgcolor=\"#e8e8e8\">";
            HTML_Form::displayCheckbox("admin", $row['admin']);
            print "   </td>\n";
            print " </tr>\n";
        }  

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">&nbsp;</th>\n";
        print "  <td bgcolor=\"#e8e8e8\"><input type=\"submit\" value=\"Submit\" name=\"submit\" />&nbsp;<input type=\"reset\" name=\"reset\" value=\"Reset\" /></td>\n";
        print " </tr>\n";

        print "</table>\n";

        print "</form>\n";

        $bb->end();

        print "<br /><br /><a name=\"password\" />";

        $bb = new BorderBox("Change password");

        print "<form method=\"post\" action=\"" . $_SERVER['PHP_SELF'] . "\">\n";
        print "<input type=\"hidden\" name=\"handle\" value=\"" . $handle . "\" />\n";
        print "<input type=\"hidden\" name=\"command\" value=\"change_password\" />\n";
        print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\" width=\"100%\">\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Old Password:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayPassword("password_old", "", 25);
        print "  </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">New Password:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayPassword("password1", "", 25);
        print " repeat ";
        HTML_Form::displayPassword("password2", "", 25);
        print "  </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">&nbsp;</th>\n";
        print "  <td bgcolor=\"#e8e8e8\"><input type=\"submit\" value=\"Submit\" name=\"submit\" />";
        print "  (You will be redirected to a login form where you have";
        print "  to enter your new password.)";
        print "  </td>\n";
        print " </tr>\n";

        print "</table>\n";

        $bb->end();

        response_footer();

    }
}

ob_end_flush();
?>
