<?php

ini_set("y2k_compliance", "on");
if ($HTTP_SERVER_VARS['QUERY_STRING'] == 'devme') {
    $duration = 86400 * 360;
    setcookie("pear_dev", "on", time() + $duration, "/");
    $HTTP_COOKIE_VARS['pear_dev'] = "on";
} elseif ($HTTP_SERVER_VARS['QUERY_STRING'] == 'undevme') {
    setcookie("pear_dev");
    unset($HTTP_COOKIE_VARS['pear_dev']);
}


$SIDEBAR_DATA='
<h3>What is PEAR?</h3>
<p>

PEAR is a framework and distribution system for reusable PHP
components.

<br />

</p>
-
';

response_header();

menu_link("Documentation", "http://php.net/manual/en/pear.php");

if (DEVBOX) {
    menu_link("Request PEAR Account", "account-request.php");
    menu_link("New Package", "package-new.php");
    menu_link("Administrators", "admin.php");
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
