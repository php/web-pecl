<?php

include("DB.php");

pageHeader();

print "<H1>PEAR: PHP Extension and Add-on Repository</H1>\n";

print "<A HREF=\"add-package.php\">ADD NEW PACKAGE</A><BR>\n";
print "<A HREF=\"add-author.php\">ADD NEW AUTHOR</A><BR>\n";
print "<A HREF=\"list-packages.php\">LIST PACKAGES</A><BR>\n";
print "<A HREF=\"list-authors.php\">LIST AUTHORS</A><BR>\n";
print "<A HREF=\"domains.php\">DOMAIN ADMINISTRATION</A><BR>\n";

pageFooter();

?>
