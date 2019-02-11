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
  |          Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

use App\Repository\CategoryRepository;
use App\Repository\PackageRepository;
use App\Repository\PackageStatsRepository;
use App\Repository\ReleaseRepository;

require_once __DIR__.'/../include/pear-prepend.php';

$packageRepository = $container->get(PackageRepository::class);

$_GET['cid'] = isset($_GET['cid']) ? (int) $_GET['cid'] : 0;
$_GET['pid'] = isset($_GET['pid']) ? (int) $_GET['pid'] : 0;
$_GET['rid'] = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;

if (!empty($_GET['cid'])) {
    $categoryName     = $database->run("SELECT name FROM categories WHERE id = ?", [$_GET['cid']])->fetch()['name'];
    $totalPackages    = $database->run("SELECT COUNT(DISTINCT pid) AS count FROM package_stats WHERE cid = ?", [$_GET['cid']])->fetch()['count'];
    $totalMaintainers = $database->run("SELECT COUNT(DISTINCT m.handle) AS count FROM maintains m, packages p WHERE m.package = p.id AND p.category = ?", [$_GET['cid']])->fetch()['count'];
    $totalReleases    = $database->run("SELECT COUNT(*) AS count FROM package_stats WHERE cid = ?", [$_GET['cid']])->fetch()['count'];
    $totalCategories  = $database->run("SELECT COUNT(*) AS count FROM categories WHERE parent = ?", [$_GET['cid']])->fetch()['count'];

    // Query to get package list from package_stats_table
    $sql = "SELECT SUM(ps.dl_number) AS dl_number, ps.package, ps.release, ps.pid, ps.rid, ps.cid
            FROM package_stats ps, packages p
            WHERE p.package_type = 'pecl' AND p.id = ps.pid AND p.category = ?
            GROUP BY ps.pid ORDER BY ps.dl_number DESC
    ";
    $results = $database->run($sql, [$_GET['cid']])->fetchAll();
} else {
    $totalPackages    = number_format($database->run('SELECT COUNT(id) AS count FROM packages WHERE package_type="pecl"')->fetch()['count'], 0, '.', ',');
    $totalMaintainers = number_format($database->run('SELECT COUNT(DISTINCT handle) AS count FROM maintains')->fetch()['count'], 0, '.', ',');
    $totalReleases    = number_format($database->run('SELECT COUNT(*) AS count FROM releases r, packages p
                    WHERE r.package = p.id AND p.package_type="pecl"')->fetch()['count'], 0, '.', ',');
    $totalCategories  = number_format($database->run('SELECT COUNT(*) AS count FROM categories')->fetch()['count'], 0, '.', ',');
    $totalDownloads   = number_format($database->run('SELECT SUM(dl_number) AS downloads FROM package_stats, packages p
                    WHERE package_stats.pid = p.id AND p.package_type="pecl"')->fetch()['downloads'], 0, '.', ',');

    $sql = "SELECT sum(ps.dl_number) as dl_number, ps.package, ps.pid, ps.rid, ps.cid
            FROM package_stats ps, packages p
            WHERE p.id = ps.pid AND p.package_type = 'pecl'
            GROUP BY ps.pid ORDER BY dl_number DESC
    ";
    $results = $database->run($sql)->fetchAll();
}

echo $template->render('pages/package_stats.php', [
    'categories' => $container->get(CategoryRepository::class)->findAll(),
    'packages' => $packageRepository->findAllByCategory($_GET['cid']),
    'releases' => $container->get(ReleaseRepository::class)->findByPackageId($_GET['pid']),
    'info' => !empty($_GET['pid']) ? $packageRepository->find($_GET['pid'], null) : null,
    'totalPackages' => isset($totalPackages) ? $totalPackages : null,
    'totalCategories' => isset($totalCategories) ? $totalCategories : null,
    'totalDownloads' => isset($totalDownloads) ? $totalDownloads : null,
    'totalReleases' => isset($totalReleases) ? $totalReleases : null,
    'totalMaintainers' => isset($totalMaintainers) ? $totalMaintainers : null,
    'categoryName' => isset($categoryName) ? $categoryName : null,
    'results' => isset($results) ? $results : null,
    'packageStatsRepository' => $container->get(PackageStatsRepository::class),
]);
