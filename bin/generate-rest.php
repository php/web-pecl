#!/usr/bin/env php
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
 * Generate static REST files for PECL from existing data
 */

use App\Package;
use App\Repository\CategoryRepository;
use App\Repository\PackageRepository;
use App\Repository\UserRepository;
use \PEAR as PEAR;
use \PEAR_Config as PEAR_Config;
use \PEAR_PackageFile as PEAR_PackageFile;
use \Archive_Tar as Archive_Tar;

require_once __DIR__.'/../include/bootstrap.php';

$filesystem->delete($config->get('rest_dir'));
$categoryRepository = new CategoryRepository($database);
$packageRepository = new PackageRepository($database);
$userRepository = new UserRepository($database);

mkdir($config->get('rest_dir'), 0777, true);
chmod($config->get('rest_dir'), 0777);

echo "Generating Category REST...\n";

$categories = $categoryRepository->findAll();

foreach ($categories as $category) {
    echo "  $category[name]...";
    $rest->saveCategory($category['name']);
    echo "done\n";
}

$rest->saveAllCategories();

echo "Generating Maintainer REST...\n";

foreach ($userRepository->findAll() as $maintainer) {
    echo "  $maintainer[handle]...";
    $rest->saveMaintainer($maintainer['handle']);
    echo "done\n";
}

echo "Generating All Maintainers REST...\n";
$rest->saveAllMaintainers();
echo "done\n";

echo "Generating Package REST...\n";
$rest->saveAllPackages();

$pearConfig = PEAR_Config::singleton();
$pkg = new PEAR_PackageFile($pearConfig);

foreach ($packageRepository->listAll() as $package => $info) {
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

        foreach ($releases as $version => $release) {
            $sql = "SELECT fullpath FROM files WHERE `release` = :release_id";

            $statement = $database->run($sql, [':release_id' => $release['id']]);
            $result = $statement->fetch();

            $fileinfo = isset($result['fullpath']) ? $result['fullpath'] : [];

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
                $rest->saveRelease($fileinfo, $pxml, $pf, $release['doneby'], $release['id']);
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

foreach ($categories as $category) {
    echo "  $category[name]...";
    $rest->savePackagesCategory($category['name']);
    echo "done\n";
}
