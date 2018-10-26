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

response_header("News");

echo "<h1>PECL news</h1>";

echo "<h2><a name=\"recent_releases\"></a>Recent Releases</h2>";
echo "<ul>";

$recent = release::getRecent();
foreach ($recent as $release) {
    $releasedate = make_utc_date(strtotime($release['releasedate']), 'Y-m-d');
    $desc = nl2br(htmlentities(substr($release['releasenotes'], 0, 400)));
    if (strlen($release['releasenotes']) > 400) {
        $desc .= ' <a href="/package/' . $release['name'] . '/' . $release['version'] . '">...</a>';
    }

    echo "<li><a href=\"/package/" . $release['name'] . "/\">";
    echo "$release[name] $release[version] ($release[state])</a> <i>$releasedate</i><br/>$desc</li>";
}

echo "</ul>\n<a href=\"/feeds/\">Syndicate this</a>";

echo "<h2><a name=\"2003\"></a>Year 2003</h2>";
echo "<ul>";
echo "<li>" . make_link("http://news.php.net/article.php?group=php.pecl.dev&article=5", "Call for PHP Extension authors") . " (September)</li>";
echo "</ul>";

response_footer();
