<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Richard Heyes <richard@php.net> + others                    |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require(true);

if (empty($_GET['phpinfo'])) {
	response_header();
	echo '<iframe src="admin.phpinfo.php?phpinfo=1" width="820" height="380" frameborder="0">No IFRAME support!</iframe>';
	response_footer();
} else {
	phpinfo();
}

?>