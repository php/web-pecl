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

<acronym title="PHP Extension and Application Repository">PEAR</acronym>
is a framework and distribution system for reusable PHP
components. <acronym title="PHP Extension Code Library">PECL</acronym>,
being a subset of PEAR, is the complement for C extensions for PHP.
<br />

See the <a href="/manual/en/faq.php">FAQ</a> and <a
href="/manual/">manual</a> for more information.

<br />
-';

response_header();

?>
<h1>PEAR Meeting in Amsterdam</h1>

<div style="margin-left:2em;margin-right:2em">
We will hold the first PEAR Meeting in Amsterdam on May 9th at 17:30.
<br /><br />
The <a href="http://www.terena.nl/about/secretariat/location.html">location</a>
has been graciously sponsored by Jeroen Houben. We will provide a live
stream from the meeting so that people that did not make it to the meeting in
person can participate via web. Apart from that there will be the
possibility to join the discussion via IRC.
<br /><br />
The current preliminary agenda is as follows:
<ul>
<li>Quality vs. Quantity?</li>
<li>Possible regulations to prevent some of the recent conflicts on the mailinglist</li>
<li>PEAR CS (method naming conventions?)</li>
<li><a href="http://marc.theaimsgroup.com/?l=pear-dev&m=104617534710384&w=2">PFC RfC</a></li>
<li>PEAR Installer</li>
<li>PEAR on windows</li>
<li>PEARweb (rating system, comment system, package proposal)</li>
<li>PEAR promotion</li>
<li>PHP 5</li>
</ul>

<a href="news/meeting-2003.php">More information can be found here.</a>

</div>
<?php
echo hdelim();
menu_link("PEAR 1.0 has been released!", "news/release-1.0.php");
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
    $image = make_link("/rss.php", make_image("xml.gif", "RSS feed"));
    $RSIDEBAR_DATA .= "<tr><td width=\"100%\" align=\"right\">" . $image . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

response_footer();

?>

