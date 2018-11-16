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

use App\Repository\Release;

$releaseRepository = new Release($dbh);

response_header("News");

echo "<h1>PECL news</h1>";

echo "<h2><a name=\"recent_releases\"></a>Recent Releases</h2>";
echo "<ul>";

$sql = "SELECT packages.id AS id,
            packages.name AS name,
            packages.summary AS summary,
            releases.version AS version,
            releases.releasedate AS releasedate,
            releases.releasenotes AS releasenotes,
            releases.doneby AS doneby,
            releases.state AS state
        FROM packages, releases
        WHERE packages.id = releases.package
            AND packages.approved = 1
            AND packages.package_type = 'pecl'
        ORDER BY releases.releasedate DESC LIMIT 5";

$statement = $database->run($sql);

foreach ($statement as $row) {
    $releasedate = $formatDate->utc($row['releasedate'], 'Y-m-d');
    $desc = nl2br(htmlentities(substr($row['releasenotes'], 0, 400)));

    if (strlen($row['releasenotes']) > 400) {
        $desc .= ' <a href="/package/' . $row['name'] . '/' . $row['version'] . '">...</a>';
    }

    echo "<li><a href=\"/package/" . $row['name'] . "/\">";
    echo "$row[name] $row[version] ($row[state])</a> <i>$releasedate</i><br/>$desc</li>";
}

echo "</ul>\n<a href=\"/feeds/\">Syndicate this</a>";

echo "<h2><a name=\"2003\"></a>Year 2003</h2>";
echo "<ul>";
echo '<li><a href="https://news.php.net/article.php?group=php.pecl.dev&article=5">Call for PHP Extension authors</a> (September)</li>';
echo "</ul>";

response_footer();
