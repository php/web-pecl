<?php
$SIDEBAR_DATA='
<H3>What is PEAR?</H3>
<P>
PEAR is a code repository for PHP extensions
and PHP library code inspired by TeX\'s CTAN
and Perl\'s CPAN.
</P>
';

response_header();

print "<!-- $REMOTE_ADDR -->\n";

menu_link("Documentation", "http://php.net/manual/en/pear.php");

if ($SERVER_NAME != "pear.php.net") {
    menu_link("Request a PEAR Account", "account-request.php");
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
