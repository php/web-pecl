<?php

pageHeader("PEAR: Authors");

print "<H1>Authors</H1>\n";

$sth = $dbh->query('SELECT handle,name,email,homepage,showemail '.
		   'FROM authors WHERE registered = 1');
if (DB::isError($sth)) {
    die("query failed: ".DB::errorMessage($dbh)."<BR>\n");
}
print "<TABLE BORDER=1>\n";
print " <TR>\n";
print "  <TH>Handle</TH>\n";
print "  <TH>Name</TH>\n";
print "  <TH>Email</TH>\n";
print "  <TH>Homepage</TH>\n";
print " </TR>\n";

while (is_array($row = $sth->fetchRow(DB_GETMODE_ASSOC))) {
    extract($row);
    print " <TR>\n";
    print "  <TD>$handle</TD>\n";
    print "  <TD>$name</TD>\n";
    if ($showemail) {
	print "  <TD><A HREF=\"mailto:$email\">$email</A></TD>\n";
    } else {
	print "  <TD>(not shown)</TD>\n";
    }
    print "  <TD><A HREF=\"$homepage\">$homepage</A></TD>\n";
    print " </TR>\n";
}

print "</TABLE>\n";

pageFooter();

?>
