<?php
/*
 * +----------------------------------------------------------------------+
 * | PEAR Web site version 1.0                                            |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2003 The PEAR Group                                    |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.02 of the PHP license,      |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available at through the world-wide-web at                           |
 * | http://www.php.net/license/2_02.txt.                                 |
 * | If you did not receive a copy of the PHP license and are unable to   |
 * | obtain it through the world-wide-web, please send a note to          |
 * | license@php.net so we can mail you a copy immediately.               |
 * +----------------------------------------------------------------------+
 * | Authors: Martin Jansen <mj@php.net>                                  |
 * +----------------------------------------------------------------------+
 *
 * $Id$
 */

/**
 * Trying to find documentation URLs for PEAR packages in the peardoc Docbook sources
 *
 * @version $Revision$
 */
require_once "PEAR.php";
require_once "VFS.php";
require_once "VFS/file.php";
require_once "HTTP/Request.php";

require_once "DB.php";

$basepath = "/home/mj/cvs/peardoc/en/package/";

$vfs = new VFS_file(array("vfsroot" => $basepath));

$dbh = DB::connect("mysql://pear:pear@localhost/pear");
if (DB::isError($dbh)) {
    exit(1);
}

$update = $dbh->prepare("UPDATE packages SET doc_link = ? WHERE name = ?");

// {{{ readFolder()

function readFolder($folder) {
    global $vfs, $basepath, $dbh, $update;

    static $level;
    $level++;

    $result = $vfs->listFolder($folder);

    if ($folder == ".") {
        $folder = "";
    }

    foreach ($result as $file) {
       if (is_dir($basepath . $folder . "/" . $file['name'])) {
            if ($folder == "") {
                $newfolder = $file['name'];
            } else {
                $newfolder = $folder . "/" . $file['name'];
            }
            readFolder($newfolder);
            $level--;
        } else {
            if ($level == 2 && preg_match("/\.xml$/", $file['name'])) {
                
                $path = $basepath . $folder . "/" . $file['name'];
                $content = file_get_contents($path);

                preg_match("/<title>(.*)<\/title>/", $content, $matches1);
                preg_match("/<sect1 id\=\"(.*)\">/", $content, $matches2);

                $url = "/manual/en/" . $matches2[1] . ".php";

                $a = &new HTTP_Request("http://pear.php.net" . $url);
                $a->sendRequest();

                if ($a->getResponseCode() == 404) {
                    $new_url = preg_replace("=\.([^\.]+)\.php$=", ".php", $url);

                    $a->reset("http://pear.php.net/" . $new_url);

                    if ($a->getResponseCode() != 404) {
                        $url = $new_url;
                    } else {
                        $url = "";
                    }
                }

                $dbh->execute($update, array($url, $matches1[1]));
            }
        }
    }
}

// }}}

readFolder(".");
?>
