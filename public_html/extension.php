<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Martin Jansen <mj@php.net>                                  |
  |          Tomas V.V.Cox <cox@idecnet.com>                             |
  +----------------------------------------------------------------------+
*/

/**
 * Page for displaying given PECL extension information for *nix and Windows
 * builds.
 *
 * This front controller is included in package-info.php and
 * package-info-win.php files due to Apache configuration on production where
 * they can't be changed so often.
 */

use App\Utils\Licenser;
use App\PackageDll;
use App\Repository\PackageRepository;
use App\Repository\ReleaseRepository;
use App\Repository\UserRepository;
use App\Utils\Breadcrumbs;

require_once __DIR__.'/../include/pear-prepend.php';

$packageNameOrId = filter_has_var(INPUT_GET, 'package') ? filter_input(INPUT_GET, 'package', FILTER_SANITIZE_STRING) : '';
$version = filter_has_var(INPUT_GET, 'version') ? filter_input(INPUT_GET, 'version', FILTER_SANITIZE_STRING) : '';
$windows = isset($windows) ? true : false;

if (is_numeric($packageNameOrId)) {
    $packageNameOrId = (int) $packageNameOrId;
}

$packageRepository = $container->get(PackageRepository::class);
$package = $packageRepository->find($packageNameOrId);

if (
    '' === $packageNameOrId
    || !isset($package['name'])
    || ('' !== $version && !isset($package['releases'][$version]))
) {
    $_SERVER['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
    header('HTTP/1.0 404 Not Found');
    include __DIR__.'/error/404.php';
    exit();
}

if ('' !== $version && isset($package['releases'][$version])) {
    $releaseId = $package['releases'][$version]['id'];

    // Find correct version for the release id
    foreach ($package['releases'] as $release) {
        if ($release['id'] == $releaseId) {
            break;
        }
    }
} else {
    $releaseId = null;
    $release = null;
}

$packageDll = $container->get(PackageDll::class);

if ($windows && '' !== $version) {
    $urls = $packageDll->getDllDownloadUrls($package['name'], $version, $package['releases'][$version]['releasedate']);
} else {
    $urls = [];
}

$template->register('findPackage', function($name) use ($packageRepository) {
    return $packageRepository->findOneByName($name);
});

$template->register('makeNiceLinkNameFromZipName', function ($url) use ($packageDll) {
    return $packageDll->makeNiceLinkNameFromZipName(basename($url));
});

echo $template->render('pages/extension.php', [
    'package'      => $package,
    'version'      => $version,
    'breadcrumbs'  => $container->get(Breadcrumbs::class)->getBreadcrumbs($package['categoryid'], true),
    'releaseId'    => $releaseId,
    'maintainers'  => $container->get(UserRepository::class)->findMaintainersByPackageId($package['packageid']),
    'license'      => $container->get(Licenser::class)->getHtml($package['license']),
    'downloads'    => $container->get(ReleaseRepository::class)->findDownloads($package['packageid']),
    'unmaintained' => (bool) $package['unmaintained'],
    'superseded'   => !empty($package['new_channel']) ? true : (bool) $package['new_package'],
    'packageDll'   => $packageDll,
    'dependants'   => $packageRepository->findDependants($package['name']),
    'host'         => $container->get('host'),
    'windows'      => $windows,
    'release'      => $release,
    'urls'         => $urls,
]);
