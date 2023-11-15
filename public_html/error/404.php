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

use App\User;

require_once __DIR__.'/../../include/pear-prepend.php';

/**
 * This is the 404 error page. Requests will also search for a package with the
 * same name as the requested resource. Thus here are enabled also urls such as:
 * https://pecl.php.net/operator to get redirected to the package info page.
 */

// Requesting something like /~foobar will redirect to the account information
// page of the user "foobar".
if (strlen($_SERVER['REDIRECT_URL']) > 0 && $_SERVER['REDIRECT_URL'][1] == '~') {
    $user = substr($_SERVER['REDIRECT_URL'], 2);

    if (preg_match($container->get('valid_usernames_regex'), $user) && User::exists($user)) {
        header('Location: /user/'.urlencode($user));
        exit;
    }
}

$pkg = strtr($_SERVER['REDIRECT_URL'], '-','_');

// Check strictly
$name = $packageEntity->info(basename($pkg), 'name');
if (!empty($name)) {
    header('Location: /package/'.urlencode($name));
    exit;
}

// Check less strictly if nothing has been found previously
$sql = "SELECT p.id, p.name, p.summary
        FROM packages p
        WHERE package_type = 'pecl' AND approved = 1 AND name LIKE ?
        ORDER BY p.name";
$term = "%" . basename($pkg) . "%";
$packages = $database->run($sql, [$term])->fetchAll();

if (count($packages) > 3) {
    $packages = [$packages[0], $packages[1], $packages[2]];
    $showSearchLink = true;
} else {
    $showSearchLink = false;
}

echo $template->render('errors/404.php', [
    'packages' => $packages,
    'showSearchLink' => $showSearchLink,
]);
