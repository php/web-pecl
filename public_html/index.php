<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

$SIDEBAR_DATA='
<h3>What is PEAR?</h3>
<p>
&quot;The fleshy pome, or fruit, of a rosaceous tree (Pyrus
communis), cultivated in many varieties in temperate
climates.&quot;
</p>
<p>

<acronym title="PHP Extension and Application Repository">PEAR</acronym>
is a framework and distribution system for reusable PHP
components. <acronym title="PHP Extension Code Library">PECL</acronym>,
being a subset of PEAR, is the complement for C extensions for PHP.
<br />

See the <a href="/manual/en/faq.php">FAQ</a> and <a
href="/manual/">manual</a> for more information.

<br /></p>
-';

response_header();

echo '<h2>'; echo make_link('/news/', 'News'); echo '</h2>';
echo '<h2>Documentation</h2>';
echo '<div class="indent">';
echo menu_link("About PEAR", "/manual/en/about-pear.php");
echo menu_link("Manual", "/manual/");
echo menu_link("Frequently Asked Questions", "/manual/en/faq.php");
echo menu_link("Mailing Lists & Support Resources", "/support.php");
echo '</div>';
echo '<h2>Downloads</h2>';
echo '<div class="indent">';
echo menu_link("Browse All Packages", "packages.php");
echo menu_link("Search Packages", "package-search.php");
echo menu_link("Download Statistics", "package-stats.php");
echo '</div>';
if (isset($_COOKIE['PEAR_USER'])) {
    echo '<h2>Developers</h2>';
    echo '<div class="indent">';
    echo menu_link("Upload Release", "release-upload.php");
    echo menu_link("New Package", "package-new.php");
    echo '</div>';
    if (user::isAdmin($_COOKIE['PEAR_USER'])) {
        echo '<h2>Administrators</h2>';
        echo '<div class="indent">';
        echo menu_link("Overview", "/admin/");
        echo menu_link("Maintainers", "/admin/package-maintainers.php");
        echo menu_link("Categories", "/admin/category-manager.php");
        echo '</div>';
    }
}
// XXX Hide for the moment?
menu_link("Request PEAR Account", "account-request.php");

$recent = release::getRecent();
if (@sizeof($recent) > 0) {
    $RSIDEBAR_DATA = "<h3>Recent Releases</h3>\n";
    $RSIDEBAR_DATA .= "<table>";
    foreach ($recent as $release) {
        extract($release);
        $releasedate = substr($releasedate, 0, 10);
        $desc = substr($releasenotes, 0, 40);
        if (strlen($releasenotes) > 40) {
            $desc .= '...';
        }
        $desc = htmlentities($desc);
        $RSIDEBAR_DATA .= "<tr><td valign='top' class='compact'>";
        $RSIDEBAR_DATA .= "<a href=\"/" . $name . "\">";
        $RSIDEBAR_DATA .= "$name $version</a><br /><i>$releasedate:</i> $desc</td></tr>";
    }
    $feed_link = make_link("/feeds/", "Syndicate this");
    $RSIDEBAR_DATA .= "<tr><td>&nbsp;</td></tr>\n";
    $RSIDEBAR_DATA .= "<tr><td width=\"100%\" align=\"right\">" . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

response_footer();

?>

