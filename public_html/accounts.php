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

use App\Repository\UserRepository;

require_once __DIR__.'/../include/pear-prepend.php';

$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : null;
$letter = isset($_GET['letter']) ? strip_tags($_GET['letter']) : null;

$pageSize = 20;

$userRepository = new UserRepository($database);
$allFirstLetters = $userRepository->getFirstLetters();

$firstLetterOffsets = [];
for ($i = 0; $i < count($allFirstLetters); $i++) {
    $currentLetter = $allFirstLetters[$i];

    if (isset($firstLetterOffsets[$currentLetter])) {
        continue;
    }

    $firstLetterOffsets[$currentLetter] = $i;
}

if (preg_match('/^[a-z]$/i', $letter)) {
    $offset = $firstLetterOffsets[$letter];
    $offset -= $offset % $pageSize;
}

settype($offset, 'integer');

$last = $offset - $pageSize;
$lastLink = $_SERVER['PHP_SELF']."?offset=$last";
$next = $offset + $pageSize;
$nextLink = $_SERVER['PHP_SELF']."?offset=$next";

$goUrl = 'http://'.$_SERVER['SERVER_NAME'];

if ($_SERVER['SERVER_PORT'] != 80) {
    $goUrl .= ':'.$_SERVER['SERVER_PORT'];
}

$goUrl .= '/user/';

echo $template->render('pages/accounts.php', [
    'offset' => $offset,
    'lastLink' => $lastLink,
    'pageSize' => $pageSize,
    'letters' => array_unique($allFirstLetters),
    'firstLetterOffsets' => $firstLetterOffsets,
    'usersCount' => $userRepository->getUsersCount(),
    'goUrl' => $goUrl,
    'nextLink' => $nextLink,
    'users' => $userRepository->findAllUsersByOffset($pageSize, $offset),
]);
