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

/**
 * Interface to download PEAR documentation in different formats
 */
response_header("Documentation");

$formats = array(   
    "pear_manual_{LANG}.tar.gz"      => array("Many HTML files",     "tar.gz"),
    "pear_manual_{LANG}.zip"         => array("Many HTML files",     "zip"),
    "pear_manual_{LANG}.tar.bz2"     => array("Many HTML files",     "tar.bz2"),
    "pear_manual_{LANG}.html.gz"     => array("One big HTML file",   "html.gz"),
    "pear_manual_{LANG}.txt.gz"      => array("Plain text file",     "txt.gz")
);

$languages = array("en" => "English", "de" => "German", "it" => "Italian", "ru" => "Russian");

$bb = new Borderbox("Download documentation");

echo "<ul>\n";

foreach ($languages as $domain => $name) {
    echo "<li><b>" . $name . ":</b><br />\n";
    echo "<a href=\"manual/" . $domain . "/\">Read online</a><br /><br />\n";
    foreach ($formats as $filename => $information) {
        $filename = str_replace("{LANG}", $domain, $filename);
        printf("<a href=\"distributions/manual/%s\" title=\"%s\">%s</a> (%s)<br />\n",
                $filename,
                "Size: " . (int) (@filesize("distributions/manual/" . $filename)/1024) . "KB",
                $information[0],
                $information[1]
              );
    }
    echo "</li><br /><br />\n";
}

echo "</ul>\n";

$bb->end();

response_footer();
?>
