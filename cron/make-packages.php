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
 * Directory where the PEAR packages reside
 * @var string
 */
$PEAR_Dir = (isset($argv[1])) ? $argv[1] : '/var/cvs/pear';
if ($PEAR_Dir{0} != '/') {
    die ('Only absolute paths allowed');
} elseif (!@is_dir($PEAR_Dir)) {
    die ("Pear dir: $PEAR_Dir is not valid");
}
/**
 * Directory where to store the TGZ files
 * @var string
 */
$TgzDir = (isset($argv[2])) ? $argv[2] : '/var/www/pear/download';
if ($TgzDir{0} != '/') {
    die ('Only absolute paths allowed');
} elseif (!is_dir($TgzDir) || !is_writeable($TgzDir)) {
    die ("Pear dir: $TgzDir is not valid");
}

/**
* DSN for pear packages database
*/
$dsn = "mysql://pear:pear@localhost/pear";


PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pear_error');

if (DB::isError($db = DB::connect($dsn))) {
    die ("Couldn't open database -> $dsn\n");
}

if (!($dir = opendir($PEAR_Dir))) {
    die ("Couldn't open Pear Dir: $PEAR_Dir");
}

$xml  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n";
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
        $pack_name = $packager->Package();
        if (PEAR::isError($pack_name)) {
            echo "Reading of {$PEAR_Dir}/package.xml failed!\n".$pack_name->getMessage();
            chdir ('..');
            continue;
        }

        $xml .=
        "   <Package>\n".
        "       <Name>".$packager->pkginfo['package']."</Name>\n".
        "       <Summary>".$packager->pkginfo['summary']."</Summary>\n".
        "       <Release>\n".
        "           <Version>".$packager->pkginfo['version']."</Version>\n".
        "           <Date>".$packager->pkginfo['release_date']."</Date>\n".
        "           <Notes>".$packager->pkginfo['release_notes']."</Notes>\n".
        "       </Release>\n".
        "   </Package>\n";

        /**
         * Look if there is already an entry for the author
         */
         $sth = $db->query("SELECT handle
                            FROM users
                            WHERE handle = lcase('".$packager->pkginfo['maintainer_handle']."')");

         if (!DB::isError($sth) && ($sth->numRows() == 0)) {

            $query = sprintf("INSERT INTO users
                              (handle,name,email,created,createdby)
                              VALUES ('%s','%s','%s','%s','%s')",
                             strtolower($packager->pkginfo['maintainer_handle']),
                             $packager->pkginfo['maintainer_name'],
                             $packager->pkginfo['maintainer_email'],
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
                           WHERE name = '".$packager->pkginfo['package']."'");

        if (!DB::isError($sth) && ($sth->numRows() == 0)) {
            $package_id =    $db->nextID('packages');
            $query = sprintf("INSERT INTO packages
                              (name,summary,id)
                              VALUES ('%s','%s','%s')",
                             $packager->pkginfo['package'],
                             $packager->pkginfo['summary'],
                             $package_id
                             );

            $db->query($query);

            $query = sprintf("INSERT INTO maintains
                              (handle,package)
                              VALUES ('%s','%s')",
                             strtolower($packager->pkginfo['maintainer_handle']),
                             $package_id
                             );

            $db->query($query);
        }

        /**
         * Store information about the current release
         */
        $sth = $db->query("SELECT package
                           FROM  releases
                           WHERE package = '".$packager->pkginfo['package']."'
                           AND   version = '".$packager->pkginfo['version']."'"
                         );

        if (!DB::isError($sth) && ($sth->numRows() == 0)) {

            $query = sprintf("INSERT INTO releases
                              (id,package,version,releasedate,releasenotes,doneby)
                              VALUES ('%s','%s','%s','%s','%s','%s')",
                              $db->nextID('releases'),
                             $packager->pkginfo['package'],
                             $packager->pkginfo['version'],
                             $packager->pkginfo['release_date'],
                             $packager->pkginfo['release_notes'],
                             'mj'  //who should be doneby?
                             );

            $db->query($query);
        }

        /**
         * Move the tgz file to the new destination
         */
         $dest = $TgzDir.'/'.basename($pack_name);
         copy($pack_name, $dest);
         unlink($pack_name);

        chdir ('..');
    }
}
closedir($dir);

$xml .= "</Packages>\n";
$fxml = fopen($TgzDir.'/Packages.xml', 'w');

fwrite($fxml, $xml);
fclose($fxml);

function &pear_error($obj) {
    echo $obj->getMessage() . "\n" . $obj->getDebugInfo(). "\n";
    return null;
}
?>
