<?php

response_header("PEAR: Authors");

print "<H1>Authors</H1>\n";

$sth = $dbh->query('SELECT handle,name,email,homepage,showemail '.
                   'FROM users WHERE registered = 1');
if (DB::isError($sth)) {
    die("query failed: ".DB::errorMessage($dbh)."<BR>\n");
}

print "<P>\n";

print "<TABLE BORDER=\"0\" CELLSPACING=\"1\" CELLPADDING=\"5\">\n";
print " <TR bgcolor=\"#CCCCCC\">\n";
print "  <TH>Handle</TH>\n";
print "  <TH>Name</TH>\n";
print "  <TH>Email</TH>\n";
print "  <TH>Homepage</TH>\n";
print "  <TH>Commands</TH>\n";
print " </TR>\n";

$rowno = 0;
while (is_array($row = $sth->fetchRow(DB_FETCHMODE_ASSOC))) {
    extract($row);
    if (++$rowno % 2) {
        print " <TR bgcolor=\"#e8e8e8\">\n";
    } else {
        print " <TR BGCOLOR=\"#e0e0e0\">\n";
    }
    print "  <TD>$handle</TD>\n";
    print "  <TD>$name</TD>\n";

    if ($showemail) {
        print "  <TD><A HREF=\"mailto:$email\">$email</A></TD>\n";
    } else {
        print "  <TD>(not shown)</TD>\n";
    }
    if (!empty($homepage)) {
        print "<TD><A HREF=\"$homepage\">$homepage</A><TD>";
    } else {
        print '<TD>&nbsp;<TD>';
    }
    print "\n  <TD><A HREF=\"edit-author.php?handle=".$row['handle']."\">[edit]</A>&nbsp;
                 <A HREF=\"detail-author.php?handle=".$row['handle']."\">[details]</A></TD>\n";
    print " </TR>\n";
}

print "</TABLE>\n";

response_footer();

?>
