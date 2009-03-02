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

/**
 * Details about PEAR accounts
 */
require_once "Damblan/URL.php";
$site = new Damblan_URL();

$params = array("handle" => "");
$site->getElements($params);

$handle = $params['handle'];

/**
 * Redirect to the accounts list if no handle was specified
 */
if (empty($handle)) {
    localRedirect("/accounts.php");
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow("SELECT * FROM users WHERE registered = 1 ".
                    "AND handle = ?", array($handle));

if ($row === null) {
    // XXX: make_404();
    PEAR::raiseError("No account information found!");
}

$access = $dbh->getCol("SELECT path FROM cvs_acl WHERE username = ?", 0,
                       array($handle));

response_header($row['name']);

print "<h1>" . $row['name'] . "</h1>\n";

print "<table border=\"0\" cellspacing=\"4\" cellpadding=\"0\">\n";
print "<tr><td valign=\"top\">\n";

$bb = new BorderBox("Account Details", "100%", "", 2, true);
$bb->horizHeadRow("Handle:", $handle);
$bb->horizHeadRow("Name:", $row['name']);
if ($row['showemail'] != 0) {
    $bb->horizHeadRow("Email:", "<a href=\"/account-mail.php?handle=" . $handle . "\">".str_replace(array("@", "."), array(" at ", " dot "), $row['email'])."</a>");
}
if ($row['homepage'] != "") {
	
	$url = parse_url($row['homepage']);
	if (empty($url['scheme'])) {
		$row['homepage'] = 'http://' . $row['homepage'];
	}
	
	$bb->horizHeadRow("Homepage:",
					  "<a href=\"$row[homepage]\" target=\"_blank\">".
					  "$row[homepage]</a></td>\n");
}

$bb->horizHeadRow("Registered since:", $row['created']);
$bb->horizHeadRow("Additional information:", empty($row['userinfo'])?"&nbsp;":$row['userinfo']);
$bb->horizHeadRow("CVS Access:", implode("<br />", $access));

if ($row['wishlist'] != "") {
    $bb->horizHeadRow("Wishlist:", make_link("/wishlist.php/" . $row['handle'], "Click here to be redirected."));
}

if ($row['admin'] == 1) {
	$bb->fullRow("$row[name] is a PECL administrator.");
}

$query = "SELECT p.id, p.name, m.role
          FROM packages p, maintains m
          WHERE m.handle = '$handle'
          AND p.id = m.package
          ORDER BY p.name";

$sth = $dbh->query($query);

$bb->end();

print "</td><td valign=\"top\">\n";

$bb = new BorderBox("Maintaining These Packages:", "100%", "", 2, true);

if ($sth->numRows() > 0) {
	$bb->headRow("Package Name", "Role");
    while (is_array($row = $sth->fetchRow())) {
		$bb->plainRow("<a href=\"/package/" . $row['name'] . "\">" . $row['name'] . "</a>",
					  $row['role']);
    }
}

$bb->end();

print "<br />\n";

display_user_notes($handle, "100%");

print "<br /><a href=\"/account-edit.php?handle=$handle\">". make_image("edit.gif", "Edit") . "</a>";

print "</td></tr></table>\n";

response_footer();

?>
