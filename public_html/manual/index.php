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

response_header("Documentation");

$languages = array("en" => "English", "de" => "German", "it" => "Italian", "ru" => "Russian");
?>

<h1>Documentation</h1>

<p>The PEAR documentation is a centralized place where developers can
add the documentation for their package.</p>

<p>Currently the documentation is available in the following languages:</p>

<ul>

<?php
$i = 0;
foreach ($languages as $domain => $name) {
    echo "<li>";
    if ($i++ == 0) {
        echo "<b>" . make_link("/manual/" . $domain . "/", $name) . "</b>";
    } else {
        echo make_link("/manual/" . $domain . "/", $name);
    }
    echo "</li>";
}
?>

</ul>

<p>If you prefer to have a local version of the documentation, you can
download it in a bunch of formats:</p>

<?php
$formats = array(   
    "pear_manual_{LANG}.tar.gz"      => array("Many HTML files",     "tar.gz"),
    "pear_manual_{LANG}.zip"         => array("Many HTML files",     "zip"),
    "pear_manual_{LANG}.tar.bz2"     => array("Many HTML files",     "tar.bz2"),
    "pear_manual_{LANG}.html.gz"     => array("One big HTML file",   "html.gz"),
    "pear_manual_{LANG}.txt.gz"      => array("Plain text file",     "txt.gz")
);

$bb = new BorderBox("Download documentation", "70%", "", 3, true);
$bb->HeadRow("Type", "Format", "Size");

foreach ($languages as $domain => $name) {
    $bb->fullRow("<b>" . $name . "</b>");

    foreach ($formats as $filename => $information) {
        $filename = str_replace("{LANG}", $domain, $filename);

        $link = make_link("distributions/manual/" . $filename, $information[0]);
        $bb->plainRow($link, $information[1],
                      (int) (@filesize("distributions/manual/" . $filename)/1024) . "KB");
    }

}

$bb->end();

response_footer();
?>