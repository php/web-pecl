<?php

response_header("PEAR: Authors");

print "<H1>Authors</H1>\n";

$sth = $dbh->query('SELECT handle,name,email,homepage,showemail '.
		   'FROM authors WHERE registered = 1');
if (DB::isError($sth)) {
    die("query failed: ".DB::errorMessage($dbh)."<BR>\n");
}

print "<A HREF=\"add-author.php\">NEW</A> \n";

print "<P>\n";

print "<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=3>\n";
print " <TR BGCOLOR=\"$pear_green\">\n";
print "  <TH><FONT COLOR=\"#ffffff\">Handle</FONT></TH>\n";
print "  <TH><FONT COLOR=\"#ffffff\">Name</FONT></TH>\n";
print "  <TH><FONT COLOR=\"#ffffff\">Email</FONT></TH>\n";
print "  <TH><FONT COLOR=\"#ffffff\">Homepage</FONT></TH>\n";
print "  <TH><FONT COLOR=\"#ffffff\">Commands</FONT></TH>\n";
print " </TR>\n";

$rowno = 0;
while (is_array($row = $sth->fetchRow(DB_GETMODE_ASSOC))) {
    extract($row);
    if (++$rowno % 2) {
	print " <TR>\n";
    } else {
	print " <TR BGCOLOR=\"#e8e8e8\">\n";
    }
    print "  <TD>$handle</TD>\n";
    print "  <TD>$name</TD>\n";
    if ($showemail) {
	print "  <TD><A HREF=\"mailto:$email\">$email</A></TD>\n";
    } else {
	print "  <TD>(not shown)</TD>\n";
    }
    print "  <TD><A HREF=\"$homepage\">$homepage</A></TD>\n";
    print "  <TD><A HREF=\"edit-author.php\">[edit]</A></TD>\n";
    print " </TR>\n";
}

print "</TABLE>\n";

response_footer();

?>
