<?php

pageHeader();

print "<H1>PEAR: PHP Extension and Add-on Repository</H1>\n";

if ($SERVER_NAME != "pear.php.net" || $REMOTE_ADDR == '213.188.9.2') {
    print "<A HREF=\"authors.php\">AUTHORS</A><BR>\n";
    print "<A HREF=\"packages.php\">PACKAGES</A><BR>\n";
    print "<A HREF=\"domains.php\">DOMAINS</A><BR>\n";
}
print "<IMG SRC=\"/gifs/pear.gif\">\n";
print "<H2><A HREF=\"doc/\">Documentation</A></H2>\n";

print "<!-- $REMOTE_ADDR -->\n";

?>
