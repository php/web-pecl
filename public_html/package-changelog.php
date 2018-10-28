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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
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
$bb = new Borderbox("Changelog for " . $name, "90%", "", 2, true);

if (count($pkg['releases']) == 0) {
    $bb->fullRow('There are no releases for ' . $name . ' yet.');
} else {
    $bb->headRow("Release", "What has changed?");

    foreach ($pkg['releases'] as $version => $release) {
        $link = make_link("package-info.php?package=" . $pkg['name'] .
                          "&amp;version=" . urlencode($version), $version);

        if (!empty($_GET['release']) && $version == $_GET['release']) {
            $bb->horizHeadRow($link, nl2br($release['releasenotes']));
        } else {
            $bb->plainRow($link, nl2br($release['releasenotes']));
        }
    }
}
$bb->end();
print '<p>' . make_link("/" . $name, 'Return') . '</p>';
response_footer();
