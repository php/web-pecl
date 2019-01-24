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

if (empty($user)) {
    $user = isset($_GET['handle']) ? $_GET['handle'] : null;
}

if (empty($user)) {
    $user = basename($_SERVER['PATH_INFO']);
}

$userRepository = new UserRepository($database);

$wishlistUrl = $userRepository->getWishlistByHandle($user);

if (empty($wishlistUrl)) {
    header("HTTP/1.0 404 Not found");

    die("<h1>User not found</h1>\n");
}

header("Location: $wishlistUrl");

printf("<a href=\"%s\">click here to go to %s's wishlist</a>", $wishlistUrl, $user);
