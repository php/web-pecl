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
   $Id$
*/

if (auth_verify(@$_POST['PEAR_USER'], @$_POST['PEAR_PW'])) {
    if (!empty($_POST['PEAR_PERSIST'])) {
        $expire = 2147483647;
    } else {
        $expire = 0;
    }
    setcookie('PEAR_USER', $_POST['PEAR_USER'], $expire, '/');
    setcookie('PEAR_PW', md5($_POST['PEAR_PW']), $expire, '/');

	/**
    * Update users password if it is held in the db
	* crypt()ed.
    */
	if (strlen(@$auth_user->password) == 13) { // $auth_user comes from auth_verify() function
		$dbh->query(sprintf("UPDATE users SET password = '%s' WHERE handle = '%s'", md5($_POST['PEAR_PW']), $_POST['PEAR_USER']));
	}
	
	/**
    * Determine URL
    */
    if (isset($_POST['PEAR_OLDURL'])) {
        $gotourl = $_POST['PEAR_OLDURL'];
    } else {
        $gotourl = '/';
    }

	localRedirect($gotourl);
}

auth_reject();

?>
