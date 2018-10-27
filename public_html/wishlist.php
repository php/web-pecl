<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | https://php.net/license/2_02.txt.                                    |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
*/
if (empty($user)) {
    $user = @$_GET['handle'];
}
if (empty($user)) {
    $user = basename($_SERVER['PATH_INFO']);
}

PEAR::setErrorHandling(PEAR_ERROR_RETURN);
$url = $dbh->getOne('SELECT wishlist FROM users WHERE handle = ?',
                    [$user]);
if (empty($url) || PEAR::isError($url)) {
    header("HTTP/1.0 404 Not found");
    die("<h1>User not found</h1>\n");
}

header("Location: $url");

printf("<a href=\"%s\">click here to go to %s's wishlist</a>",
       $url,
       $user
       );
