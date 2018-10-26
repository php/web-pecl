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
*/

$recent = release::getRecent();
if (@sizeof($recent) > 0) {
    $RSIDEBAR_DATA = "<strong>Recent&nbsp;Releases:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    foreach ($recent as $release) {
        extract($release);
        $releasedate = make_utc_date(strtotime($releasedate), 'Y-m-d');
        $desc = substr($releasenotes, 0, 40);
        if (strlen($releasenotes) > 40) {
            $desc .= '...';
        }
        $desc = htmlentities($desc);
        $RSIDEBAR_DATA .= "<tr><td valign=\"top\" class=\"compact\">";
        $RSIDEBAR_DATA .= "<a href=\"/package/" . $name . "/\">";
        $RSIDEBAR_DATA .= "$name $version</a><br /><i>$releasedate:</i> $desc</td></tr>";
    }
    $feed_link = "<a href=\"/feeds/\">Syndicate this</a>";
    $RSIDEBAR_DATA .= "<tr><td>&nbsp;</td></tr>\n";
    $RSIDEBAR_DATA .= '<tr><td align="right">' . $feed_link . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

response_header();

echo '<h3>What is PECL?</h3>
<p>
<acronym title="PHP Extension Community Library">PECL</acronym>
is a repository for PHP Extensions, providing a directory of all known
extensions and hosting facilities for downloading and development of PHP
extensions.
</p>
<p>
The packaging and distribution system used by PECL is shared with its
sister, <acronym title="PHP Extension and Application Repository"
>PEAR</acronym>.
</p>';

echo '<h3>'; echo make_link('/news/', 'News'); echo '</h3>';
echo '<h3>Documentation</h3>';
echo '<div class="indent">';
echo menu_link("PECL specific docs", "/doc/index.php");
echo menu_link("Mailing Lists &amp; Support Resources", "/support.php");
echo '</div>';
echo '<h3>Downloads</h3>';
echo '<div class="indent">';
echo menu_link("Browse All Packages", "packages.php");
echo menu_link("Search Packages", "package-search.php");
echo menu_link("Download Statistics", "package-stats.php");
echo '</div>';
if (!empty($auth_user)) {
    echo '<h3>Developers</h3>';
    echo '<div class="indent">';
    echo menu_link("Upload Release", "release-upload.php");
    echo menu_link("New Package", "package-new.php");
    echo '</div>';
    if (user::isAdmin($auth_user->handle)) {
        echo '<h3>Administrators</h3>';
        echo '<div class="indent">';
        echo menu_link("Overview", "/admin/");
        echo menu_link("Maintainers", "/admin/package-maintainers.php");
        echo menu_link("Categories", "/admin/category-manager.php");
        echo '</div>';
    }
}
// XXX Hide for the moment?
menu_link("I want to publish my PHP Extension in PECL", "account-request.php");

response_footer();
