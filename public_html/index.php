<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

$recent = Release::getRecent();
if (@sizeof($recent) > 0) {
    $RSIDEBAR_DATA = "<strong>Recent&nbsp;Releases:</strong>\n";
    $RSIDEBAR_DATA .= '<table class="sidebar-releases">' . "\n";
    foreach ($recent as $release) {
        extract($release);
        $releasedate = $formatDate->utc($releasedate, 'Y-m-d');
        $desc = substr($releasenotes, 0, 40);
        if (strlen($releasenotes) > 40) {
            $desc .= '...';
        }
        $desc = htmlentities($desc);
        $RSIDEBAR_DATA .= "<tr><td valign=\"top\">";
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

echo '<h3><a href="/news/">News</a></h3>';
echo '<h3>Documentation</h3>';
echo '<div class="indent">';
echo '<a href="/doc/index.php" class="item">PECL specific docs</a><br>';
echo '<a href="/support.php" class="item">Mailing Lists &amp; Support Resources</a><br>';
echo '</div>';
echo '<h3>Downloads</h3>';
echo '<div class="indent">';
echo '<a href="/packages.php" class="item">Browse All Packages</a><br>';
echo '<a href="/package-search.php" class="item">Search Packages</a><br>';
echo '<a href="/package-stats.php" class="item">Download Statistics</a><br>';
echo '</div>';
if (!empty($auth_user)) {
    echo '<h3>Developers</h3>';
    echo '<div class="indent">';
    echo '<a href="/release-upload.php" class="item">Upload Release</a><br>';
    echo '<a href="/package-new.php" class="item">New Package</a><br>';
    echo '</div>';
    if ($auth_user->isAdmin()) {
        echo '<h3>Administrators</h3>';
        echo '<div class="indent">';
        echo '<a href="/admin" class="item">Overview</a><br>';
        echo '<a href="/admin/package-maintainers.php" class="item">Maintainers</a><br>';
        echo '<a href="/admin/category-manager.php" class="item">Categories</a><br>';
        echo '</div>';
    }
}

echo '<a href="/account-request.php" class="item">I want to publish my PHP Extension in PECL</a><br>';

response_footer();
