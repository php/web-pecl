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

<br /></p>
-';

response_header();
?>
<h3>Announcing the PEAR Group</h3>

<p>On 12th August 2003 Stig S. Bakken, the founder of PEAR, announced
the forming of the PEAR Group, which will be the governing body of
PEAR. The full announcement can be
<?php echo make_link("http://marc.theaimsgroup.com/?l=pear-dev&m=106073080219083&w=2",
                     "found here"); ?>.</p>
<p>More information about the Group, including a first administrative
document, can be found at a <?php echo make_link("/group/", "dedicated place"); ?> 
on pear.php.net.</p>

<?php
echo hdelim();

echo '<h2>'; echo make_link('/news/', 'News'); echo '</h2>';
echo '<h2>Documentation</h2><dl>';
echo '<dd>'; menu_link("About PEAR", "/manual/en/about-pear.php"); echo '</dd>';
echo '<dd>'; menu_link("Manual", "/manual/"); echo '</dd>';
echo '<dd>'; menu_link("Frequently Asked Questions", "/manual/en/faq.php"); echo '</dd>';
echo '<dd>'; menu_link("Mailing Lists & Support Resources", "/support.php"); echo '</dd>';
echo '</dl>';
echo '<h2>Downloads</h2><dl>';
echo '<dd>'; menu_link("Browse All Packages", "packages.php"); echo '</dd>';
echo '<dd>'; menu_link("Search Packages", "package-search.php"); echo '<dd>';
echo '<dd>'; menu_link("Download Statistics", "package-stats.php"); echo '</dd>';
echo '</dl>';
if (isset($_COOKIE['PEAR_USER'])) {
    echo '<h2>Developers</h2><dl>';
    echo '<dd>'; menu_link("Upload Release", "release-upload.php"); echo '</dd>';
    echo '<dd>'; menu_link("New Package", "package-new.php"); echo '</dd>';
    echo '</dl>';
    if (user::isAdmin($_COOKIE['PEAR_USER'])) {
        echo '<h2>Administrators</h2><dl>';
        echo '<dd>'; menu_link("Overview", "/admin/"); echo '</dd>';
        echo '<dd>'; menu_link("Maintainers", "/admin/package-maintainers.php"); echo '</dd>';
        echo '<dd>'; menu_link("Categories", "/admin/category-manager.php"); echo '</dd>';
        echo '</dl>';
    }
}
// XXX Hide for the moment?
menu_link("Request PEAR Account", "account-request.php");

echo hdelim();
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
    $image = make_link("/rss.php", make_image("xml.gif", "RSS feed"));
    $RSIDEBAR_DATA .= "<tr><td width=\"100%\" align=\"right\">" . $image . "</td></tr>\n";
    $RSIDEBAR_DATA .= "</table>\n";
}

response_footer();

?>

