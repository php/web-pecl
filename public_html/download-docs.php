<?php
/**
 * Interface to download PEAR documentation in different formats
 *
 * $Id$
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

echo "<font color=\"#ff0000\"><b>Warning: Most of the links don't work at the moment.</b></font>\n";

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
