<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

/**
 * Details about PEAR accounts
 */

$handle = filter_input(INPUT_GET, 'handle', FILTER_SANITIZE_STRING);
/**
 * Redirect to the accounts list if no handle was specified
 */
if (empty($handle)) {
    localRedirect("/accounts.php");
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow("SELECT * FROM users WHERE registered = 1 ".
                    "AND handle = ?", [$handle]);

if ($row === null) {
    // XXX: make_404();
    header('HTTP/1.0 404 Not Found');
    PEAR::raiseError("No account information found!");
}

$access = $dbh->getCol("SELECT path FROM cvs_acl WHERE username = ?", 0,
                       [$handle]);

response_header($row['name']);

print "<h1>" . $row['name'] . "</h1>\n";

print "<table border=\"0\" cellspacing=\"4\" cellpadding=\"0\">\n";
print "<tr><td valign=\"top\">\n";

$bb = new BorderBox("Account Details", "100%", "", 2, true);
$bb->horizHeadRow("Handle:", $handle);
$bb->horizHeadRow("Name:", $row['name']);
if ($row['showemail'] != 0) {
    $bb->horizHeadRow("Email:", "<a href=\"/account-mail.php?handle=" . $handle . "\">".str_replace(["@", "."], [" at ", " dot "], $row['email'])."</a>");
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

//XXX: Remove entirely?
//$bb->horizHeadRow("Registered since:", $row['created']);
$bb->horizHeadRow("Additional information:", empty($row['userinfo'])?"&nbsp;":$row['userinfo']);
$bb->horizHeadRow("VCS Access:", implode("<br />", $access));

if ($row['wishlist'] != "") {
    $bb->horizHeadRow("Wishlist:", '<a href="/wishlist.php/'.$row['handle'].'">Click here to be redirected.</a>');
}

if ($row['admin'] == 1) {
    $bb->fullRow("$row[name] is a PECL administrator.");
}

$query = "SELECT p.id, p.name, m.role
          FROM packages p, maintains m
          WHERE m.handle = '$handle'
          AND p.id = m.package
          AND p.package_type = 'pecl'
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
