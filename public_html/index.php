<?php

ini_set('y2k_compliance', 'on');
if ($HTTP_SERVER_VARS['QUERY_STRING'] == 'devme') {
    $duration = 86400 * 360;
    setcookie('pear_dev', 'on', time() + $duration, '/');
    $HTTP_COOKIE_VARS['pear_dev'] = 'on';
} elseif ($HTTP_SERVER_VARS['QUERY_STRING'] == 'undevme') {
    setcookie('pear_dev', '', time() - 3600, '/');
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

menu_link("Documentation", "/manual/");
menu_link("Frequently Asked Questions", "faq.php");

if (DEVBOX) {
    menu_link("Request PEAR Account", "account-request.php");
    menu_link("New Package", "package-new.php");
    menu_link("Administrators", "admin.php");
    menu_link("Browse Packages", "packages.php");
    menu_link("Want to contribute?", "signup.php");
    menu_link("Upload Release", "release-upload.php");
    $recent = release::getRecent();
    if (@sizeof($recent) > 0) {
        
        $RSIDEBAR_DATA = "<h3>Recent Releases</h3>\n";
        $RSIDEBAR_DATA .= "<table>";
        foreach ($recent as $release) {
            extract($release);
            $RSIDEBAR_DATA .= "<tr><td valign='top'><p>";
            $RSIDEBAR_DATA .= "<a href=\"pkginfo.php?pacid=$name&release=$version\">";
            $RSIDEBAR_DATA .= "$name $version</a><br /><small>($releasedate)</small></p></td></tr>";
        }
        $RSIDEBAR_DATA .= "</table>\n";
    }
}

response_footer();

?>
