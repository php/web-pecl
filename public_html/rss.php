<?php
/**
 * This script creates an RSS file that contains the latest
 * PEAR releases. The default number of releases thar are
 * listed is 5. The number can be specified by the GET parameter
 * "limit".
 *
 * $Id$
 */

if (!isset($_GET['limit']) || $_GET['limit'] == "") {
    $limit = 5;
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

<rss version="0.91">
<channel>
  <title>PEAR</title>
  <link>http://pear.php.net/</link>
  <description>This are the latest releases from PEAR.</description>
  <image>
    <url>http://pear.php.net/gifs/pearsmall.gif</url>
    <title>PEAR</title>
    <link>http://pear.php.net/</link>
    <width>104</width>
    <height>50</height>
  </image>

<?php
foreach (release::getRecent($limit) as $release) {

    $desc = substr($release['releasenotes'], 0, 40);
    if (strlen($release['releasenotes']) > 40) {
        $desc .= '...';
    }

    echo "  <item>\n";

    echo "    <title>" . $release['name'] . "</title>\n";
    printf("    <link>http://pear.php.net/package-info.php?pacid=%s&amp;release=%s</link>\n",
           $release['id'],
           $release['version']
          );

    echo "    <description>" . htmlspecialchars($desc) . "</description>\n";
    echo "  </item>\n";
}
?>

</channel>
</rss>