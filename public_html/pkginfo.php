<?php

response_header("PEAR :: Package :: $package");

$descriptions = array(
    "name" => "Package Name",
    "stablerelease" => "Latest Stable Release",
    "develrelease" => "Latest Development Release",
    "copyright" => "License",
    "summary" => "Package Description"
);

$row = $dbh->getRow("SELECT * FROM packages WHERE name = '$package'",
                    DB_FETCHMODE_ASSOC);
print "<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=1>";
print "<TR><TD BGCOLOR=\"#000000\">\n";
print "<TABLE CELLSPACING=1 BORDER=0 CELLPADDING=3>\n";
foreach ($row as $field => $value) {
    if (empty($descriptions[$field])) {
        continue;
    }
    if (empty($value)) {
        $value = "&nbsp;";
    }
    print " <TR BGCOLOR=\"#ffffff\">\n";
    print "  <TH ALIGN=\"left\">$descriptions[$field]</TH>\n";
    print "  <TD>$value</TD>\n";
    print " </TR>\n";
}
print "</TABLE></TD></TR></TABLE>\n";

response_footer();

?>
