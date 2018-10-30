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
  | Authors: Greg Beaver <cellog@php.net>                                |
  +----------------------------------------------------------------------+
*/

/**
 * Generate static REST files for pecl.php.net from existing data
 */

/**
 * Useful files to have
 */
set_include_path(__DIR__ . '/include' . PATH_SEPARATOR . get_include_path());
ob_start();
require_once "pear-config.php";
if ($_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME) {
    error_reporting(E_ALL);
    define('DEVBOX', true);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
    define('DEVBOX', false);
}

require_once "PEAR.php";

include_once "pear-database.php";
include_once __DIR__.'/src/Rest.php';
include_once "DB.php";
include_once "DB/storage.php";

if (empty($dbh)) {
    $options = [
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    ];
    $dbh = DB::connect(PEAR_DATABASE_DSN, $options);
    $dbh->query('SET NAMES utf8');
}

if (!isset($rest)) {
    if (isset($_SERVER['argv']) && $_SERVER['argv'][1] == 'pecl') {
        $restDir = PEAR_REST_DIR;
    } else {
        $restDir = __DIR__.DIRECTORY_SEPARATOR.'public_html'.DIRECTORY_SEPARATOR.'rest';
    }

    $rest = new Rest($restDir, $dbh);
}

ob_end_clean();
PEAR::setErrorHandling(PEAR_ERROR_DIE);
require_once 'System.php';
System::rm(['-r', $restDir]);
System::mkdir(['-p', $restDir]);
chmod($restDir, 0777);
echo "Generating Category REST...\n";
foreach (Category::listAll() as $category) {
    echo "  $category[name]...";
    $rest->saveCategory($category['name']);
    echo "done\n";
}
$rest->saveAllCategories();
echo "Generating Maintainer REST...\n";
$maintainers = $dbh->getAll('SELECT * FROM users', [], DB_FETCHMODE_ASSOC);
foreach ($maintainers as $maintainer) {
    echo "  $maintainer[handle]...";
    $rest->saveMaintainer($maintainer['handle']);
    echo "done\n";
}
echo "Generating All Maintainers REST...\n";
$rest->saveAllMaintainers();
echo "done\n";
echo "Generating Package REST...\n";
$rest->saveAllPackages();
require_once 'Archive/Tar.php';
require_once 'PEAR/PackageFile.php';
$config = PEAR_Config::singleton();
$pkg = new PEAR_PackageFile($config);
foreach (Package::listAll(false, false, false) as $package => $info) {
    echo "  $package\n";
    $rest->savePackage($package);
    echo "     Maintainers...";
    $rest->savePackageMaintainer($package);
    echo "...done\n";
    $releases = Package::info($package, 'releases');
    if ($releases) {
        echo "     Processing All Releases...";
        $rest->saveAllReleases($package);
        echo "done\n";
        foreach ($releases as $version => $blah) {
            $fileinfo = $dbh->getOne('SELECT fullpath FROM files WHERE release = ?',
                [$blah['id']]);
            if (!$fileinfo) {
                echo "     Skipping INVALID Version $version (corrupt in database!)\n";
                continue;
            }
            $tar = new Archive_Tar($fileinfo);
            if ($pxml = $tar->extractInString('package2.xml')) {
            } elseif ($pxml = $tar->extractInString('package.xml'));
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
            $pf = $pkg->fromAnyFile($fileinfo, PEAR_VALIDATE_NORMAL);
            PEAR::popErrorHandling();
            if (!PEAR::isError($pf)) {
                echo "     Version $version...";
                $rest->saveRelease($fileinfo, $pxml, $pf, $blah['doneby'],
                    $blah['id']);
                echo "done\n";
            } else {
                echo "     Skipping INVALID Version $version\n";
            }
        }
        echo "\n";
    } else {
        echo "  done\n";
    }
}
echo "Generating Category Package REST...\n";
foreach (Category::listAll() as $category) {
    echo "  $category[name]...";
    $rest->savePackagesCategory($category['name']);
    echo "done\n";
}
