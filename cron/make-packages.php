#!/usr/bin/php -Cq
<?php // -*- C++ -*-
/* vim: set expandtab tabstop=4 shiftwidth=4; */
// +---------------------------------------------------------------------+
// |  PHP version 4.0                                                    |
// +---------------------------------------------------------------------+
// |  Copyright (c) 1997-2001 The PHP Group                              |
// +---------------------------------------------------------------------+
// |  This source file is subject to version 2.0 of the PHP license,     |
// |  that is bundled with this package in the file LICENSE, and is      |
// |  available through the world-wide-web at                            |
// |  http://www.php.net/license/2_02.txt.                               |
// |  If you did not receive a copy of the PHP license and are unable to |
// |  obtain it through the world-wide-web, please send a note to        |
// |  license@php.net so we can mail you a copy immediately.             |
// +---------------------------------------------------------------------+
// |  Authors:  Christian Stocker <chregu@phant.ch>                      |
// |            Martin Jansen <mj@php.net>                               |
// +---------------------------------------------------------------------+
//

/**
 * This script is used to maintain new PEAR packages:
 *
 * It walks through PEAR_Dir and looks for a package.xml file
 * in each directory. If such a file is found, it's contents
 * are read. This contents are stored in a database and a tgz
 * file with the sources is created.
 *
 * WARNING: This code is highly experimental and may still contain
 *          tons of bugs.       - mj
 */


require_once "PEAR/Packager.php";
require_once "DB.php";

/**
 * Directory where to store the TGZ files
 * @var string
 */
$TgzDir = "/var/www/pear/download";

/**
 * Directory where the PEAR packages reside
 * @var string
 */
$PEAR_Dir = "/var/cvs/pear";

/**
* DSN for pear packages database
*/
$dsn = "pgsql://pear:pear@localhost/pear";


PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pear_error');

if (DB::isError($db = DB::connect($dsn))) {
    die ("Couldn't open database -> $dsn\n");
}

if (!is_dir($TgzDir) || !is_writeable($TgzDir)) {
    die ("Couldn't use $TgzDir as target packages dir");
}

if (!($dir = opendir($PEAR_Dir))) {
    die ("Couldn't open Pear Dir: $PEAR_Dir");
}

$xml  = "< ?xml version=\"1.0\" encoding=\"ISO-8859-1\" ? >\n";
$xml .= "<Packages>\n";

while ($file = readdir($dir)) {
    if (!strstr($file, '.') && !strstr($file, 'CVS') )
    {
        chdir ($PEAR_Dir."/".$file);
        if (!file_exists("package.xml")) {
            continue;
        }

        /**
        * Build the package release
        */
        $packager =& new PEAR_Packager();
        $packager->debug = 0;
        if (PEAR::isError($packager->Package())) {
            echo "Reading of {$PEAR_Dir}/package.xml failed!\n";
            chdir ('..');
            continue;
        }

        $xml .=
        "<Package>\n".
        "    <Name>".$packager->pkginfo['Package,Name']."</Name>\n".
        "    <Summary>".$packager->pkginfo['Package,Summary']."</Summary>\n".
        "    <Release>\n".
        "        <Version>".$packager->pkginfo['Release,Version']."</Version>\n".
        "        <Date>".$packager->pkginfo['Release,Date']."</Date>\n".
        "        <Notes>".$packager->pkginfo['Release,Notes']."</Notes>\n".
        "    </Release>\n".
        "</Package>\n";

        /**
         * Look if there is already an entry for the author
         */
         $sth = $db->query("SELECT handle
                            FROM users
                            WHERE handle = lcase('".$packager->pkginfo['Maintainer,Initials']."')");

         if (!DB::isError($sth) && ($sth->numRows() == 0)) {

            $query = sprintf("INSERT INTO users
                              (handle,name,email,created,createdby)
                              VALUES ('%s','%s','%s','%s','%s')",
                             strtolower($packager->pkginfo['Maintainer,Initials']),
                             $packager->pkginfo['Maintainer,Name'],
                             $packager->pkginfo['Maintainer,EMail'],
                             date('Y-m-d H:i:s'),
                             'mj'   //who should be createby?
                             );

            $db->query($query);
         }

        /**
         * Store information for new packages
         */
        $sth = $db->query("SELECT name
                           FROM packages
                           WHERE name = '".$packager->pkginfo['Package,Name']."'");

        if (!DB::isError($sth) && ($sth->numRows() == 0)) {

            $query = sprintf("INSERT INTO packages
                              (name,summary)
                              VALUES ('%s','%s')",
                             $packager->pkginfo['Package,Name'],
                             $packager->pkginfo['Package,Summary']
                             );

            $db->query($query);

            $query = sprintf("INSERT INTO maintains
                              (handle,package)
                              VALUES ('%s','%s')",
                             strtolower($packager->pkginfo['Maintainer,Initials']),
                             $packager->pkginfo['Package,Name']
                             );

            $db->query($query);
        }

        /**
         * Store information about the current release
         */
        $sth = $db->query("SELECT package
                           FROM  releases
                           WHERE package = '".$packager->pkginfo['Package,Name']."'
                           AND   version = '".$packager->pkginfo['Release,Version']."'"
                         );

        if (!DB::isError($sth) && ($sth->numRows() == 0)) {

            $query = sprintf("INSERT INTO releases
                              (package,version,releasedate,releasenotes,doneby)
                              VALUES ('%s','%s','%s','%s','%s')",
                             $packager->pkginfo['Package,Name'],
                             $packager->pkginfo['Release,Version'],
                             $packager->pkginfo['Release,Date'],
                             $packager->pkginfo['Release,Notes'],
                             'mj'  //who should be doneby?
                             );

            $db->query($query);
        }

        /**
         * Move the tgz file to the new destination
         */
        rename($PEAR_Dir.'/'.$file.'/'.$packager->pkgver.'.tgz',
               $TgzDir.'/'.$packager->pkgver.'.tgz');

        chdir ('..');
    }
}
closedir($dir);

$xml .= "</Packages>\n";
$fxml = fopen($TgzDir.'/Packages.xml', 'w');

fwrite($fxml, $xml);
fclose($fxml);

if (function_exists('gzopen')) {
    $fxml = gzopen($TgzDir.'/Packages.xml.gz', 'wb');
    gzwrite($fxml, $xml);
    gzclose($fxml);
}

function &pear_error($obj) {
    echo $obj->getMessage() . "\n" . $obj->getDebugInfo(). "\n";
    return null;
}
?>
