<?php

response_header();

print "<H1>PEAR: PHP Extension and Application Repository</H1>\n";

if ($SERVER_NAME != "pear.php.net" || $REMOTE_ADDR == '213.188.9.2') {
    menu_link("Browse Packages", "packages.php");
    menu_link("Want to contribute?", "signup.php");
}
menu_link("Documentation", "http://php.net/manual/en/pear.php");

print "<!-- $REMOTE_ADDR -->\n";

response_footer();

?>
