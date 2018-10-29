<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
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

if (isset($showmsg)) {
    $delay = 3;
    header("Refresh: $delay; url=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\"");
    response_header("Logging Out...");

	$logoutmsg = "Authorization failed. Retry?";
    report_error("Press 'Cancel' when presented a new login box or ".
		 "one saying '$logoutmsg'<br />");
    response_footer();
} else {
    header("HTTP/1.0 401 Unauthorized");
    header("WWW-authenticate: basic realm=\"PEAR user\"");
    header("Refresh: 1; url=\"./\"");
    auth_reject(PEAR_AUTH_REALM, "Logging out");
}
