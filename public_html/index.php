<?php

response_header();

print "<!-- $REMOTE_ADDR -->\n";

menu_link("Documentation", "http://php.net/manual/en/pear.php");

if ($SERVER_NAME != "pear.php.net") {
    menu_link("Browse Packages", "packages.php");
    menu_link("Want to contribute?", "signup.php");
    menu_link("Upload Release", "release-upload.php");
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
}

response_footer();

?>
