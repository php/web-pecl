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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
 */

auth_require();
require_once "HTML/Form.php";

response_header("Package statistics");
?>

<h1>Package statistics</h1>

<?php

$form = new HTML_Form($_SERVER['PHP_SELF']);

/** Get packages for the user */
if (User::isAdmin($_SERVER['PHP_AUTH_USER'])) {
    $query = "SELECT * FROM packages ORDER BY name";
} else {
    $query = "SELECT p.* FROM packages p, mantains m WHERE p.id = m.package"
             . " AND m.handle = '" . $_SERVER['PHP_AUTH_USER'] . "'"
             . " ORDER BY p.name";
}

$sth = $dbh->query($query);

while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $packages[$row['id']] = $row['name'];
}

echo "<div class=\"searchCage\">\n";
$form->addSelect("pid", "Package", $packages, (isset($_GET['pid']) ? $_GET['pid'] : ""));
$form->addSubmit("submit", "Go");
$form->display();
echo "</div>\n";

if (isset($_GET['pid'])) {

    if (User::isAdmin($_SERVER['PHP_AUTH_USER']) || 
        maintainer::get($_GET['pid']) == $_SERVER['PHP_AUTH_USER'])
    {
        $info = package::info($_GET['pid']);
        
        echo "<h2>Statistics for package \"" . $info['name'] . "\"</h2>\n";

        $bb = new Borderbox("General statistics");
        echo "Number of releases: <b>" . count($info['releases']) . "</b><br />\n";
        echo "Total downloads: <b>" . statistics::package($_GET['pid']) . "</b><br />\n";
        $bb->end();

        if (count($info['releases']) > 0) {
            echo "<br />\n";
            $bb = new Borderbox("Release statistics");

            $release_statistics = statistics::release($_GET['pid']);

            $i= 0;
            foreach ($info['releases'] as $key => $value) {
                $bb2 = new Borderbox("Release: <b>" . $key . "</b>", 400);                
                echo "Number of downloads: <b>" . $release_statistics[$i]['total'] . "</b><br />\n";

                if ($release_statistics[$i]['total'] > 1) {
                    echo "First download: <b>" . $release_statistics[$i]['first_download'] . "</b><br />\n";
                    echo "Last download: <b>" . $release_statistics[$i]['last_download'] . "</b><br />\n";
                }

                $bb2->end();
                $i++;
                echo "<br />\n";
            }
            
            $bb->end();
        }

    } else {
        PEAR::raiseError("Not enough priviliges to view statistics for"
                         . " this package.");
    }
}

response_footer();
?>
