<?php

pageHeader();

print "<H1>PEAR: PHP Extension and Application Repository</H1>\n";

if ($SERVER_NAME != "pear.php.net" || $REMOTE_ADDR == '213.188.9.2') {
    menuLink("Authors", "authors.php");
    menuLink("Packages", "packages.php");
    menuLink("Domains", "domains.php");
}
menuLink("Documentation", "doc/");

print "<!-- $REMOTE_ADDR -->\n";

?>
