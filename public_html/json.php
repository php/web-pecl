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
  | Authors: Pierre Joye <pierre@php.net>                                |
  +----------------------------------------------------------------------+
*/

use App\Repository\PackageRepository;

// Only support package maintainer for now, needed for bugs.php.net
$packageIdOrName = filter_input(INPUT_GET, 'package', FILTER_SANITIZE_STRING);

if (!$packageIdOrName) {
    header('HTTP/1.0 404 Not Found');
    echo "Package $packageIdOrName not found";

    exit();
}

// Package data
$package = $packageEntity->info($packageIdOrName);

if (!$package || !isset($package['packageid'])) {
    header("HTTP/1.0 404 Not Found");
    echo "Package $packageIdOrName not found";

    exit();
}

$packageRepository = new PackageRepository($database);

$maintainers = [];
foreach ($packageRepository->getMaintainersByPackageId($package['packageid']) as $maintainer) {
    $maintainers[] = $maintainer['handle'];
}

echo json_encode($maintainers);
