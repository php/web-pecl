<?php

response_header("PEAR: List packages");

$dbh = DB::Connect("mysql://pear@localhost/pear");
if (DB::isError($dbh)) {
    die("DB::Factory failed: ".DB::errorMessage($dbh)."<BR>\n");
}

$sth = $dbh->query("SELECT * FROM authors");
if (DB::isError($sth)) {
    die("query failed: ".DB::errorMessage($dbh)."<BR>\n");
}
print "<TABLE BORDER=1>\n";
print " <TR>\n";
print "  <TH>Handle</TH>\n";
print "  <TH>Password</TH>\n";
print "  <TH>Name</TH>\n";
print "  <TH>Email</TH>\n";
print "  <TH>Homepage</TH>\n";
print "  <TH>Created</TH>\n";
print "  <TH>Modified</TH>\n";
print "  <TH>Created By</TH>\n";
print "  <TH>Show Email?</TH>\n";
print "  <TH>Reg'ed?</TH>\n";
print "  <TH>Creds</TH>\n";
print "  <TH>Info</TH>\n";
print " </TR>\n";

while (is_array($row = $sth->fetchRow())) {
    print " <TR>\n";
    print "  <TD>";
    print implode("</TD>\n  <TD>", $row);
    print "</TD>\n";
    print " </TR>\n";
}

print "</TABLE>\n";

response_footer();

?>
