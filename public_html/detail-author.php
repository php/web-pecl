<?php
/**
 * Details about PEAR authors
 *
 * $Id$
 */

/**
 * Redirect to the authors list if no ID was specified
 */
if (!isset($HTTP_GET_VARS['handle'])) {
    localRedirect("/authors.php");
} else {
    $handle = $HTTP_GET_VARS['handle'];
}

response_header("Author information");

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow("SELECT * FROM users WHERE registered = 1 AND handle = ?",
					array($handle));

if ($row === null) {
    PEAR::raiseError("No author information found!");
}

print "<H1>Information about author \"".$handle."\"</H1>\n";
print "<P>\n";

print "<TABLE BORDER=\"0\" CELLSPACING=\"1\" CELLPADDING=\"5\">\n";
print " <TR>\n";
print "  <TH BGCOLOR=\"#CCCCCC\">Handle:</TH>\n";
print "  <TD BGCOLOR=\"#e8e8e8\">".$handle."</TD>\n";
print " </TR>\n";

print " <TR>\n";
print "  <TH BGCOLOR=\"#CCCCCC\">Name:</TH>\n";
print "  <TD BGCOLOR=\"#e8e8e8\">".$row['name']."</TD>\n";
print " </TR>\n";

if ($row['showemail'] != 0) {
    print " <TR>\n";
    print "  <TH BGCOLOR=\"#CCCCCC\">EMail:</TH>\n";
    print "  <TD BGCOLOR=\"#e8e8e8\"><A HREF=\"mailto:".$row['email']."\">".$row['email']."</A></TD>\n";
    print " </TR>\n";
}

if ($row['homepage'] != "") {
    print " <TR>\n";
    print "  <TH BGCOLOR=\"#CCCCCC\">Homepage:</TH>\n";
    print "  <TD BGCOLOR=\"#e8e8e8\"><A HREF=\"".$row['homepage']."\" TARGET=\"_blank\">".$row['homepage']."</A></TD>\n";
    print " </TR>\n";
}

print " <TR>\n";
print "  <TH BGCOLOR=\"#CCCCCC\">Registered since:</TH>\n";
print "  <TD BGCOLOR=\"#e8e8e8\">".$row['created']."</TD>\n";
print " </TR>\n";

print " <TR>\n";
print "  <TH BGCOLOR=\"#CCCCCC\">Additional information:</TH>\n";
print "  <TD BGCOLOR=\"#e8e8e8\">".$row['userinfo']."&nbsp;</TD>\n";
print " </TR>\n";

if ($row['admin'] == 1) {
    print " <TR>\n";
    print "  <TD COLSPAN=\"2\" BGCOLOR=\"#e8e8e8\">".$row['name']." is a PEAR administrator.</TD>\n";
    print " </TR>\n";
}
print "</TABLE>\n";

$query = "SELECT p.id, p.name, m.role
          FROM packages p, maintains m
          WHERE m.handle = '$handle'
          AND p.id = m.package";

$sth = $dbh->query($query);

if (DB::IsError($sth)) {
    DB::raiseError("query failed: ".$sth->message);
}

if ($sth->numRows() > 0) {
    print "<BR><BR>\n";
    print "<TABLE BORDER=\"0\" CELLSPACING=\"1\" CELLPADDING=\"5\">\n";
    print " <TR>\n";
    print "  <TH colspan=\"2\" BGCOLOR=\"#e8e8e8\">The author is maintaing the following packages:</TD>";
    print " </TR>\n<TR><TH BGCOLOR=\"#e8e8e8\">Package Name</TH>";
    print "<TH BGCOLOR=\"#e8e8e8\">Role</TH></TR>\n";

    while (is_array($row = $sth->fetchRow())) {
        print " <TR>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">";
        print "  <a href=\"pkginfo.php?pacid={$row['id']}\">{$row['name']}</a>";
        print "  </TD>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\" align=\"center\">{$row['role']}";
        print "  </TD>\n";
        print " </TR>\n";
    }

    print "</TABLE>";
}
response_footer();

?>
