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

See the <a href="/manual/en/faq.php">FAQ</a> and <a
href="/manual/">manual</a> for more information.

<br />
-';

response_header();

?>
<h1>PEAR 1.0 is released!</h1>

<div style="margin-left:2em;margin-right:2em">
As of PHP 4.3.0, PEAR is an officially supported
part of PHP.  From this release, the PEAR installer with all its
prerequisites is installed by default on Unix-style systems (Windows
will follow in 4.3.1).  It has been a long pregnancy.
<br /><br />
Some historical highlights:
<br /><br />
1999-11-21 : Malin Bakken was born<br />
1999-11-22 : the first few lines of PEAR code were committed (DB.php)<br />
2000-07-24 : the PEAR and PEAR_Error classes were born<br />
2000-08-01 : first working version of the "pear" command<br />
2001-05-15 : first contributor to base system<br />
2001-12-28 : first package uploaded to the current pear.php.net<br />
2002-05-26 : installer can upgrade itself<br />
2002-06-13 : first version of Gtk installer<br />
2002-07-11 : first version of Web installer<br />
<br /><br />
Thanks to all PEAR contributors, and special thanks to those who have
pitching in when I've been too busy with family and work to do any PHP
hacking:
<br /><br />
* Tomas V.V.Cox<br />
* Martin Jansen<br />
* Christian Dickmann<br />
* Jon Parise<br />
* Richard Heyes<br />
* Pierre-Alain Joye
<br /><br />
<a href="account-info.php?handle=ssb">Stig Bakken &lt;stig&#64;php.net&gt;</a>
<br /><br />
<a href="/weeklynews.php/en/20030119.html">READ THE INTERVIEW OF STIG BAKKEN</a><br />
</div>

<?php

echo hdelim();
menu_link("Documentation", "/manual/");
menu_link("Frequently Asked Questions", "/manual/en/faq.php");
menu_link("Weekly News", "/weeklynews.php");
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

response_footer();

?>
