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

/*
 * If the PHPSESSID cookie isn't set, the user MAY have cookies turned off.
 * To figure out cookies are REALLY off, check to see if the person came
 * from within the PEAR website or just submitted the login form.
 */
if (!isset($_COOKIE[session_name()]) &&
    ((strpos(@$_SERVER['HTTP_REFERER'], @$_GET['redirect']) !== false) ||
     (isset($_POST['PEAR_USER']) && isset($_POST['PEAR_PW']))))
{
//    auth_reject(PEAR_AUTH_REALM, 'Cookies must be enabled to log in.');
}

/*
 * If they're already logged in, say so.
 */
if (!empty($auth_user)) {
	response_header('Login');
	echo '<div class="warnings">You are already logged in.</div>';
	response_footer();
	exit;
}

if (isset($_POST['PEAR_USER'], $_POST['PEAR_PW']) && auth_verify(@$_POST['PEAR_USER'], @$_POST['PEAR_PW'])) {
	if (!empty($_POST['PEAR_PERSIST'])) {
		setcookie('REMEMBER_ME', 1, 2147483647, '/');
		setcookie(session_name(), session_id(), 2147483647, '/');
	} else {
	    $expire = 0;
	    setcookie('REMEMBER_ME', 0, 2147483647, '/');
	    setcookie(session_name(), session_id(), null, '/');
	}

    $_SESSION['PEAR_USER'] = $_POST['PEAR_USER'];

    /*
     * Update users lastlogin
     */
    $query = 'UPDATE users SET lastlogin = NOW() WHERE handle = ?';
    $dbh->query($query, [$_POST['PEAR_USER']]);

    /*
     * Update users password if it is held in the db
     * crypt()ed.
     */
    if (strlen(@$auth_user->password) == 13) { // $auth_user comes from auth_verify() function
        $query = 'UPDATE users SET password = ? WHERE handle = ?';
        $dbh->query($query, [md5($_POST['PEAR_PW']), $_POST['PEAR_USER']]);
    }

    /*
     * Determine URL
     */
    if (isset($_POST['PEAR_OLDURL']) &&
        basename($_POST['PEAR_OLDURL']) != 'login.php')
    {
        localRedirect($_POST['PEAR_OLDURL']);
    } else {
	    localRedirect('index.php');
    }

    exit;
}

$msg = '';
if (isset($_POST['PEAR_USER']) || isset($_POST['PEAR_PW'])) {
    $msg = 'Invalid username or password.';
}

auth_reject(PEAR_AUTH_REALM, $msg);
