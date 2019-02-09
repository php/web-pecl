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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

/**
 * Interface to delete a package.
 *
 * TODO: Implement backup functionality.
 */

use App\Auth;
use App\Repository\PackageRepository;
use App\Rest;

require_once __DIR__.'/../include/pear-prepend.php';

$container->get(Auth::class)->secure(true);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo $template->render('error.php', [
        'errors' => ['No package ID specified.'],
    ]);

    exit;
}

$packageRepository = $container->get(PackageRepository::class);
$packageName = $packageRepository->find($_GET['id'], 'name');
$content = '';

if (!empty($_POST['confirm']) && 'yes' === $_POST['confirm']) {
    $removedFiles = 0;

    $sql = "SELECT p.name, r.version
            FROM packages p, releases r
            WHERE p.id = r.package AND r.package = :id";

    foreach ($database->run($sql, [':id' => $_GET['id']])->fetchAll() as $value) {
        $file = sprintf("%s/%s-%s.tgz",
                        $container->get('packages_dir'),
                        $value['name'],
                        $value['version']);

        if (@unlink($file)) {
            $content .= 'Deleting release archive "'.$file."\"\n";
            $removedFiles++;
        } else {
            $content .= '<div style="color:#ff0000">Unable to delete file '.$file.'</div>';
        }
    }

    $content .= "\n".$removedFiles." file(s) deleted\n\n";

    $categoryId = $packageRepository->find($_GET['id'], 'categoryid');
    $database->run("UPDATE categories SET npackages = npackages-1 WHERE id=?", [$categoryId]);

    $tables = [
        'releases'  => 'package',
        'maintains' => 'package',
        'deps'      => 'package',
        'files'     => 'package',
        'packages'  => 'id'
    ];

    foreach ($tables as $table => $column) {
        $content .= 'Removing package information from table "'.$table.'": ';

        $sql = "DELETE FROM $table WHERE $column = :id";
        $statement = $database->run($sql, [':id' => $_GET['id']]);

        $content .= '<b>'.$statement->rowCount()."</b> rows affected.\n";
    }

    $rest = $container->get(Rest::class);
    $rest->deletePackage($packageName);
    $rest->savePackagesCategory($packageRepository->find($_GET['id'], 'category'));
}

echo $template->render('pages/package_delete.php', [
    'content' => isset($content) ? $content : '',
    'packageName' => $packageName,
]);
