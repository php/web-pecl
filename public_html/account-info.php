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
                    "AND handle = ?", array($handle));

if ($row === null) {
    PEAR::raiseError("No account information found!");
}

$access = $dbh->getCol("SELECT path FROM cvs_acl WHERE username = ?", 0,
                       array($handle));

print "<h1>Information about account \"$handle\"</h1>\n";
print "<a href=\"account-edit.php?handle=$handle\">[Edit]</a>";
print "<br />\n";
print "<br />\n";

print "<table border=\"0\" cellspacing=\"4\" cellpadding=\"0\">\n";
print "<tr><td valign=\"top\">\n";

$bb = new BorderBox("Account Details", "100%", "", 2, true);
$bb->horizHeadRow("Handle:", $handle);
$bb->horizHeadRow("Name:", $row['name']);
if ($row['showemail'] != 0) {
	$bb->horizHeadRow("Email:", $row['email']);
}
if ($row['homepage'] != "") {
	$bb->horizHeadRow("Homepage:",
					  "<a href=\"$row[homepage]\" target=\"_blank\">".
					  "$row[homepage]</a></td>\n");
}

$bb->horizHeadRow("Registered since:", $row['created']);
$bb->horizHeadRow("Additional information:", $row['userinfo']);
$bb->horizHeadRow("CVS Access:", implode("<br />", $access));

if ($row['admin'] == 1) {
	$bb->fullRow("$row[name] is a PEAR administrator.");
}

$query = "SELECT p.id, p.name, m.role
          FROM packages p, maintains m
          WHERE m.handle = '$handle'
          AND p.id = m.package
          ORDER BY p.name";

$sth = $dbh->query($query);

if (DB::IsError($sth)) {
    DB::raiseError("query failed: ".$sth->message);
}

$bb->end();

print "</td><td valign=\"top\">\n";

$bb = new BorderBox("Maintaining These Packages:", "100%", "", 2, true);

if ($sth->numRows() > 0) {
	$bb->headRow("Package Name", "Role");
    while (is_array($row = $sth->fetchRow())) {
		$bb->plainRow("<a href=\"pkginfo.php?pacid=$row[id]\">$row[name]</a>",
					  $row['role']);
    }
}

$bb->end();

print "<br />\n";

display_user_notes($handle, "100%");

print "</td></tr></table>\n";

response_footer();

?>
