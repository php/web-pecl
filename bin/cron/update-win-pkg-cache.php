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
  | Authors: Anatol Belski <ab@php.net>                                  |
  +----------------------------------------------------------------------+
*/

use App\PackageDll;
use \DB as DB;

require_once __DIR__.'/../../include/bootstrap.php';

$packageDll = new PackageDll($config->get('tmp_dir'));

$sql = "SELECT packages.name, releases.version, releases.releasedate
        FROM packages, releases
        WHERE packages.id = releases.package";

$data = $database->run($sql)->fetchAll();

if ($packageDll->isResetOverdue()) {
    if (!$packageDll->resetDllDownloadCache()) {
        // some reset running, just sleep and do our thing
        sleep(10);
    }
}

foreach ($data as $pkg) {
    if (!$packageDll->updateDllDownloadCache($pkg['name'], $pkg['version'])) {
        echo "Failed to update cache for $pkg[name]-$pkg[version]\n";
    }
}
