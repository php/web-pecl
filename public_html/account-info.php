<?php
/**
 * Details about PEAR accounts
 *
 * $Id$
 */

/**
 * Redirect to the accounts list if no ID was specified
 */
if (!isset($HTTP_GET_VARS['handle'])) {
    localRedirect("/accounts.php");
} else {
    $handle = $HTTP_GET_VARS['handle'];
}

response_header("Author information");

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow("SELECT * FROM users WHERE registered = 1 ".
                    "AND username = ?", array($handle));

if ($row === null) {
    PEAR::raiseError("No account information found!");
}

$access = $dbh->getCol("SELECT path FROM cvs_acl WHERE username = ?", 0,
                       array($handle));

print "<h1>Information about account \"$handle\"</h1>\n";
print "<a href=\"account-edit.php?handle=$handle\">[Edit]</a>";
print "<br />\n";
print "<br />\n";

print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\">\n";
print " <tr>\n";
print "  <th bgcolor=\"#CCCCCC\">Handle:</th>\n";
print "  <td bgcolor=\"#e8e8e8\">$handle</td>\n";
print " </tr>\n";

print " <tr>\n";
print "  <th bgcolor=\"#CCCCCC\">Name:</th>\n";
print "  <td bgcolor=\"#e8e8e8\">".$row['name']."</td>\n";
print " </tr>\n";

if ($row['showemail'] != 0) {
    print " <tr>\n";
    print "  <th bgcolor=\"#CCCCCC\">EMail:</th>\n";
    print "  <td bgcolor=\"#e8e8e8\"><a href=\"mailto:".$row['email']."\">".$row['email']."</a></td>\n";
    print " </tr>\n";
}

if ($row['homepage'] != "") {
    print " <tr>\n";
    print "  <th bgcolor=\"#CCCCCC\">Homepage:</th>\n";
    print "  <td bgcolor=\"#e8e8e8\"><a href=\"".$row['homepage']."\" target=\"_blank\">".$row['homepage']."</a></td>\n";
    print " </tr>\n";
}

print " <tr>\n";
print "  <th bgcolor=\"#CCCCCC\">Registered since:</th>\n";
print "  <td bgcolor=\"#e8e8e8\">".$row['created']."</td>\n";
print " </tr>\n";

print " <tr>\n";
print "  <th bgcolor=\"#CCCCCC\">Additional information:</th>\n";
print "  <td bgcolor=\"#e8e8e8\">".$row['userinfo']."&nbsp;</td>\n";
print " </tr>\n";

print " <tr>\n";
print "  <th valign=\"top\" bgcolor=\"#cccccc\">CVS Access:</th>\n";
print "  <td bgcolor=\"#e8e8e8\">".implode("<br />", $access)."</td>\n";
print " </tr>\n";

if ($row['admin'] == 1) {
    print " <tr>\n";
    print "  <td colspan=\"2\" bgcolor=\"#e8e8e8\">".$row['name']." is a PEAR administrator.</td>\n";
    print " </tr>\n";
}
print "</table>\n";

$query = "SELECT p.id, p.name, m.role
          FROM packages p, maintains m
          WHERE m.handle = '$handle'
          AND p.id = m.package
          ORDER BY p.name";

$sth = $dbh->query($query);

if (DB::IsError($sth)) {
    DB::raiseError("query failed: ".$sth->message);
}

if ($sth->numRows() > 0) {
    print "<br /><br />\n";
    print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\">\n";
    print " <tr>\n";
    print "  <th colspan=\"2\" bgcolor=\"#e8e8e8\">Maintaining the following packages:</td>";
    print " </tr>\n<tr><th bgcolor=\"#e8e8e8\">Package Name</th>";
    print "<th bgcolor=\"#e8e8e8\">Role</th></tr>\n";

    while (is_array($row = $sth->fetchRow())) {
        print " <tr>\n";
        print "  <td bgcolor=\"#e8e8e8\">";
        print "  <a href=\"pkginfo.php?pacid={$row['id']}\">{$row['name']}</a>";
        print "  </td>\n";
        print "  <td bgcolor=\"#e8e8e8\" align=\"center\">{$row['role']}";
        print "  </td>\n";
        print " </tr>\n";
    }

    print "</table>";
}
response_footer();

?>
