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

// expected url vars: pacid package
if (isset($_GET['package']) && empty($_GET['pacid'])) {
    $pacid = $_GET['package'];
} else {
    $pacid = (isset($_GET['pacid'])) ? (int) $_GET['pacid'] : null;
}

$pkg = package::info($pacid);

if (empty($pkg['name'])) {
    response_header("Error");
    PEAR::raiseError('Invalid package');
    response_footer();
    exit();
}

$name = $pkg['name'];
response_header("$name Changelog");
print '<p>' . make_link("/" . $name, 'Return') . '</p>';
$bb = new Borderbox("$name Changelog");

if (count($pkg['releases']) == 0) {
    print "<center><p><i>No releases yet</i></p></center>";
} else {
    print "<table width=\"100%\" border=\"0\">\n";

    foreach ($pkg['releases'] as $version => $release) {
        extract($release);

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
print '<p>' . make_link("/" . $name, 'Return') . '</p>';
response_footer();
?>