<?php

auth_require(true);

require_once "HTML/Form.php";

if (isset($HTTP_GET_VARS['handle'])) {
    $handle = $HTTP_GET_VARS['handle'];
} elseif (isset($HTTP_POST_VARS['handle'])) {
    $handle = $HTTP_POST_VARS['handle'];
} else {
    $handle = "";
}

response_header("Edit Account: $handle");

if (empty($handle) && !isset($HTTP_POST_VARS['command'])) {
    PEAR::raiseError("No valid handle found!");
}

if (!isset($HTTP_POST_VARS['command'])) {
    $HTTP_POST_VARS['command'] = "display";
}

switch ($HTTP_POST_VARS['command']) {

    case "update" : {

        $query = sprintf("UPDATE users SET name = '%s', email = '%s', homepage = '%s', userinfo = '%s', showemail = '%s', admin = '%s'",
                         $HTTP_POST_VARS['name'],
                         $HTTP_POST_VARS['email'],
                         $HTTP_POST_VARS['homepage'],
                         $HTTP_POST_VARS['userinfo'],
                         isset($HTTP_POST_VARS['showemail']) ? 1 : 0,
                         isset($HTTP_POST_VARS['admin']) ? 1 : 0);
        $query .= " WHERE username = '" . $HTTP_POST_VARS['handle'] . "'";
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

    default : {
        $dbh->setFetchmode(DB_FETCHMODE_ASSOC);
        $row = $dbh->getRow("SELECT * FROM users WHERE username = ?",
                            array($handle));
        $cvs_acl_arr = $dbh->getCol("SELECT path FROM cvs_acl ".
                                    "WHERE username = ? AND access = 1", 0,
                                    array($handle));
        $cvs_acl = implode("\n", $cvs_acl_arr);
        if ($row === null) {
            PEAR::raiseError("No account information found!");
        }


        print "<form action=\"" . $HTTP_SERVER_VARS['PHP_SELF'] . "\" method=\"post\">\n";
        print "<input type=\"hidden\" name=\"command\" value=\"update\" />\n";
        print "<input type=\"hidden\" name=\"handle\" value=\"$handle\" />\n";

        print "<h1>Editing account \"$handle\"</h1>\n";

        print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\">\n";
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
        print "  <th bgcolor=\"#CCCCCC\">EMail:</th>\n";
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
        print "  <th bgcolor=\"#CCCCCC\">Show EMail adress:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayCheckbox("showemail", $row['showemail']);
        print "   </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">Administrator:</th>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        HTML_Form::displayCheckbox("admin", $row['admin']);
        print "   </td>\n";
        print " </tr>\n";

        print " <tr>\n";
        print "  <th bgcolor=\"#CCCCCC\">&nbsp;</th>\n";
        print "  <td bgcolor=\"#e8e8e8\"><input type=\"submit\" />&nbsp;<input type=\"reset\" /></td>\n";
        print " </tr>\n";

        print "</table>\n";

        print "</form>\n";

        response_footer();

    }
}
?>
