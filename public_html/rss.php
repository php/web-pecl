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

/**
 * This script creates an RSS file that contains the latest
 * PEAR releases. The default number of releases thar are
 * listed is 10. The number can be specified by the GET parameter
 * "limit".
 */

if (empty($_GET['limit'])) {
    $limit = 10;
} else {
    $limit = (int)$_GET['limit'];

    // sanity check
    if ($limit > 20) {
        $limit = 20;
    }    
}
 
header("Content-type: text/xml");
echo "<?xml version=\"1.0\"?>\n";
?>
<rss version="0.93">
<channel>
  <title>PEAR</title>
  <link>http://pear.php.net/</link>
  <description>This is a list containing the latest releases from PEAR.</description>
  <language>en</language>
  <image>
    <url>http://pear.php.net/gifs/pearsmall.gif</url>
    <title>PEAR</title>
    <link>http://pear.php.net/</link>
    <width>104</width>
    <height>50</height>
  </image>

<?php
$releases = release::getRecent($limit);
foreach ($releases as $release) {
    echo "  <item>\n";
    echo "    <title>" . $release['name'] . " " . $release['version'] . "</title>\n";
    echo "    <pubDate>" . $release['releasedate'] . "</pubDate>\n";
    printf("    <link>http://pear.php.net/package-info.php?package=%s&amp;version=%s</link>\n",
           $release['name'],
           $release['version']
          );

    echo "    <description>" . htmlspecialchars($release['releasenotes']) . "</description>\n";
    echo "  </item>\n";
}
?>

</channel>
</rss>
