<?php

response_header();

print "<!-- $REMOTE_ADDR -->\n";

if ($SERVER_NAME != "pear.php.net" || $REMOTE_ADDR == '213.188.9.2') {
    menu_link("Browse Packages", "packages.php");
    menu_link("Want to contribute?", "signup.php");
}
menu_link("Documentation", "http://php.net/manual/en/pear.php");

print "<B>Recent Releases</B>\n";
$recent = get_recent_releases();
print "<TABLE>";
foreach ($recent as $release) {
    extract($release);
    print "<TR><TD VALIGN='top'><FONT SIZE='-1'>";
    print "<A HREF=\"pkginfo.php?package=$name&release=$version\">";
    print "$name $version</A></FONT></TD>";
    print "<TD VALIGN='top'><FONT SIZE='-1'>";
    print "$doneby, $releasedate: $releasenotes</FONT></TD></TR>\n";
}
print "</TABLE>\n";

response_footer();

?>
