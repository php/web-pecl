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

response_header("News");

echo "<h1>PEAR news</h1>";

echo "<h2><a name=\"2003\" />Year 2003</h2>";
echo "<ul>";

echo "<li>" . make_link("activestate-award-ssb.php", "ActiveState Active Award for Stig Bakken") . " (July)</li>";
echo "<li>" . make_link("meeting-2003-summary.php", "Summary of the PEAR Meeting") . " (May)</li>";
echo "<li>" . make_link("meeting-2003.php", "PEAR Meeting in Amsterdam") . " (March)</li>";
echo "<li>" . make_link("release-1.0.php", "PEAR 1.0 is released!") . " (January)</li>";

echo "</ul>";

response_footer();
?>
