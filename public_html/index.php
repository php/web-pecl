<?php

pageHeader();

print "<H1>PEAR: PHP Extension and Add-on Repository</H1>\n";

if ($SERVER_NAME != "pear.php.net") {
    print "<A HREF=\"authors.php\">AUTHORS</A><BR>\n";
    print "<A HREF=\"packages.php\">PACKAGES</A><BR>\n";
    print "<A HREF=\"domains.php\">DOMAINS</A><BR>\n";
}
print "<H2><A HREF=\"doc/\">Documentation</A></H2>\n";

$dbh = DB::connect("mysql://pear@localhost/pear");
$ssb = new Author(&$dbh, "SSB");
print $ssb->toString();

pageFooter();

?>
