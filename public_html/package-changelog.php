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

// expected url vars: pacid package
if (isset($_GET['package']) && empty($_GET['pacid'])) {
    $pacid = $dbh->getOne("SELECT id FROM packages WHERE name = ?",
                          array($_GET['package']));
} else {
    $pacid = (isset($_GET['pacid'])) ? (int) $_GET['pacid'] : null;
}

if ($pacid != (string)(int)$pacid) {
    die('Invalid package id');
}
$name = package::info($pacid, 'name');

response_header("$name Changelog");
print '<p>' . make_link("package-info.php?pacid=$pacid", 'Return') . '</p>';
$bb = new Borderbox("$name Changelog");

$sql = "SELECT releases.version AS version, ".
       "DATE_FORMAT(releases.releasedate, '%Y-%m-%d') AS releasedate, ".
       "releases.releasenotes AS releasenotes, ".
       "releases.state AS state ".
       "FROM releases ".
       "WHERE releases.package = $pacid ".
       "ORDER BY releases.releasedate DESC";

$res = $dbh->query($sql);

if ($res->numRows() < 1) {
    print "<center><p><i>No releases yet</i></p></center>";
} else {
    print "<table width=\"100%\" border=\"0\">\n";

    while ($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
        extract($row);
        if (isset($_GET['release']) && $_GET['release'] == $version) {
            $bgcolor1 = "#dddddd";
            $bgcolor2 = "#eeeeee";
        } else {
            $bgcolor1 = "#FFFFFF";
            $bgcolor2 = "#FFFFFF";
        }

        print "<tr bgcolor=\"" . $bgcolor1 . "\"><td><p><b>Version: $version-$state ($releasedate)".
              "</b></p></td></tr>\n".
              "<tr bgcolor=\"" . $bgcolor2 . "\"><td>" . nl2br($releasenotes) ."<br /></td></tr>\n";
    }
    print "</table>\n";
}
$bb->end();
print '<p>' . make_link("package-info.php?pacid=$pacid", 'Return') . '</p>';
response_footer();
?>