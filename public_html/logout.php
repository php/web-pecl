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
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
*/

if (isset($showmsg)) {
    $delay = 3;
    Header("Refresh: $delay; url=\"" . htmlspecialchars($_SERVER['PHP_SELF']) . "\"");
    response_header("Logging Out...");
//	$ua = $HTTP_USER_AGENT;
	$logoutmsg = "Authorization failed. Retry?";
    report_error("Press 'Cancel' when presented a new login box or ".
		 "one saying '$logoutmsg'<br />");
    response_footer();
} else {
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"PEAR user\"");
    Header("Refresh: 1; url=\"./\"");
    auth_reject(PEAR_AUTH_REALM, "Logging out");
}

?>
