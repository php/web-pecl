<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

ini_set('y2k_compliance', 'on');
if ($_SERVER['QUERY_STRING'] == 'devme') {
    $duration = 86400 * 360;
    setcookie('pear_dev', 'on', time() + $duration, '/');
    $_COOKIE['pear_dev'] = 'on';
} elseif ($_SERVER['QUERY_STRING'] == 'undevme') {
    setcookie('pear_dev', '', time() - 3600, '/');
    unset($_COOKIE['pear_dev']);
}

$SIDEBAR_DATA='
<h3>What is PEAR?</h3>
<p>
&quot;The fleshy pome, or fruit, of a rosaceous tree (Pyrus
communis), cultivated in many varieties in temperate
climates.&quot;
</p>
<p>

PEAR is a framework and distribution system for reusable PHP
components.

<br />

</p>
-
';

response_header();

menu_link("Documentation", "/manual/");
menu_link("Frequently Asked Questions", "/manual/en/faq.php");

if (DEVBOX) {
    menu_link("Browse Packages", "packages.php");
	menu_link("Search Packages", "package-search.php");
    menu_link("Request PEAR Account", "account-request.php");
    echo hdelim();
    echo "<h3>Available when logged in:</h3>";
    menu_link("New Package", "package-new.php");
    menu_link("Upload Release", "release-upload.php");
    menu_link("Administrators", "admin.php");
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
            $RSIDEBAR_DATA .= "<tr><td valign='top' class='compact'>";
            $RSIDEBAR_DATA .= "<a href=\"package-info.php?pacid=$id&release=$version\">";
//            $RSIDEBAR_DATA .= "$name $version</a><br /><font size=\"-1\" face=\"arial narrow,arial,helvetica,sans-serif\"><i>$releasedate:</i>$desc</font></td></tr>";
            $RSIDEBAR_DATA .= "$name $version</a><br /><i>$releasedate:</i> $desc</td></tr>";
        }
        $RSIDEBAR_DATA .= "</table>\n";
    }
}

response_footer();

?>
