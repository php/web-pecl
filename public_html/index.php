<?php

ini_set('y2k_compliance', 'on');
if ($HTTP_SERVER_VARS['QUERY_STRING'] == 'devme') {
    $duration = 86400 * 360;
    setcookie('pear_dev', 'on', time() + $duration, '/');
    $HTTP_COOKIE_VARS['pear_dev'] = 'on';
} elseif ($HTTP_SERVER_VARS['QUERY_STRING'] == 'undevme') {
    setcookie('pear_dev');
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
    $recent = get_recent_releases();
    if (@sizeof($recent) > 0) {
        print "<b>Recent Releases</b>\n";
        print "<table>";
        foreach ($recent as $release) {
            extract($release);
            print "<tr><td valign='top'><font size='-1'>";
            print "<a href=\"pkginfo.php?package=$name&release=$version\">";
            print "$name $version</a></font></td>";
            print "<td valign='top'><font size='-1'>";
            print "$doneby, $releasedate: $releasenotes</font></td></tr>\n";
        }
        print "</table>\n";
    }
}

response_footer();

?>