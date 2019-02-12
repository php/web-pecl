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
  +----------------------------------------------------------------------+
*/

use App\Auth;
use App\Repository\UserRepository;
use App\Repository\PackageRepository;
use App\Rest;

require_once __DIR__.'/../../include/pear-prepend.php';

$userRepository = $container->get(UserRepository::class);

$id = isset($_GET['pid']) ? (int) $_GET['pid'] : 0;

$self = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES);

// Select package first
if (empty($id)) {
    $container->get(Auth::class)->secure(true);

    echo $template->render('pages/admin/maintainers/index.php', [
        'packages' => $container->get(PackageRepository::class)->findAllPeclPackages(),
    ]);

} elseif (!empty($_GET['update'])) {
    if (!isAllowed($id, $userRepository)) {
        echo $template->render('error.php', [
            'errors' => ['Only the lead maintainer of the package or PECL administrators can edit the maintainers.'],
        ]);

        exit;
    }

    $all = $userRepository->findMaintainersByPackageId($id);

    // Transform
    $newList = [];
    foreach ((array) $_GET['maintainers'] as $maintainer) {
        list($handle, $role) = explode("||", $maintainer);
        $newList[$handle] = $role;
    }

    $package = $database->run('SELECT name FROM packages WHERE id=?', [$id])->fetch()['name'];

    // Perform databases operations
    $sql = "SELECT role FROM maintains WHERE handle = ? AND package = ?";
    $check = $database->prepare($sql);

    $sql  = "INSERT INTO maintains VALUES (?, ?, ?, 1)";
    $insert = $database->prepare($sql);

    $sql  = "UPDATE maintains SET role = ? WHERE handle = ? AND package = ?";
    $update = $database->prepare($sql);

    $sql  = "DELETE FROM maintains WHERE handle = ? AND package = ?";
    $delete = $database->prepare($sql);

    $content = '';

    // In a first run, we delete all maintainers which are not in the new list.
    // This isn't the best solution, but for now it works.
    foreach ($all as $role) {
        if (isset($newList[$role['handle']])) {
            continue;
        }

        $content .= 'Deleting user <b>'.$role['handle'].'</b> ...<br>';

        $delete->execute([$role['handle'], $id]);
    }

    // Update/Insert existing maintainers
    foreach ($newList as $handle => $role) {
        $check->execute([$handle, $id]);

        $row = $check->fetch();
        if (!is_array($row)) {
            // Insert new maintainer
            $content .= 'Adding user <b>'.$handle.'</b> ...<br>';
            $insert->execute([$handle, $id, $role]);
        } else if ($role != $row['role']) {
            // Update role
            $content .= 'Updating user <b>'.$handle.'</b> ...<br>';
            $update->execute([$role, $handle, $id]);
        }
    }

    $container->get(Rest::class)->savePackageMaintainer($package);

    $url = $self;

    if (!empty($_GET['pid'])) {
        $url .= "?pid=".urlencode(strip_tags($_GET['pid']));
    }

    $content .= '<br><b>Done</b><br>';
    $content .= '<a href="'.$url.'">Back</a>';

    echo $template->render('pages/admin/maintainers/update.php', [
        'content' => $content,
    ]);
} else {
    if (!isAllowed($id, $userRepository)) {
        echo $template->render('error.php', [
            'errors' => ['Only the lead maintainer of the package or PECL administrators can edit the maintainers.'],
        ]);

        exit;
    }

    echo $template->render('pages/admin/maintainers/package.php', [
        'package' => $database->run('SELECT name FROM packages WHERE id=?', [$id])->fetch()['name'],
        'self' => $self,
        'id' => $id,
        'users' => $userRepository->findAll(),
        'maintainers' => $userRepository->findMaintainersByPackageId($id),
    ]);
}

function isAllowed($packageId, $userRepository)
{
    global $auth_user, $auth;

    $auth->secure();

    $lead = false;
    foreach ($userRepository->findLeadMaintainersByPackage($packageId) as $item) {
        if ($auth_user->handle === $item['handle']) {
            $lead = true;
            break;
        }
    }

    $admin = $auth_user->isAdmin();

    return ($lead || $admin);
}
