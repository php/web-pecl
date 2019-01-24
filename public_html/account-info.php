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
 * Details about PECL account.
 */

use App\Repository\CvsAclRepository;
use App\Repository\NoteRepository;
use App\Repository\PackageRepository;
use App\Repository\UserRepository;

require_once __DIR__.'/../include/pear-prepend.php';

$handle = filter_input(INPUT_GET, 'handle', FILTER_SANITIZE_STRING);

// Redirect to the accounts list if no handle was specified
if (empty($handle)) {
    localRedirect('/accounts.php');
}

$userRepository = new UserRepository($database);
$user = $userRepository->findActiveByHandle($handle);

if (!$user) {
    header('HTTP/1.0 404 Not Found');
    PEAR::raiseError('No account information found!');
}

// Add missing URL scheme to homepage link
if (!empty($user['homepage']) && empty(parse_url($user['homepage'], PHP_URL_SCHEME))) {
    $user['homepage'] = 'http://'.$user['homepage'];
}

$cvsAclRepository = new CvsAclRepository($database);
$packageRepository = new PackageRepository($database);
$noteRepository = new NoteRepository($database);

echo $template->render('pages/account_info.php', [
    'user'     => $user,
    'access'   => $cvsAclRepository->getPathByUsername($handle),
    'packages' => $packageRepository->findPackagesMaintainedByHandle($handle),
    'notes'    => $noteRepository->getNotesByUser($handle),
]);
