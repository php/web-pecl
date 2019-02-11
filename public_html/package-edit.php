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
 * Interface to update package information.
 */

use App\Auth;
use App\Release;
use App\Repository\PackageRepository;
use App\Rest;
use App\User;

require_once __DIR__.'/../include/pear-prepend.php';

$container->get(Auth::class)->secure();

if (!isset($_GET['id'])) {
    echo $template->render('error.php', [
        'errors' => ['No package ID specified.'],
    ]);

    exit;
}

// The user has to be either a lead developer of the package or a PECL admin.
if (
    !User::maintains($container->get('auth_user')->handle, $_GET['id'], 'lead')
    && !$container->get('auth_user')->isAdmin()
) {
    echo $template->render('error.php', [
        'errors' => ['Only the lead maintainer of the package or PECL administrators can edit the package.'],
    ]);

    exit;
}

$packageRepository = $container->get(PackageRepository::class);
$content = '';

if (isset($_POST['submit'])) {
    if (!$_POST['name'] || !$_POST['license'] || !$_POST['summary']) {
        echo $template->render('error.php', [
            'errors' => ['You have to enter values for name, license and summary!'],
        ]);

        exit;
    }

    if (!empty($_POST['newpk_id'])) {
        $_POST['new_channel'] = 'pecl.php.net';
        $_POST['new_package'] = $database->run('SELECT name from packages WHERE id = ?', [$_POST['newpk_id']])->fetch('name');

        if (!$_POST['new_package']) {
            $_POST['new_channel'] = $_POST['newpk_id'] = null;
        }
    } else {
        if ('pecl.php.net' === $_POST['new_channel']) {
            $_POST['newpk_id'] = $database->run('SELECT id from packages WHERE name = ?', [$_POST['new_package']])->fetch()['id'];

            if (!$_POST['newpk_id']) {
                $_POST['new_channel'] = $_POST['new_package'] = null;
            }
        }
    }

    // Get current extension data from database
    $current = $database->run('SELECT name FROM packages WHERE id = ?', [$_GET['id']])->fetch();

    // Check if extension name has changed and is valid
    if (
        $current['name'] !== $_POST['name']
        && !preg_match($container->get('valid_extension_name_regex'), $_POST['name'])
    ) {
        echo $template->render('error.php', [
            'errors' => ['Invalid package name. PECL package names must start with a letter and preferably include only lowercase letters. Optionally, numbers and underscores are also allowed.'],
        ]);

        exit;
    }

    $sql = 'UPDATE packages
            SET
                name = ?,
                license = ?,
                summary = ?,
                description = ?,
                category = ?,
                homepage = ?,
                cvs_link = ?,
                doc_link = ?,
                bug_link = ?,
                unmaintained = ?,
                newpackagename = ?,
                newchannel = ?
            WHERE id = ?';

    $arguments = [
        $_POST['name'],
        $_POST['license'],
        $_POST['summary'],
        $_POST['description'],
        $_POST['category'],
        $_POST['homepage'],
        $_POST['cvs_link'],
        $_POST['doc_link'],
        $_POST['bug_link'],
        (isset($_POST['unmaintained']) ? 1 : 0),
        $_POST['new_package'],
        $_POST['new_channel'],
        $_GET['id']
    ];

    $database->run($sql, $arguments);

    $rest = $container->get(Rest::class);
    $rest->savePackage($_POST['name']);
    $rest->savePackagesCategory($packageRepository->find($_POST['name'], 'category'));
    $content .= 'Package information successfully updated.';
} elseif (isset($_GET['action']) && 'release_remove' === $_GET['action']) {
    if (!isset($_GET['release'])) {
        echo $template->render('error.php', [
            'errors' => ['Missing package ID!'],
        ]);

        exit;
    }

    if ($container->get(Release::class)->remove($_GET['id'], $_GET['release'])) {
        $content .= 'Release successfully deleted.';
    } else {
        echo $template->render('error.php', [
            'errors' => ['An error occured while deleting the release!'],
        ]);

        exit;
    }
}

$row = $packageRepository->find($_GET['id']);

if (empty($row['name'])) {
    echo $template->render('error.php', [
        'errors' => ['Illegal package id'],
    ]);

    exit;
}

echo $template->render('pages/package_edit.php', [
    'content' => $content,
    'row' => $row,
    'categories' => $database->run('SELECT id, name FROM categories ORDER BY name')->fetchAll(),
    'packages' => $database->run('SELECT name FROM packages WHERE package_type="pecl" ORDER BY name')->fetchAll(),
]);
