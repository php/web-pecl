<?php
response_header("Author information");

$sth = $dbh->query("SELECT * FROM users WHERE registered = 1 AND handle = '".$HTTP_GET_VARS['handle']."'");
if (DB::isError($sth)) {
    DB::raiseError();
}

if ($sth->numRows() == 0) {
    PEAR::raiseError("No author information found!");
}

print "<H1>Information about author \"".$HTTP_GET_VARS['handle']."\"</H1>\n";

$row = $sth->fetchRow(DB_FETCHMODE_ASSOC);

print "<P>\n";

print "<TABLE BORDER=\"0\" CELLSPACING=\"1\" CELLPADDING=\"5\">\n";
print " <TR>\n";
print "  <TH BGCOLOR=\"#CCCCCC\">Handle:</TH>\n";
print "  <TD BGCOLOR=\"#e8e8e8\">".$HTTP_GET_VARS['handle']."</TD>\n";
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

$query = "SELECT package FROM maintains WHERE handle = '".$HTTP_GET_VARS['handle']."'";
$sth = $dbh->query($query);

if (DB::IsError($sth)) {
    DB::raiseError("query failed: ".$sth->message);
}

if ($sth->numRows() > 0) {
    print "<BR><BR>\n";
    print "<TABLE BORDER=\"0\" CELLSPACING=\"1\" CELLPADDING=\"5\">\n";
    print " <TR>\n";
    print "  <TH BGCOLOR=\"#e8e8e8\">The author is maintaing the following packages:</TD>";
    print " </TR>\n";
}

while (is_array($row = $sth->fetchRow(DB_FETCHMODE_ASSOC))) {
    print " <TR>\n";
    print "  <TD BGCOLOR=\"#e8e8e8\">".$row['package']."</TD>\n";
    print " </TR>\n";       
}

if ($sth->numRows() > 0) {
    print "</TABLE>";
}

response_footer();

?>
