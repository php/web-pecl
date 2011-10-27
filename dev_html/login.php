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
   $Id: login.php -1   $
*/

/*
 * If the PHPSESSID cookie isn't set, the user MAY have cookies turned off.
 * To figure out cookies are REALLY off, check to see if the person came
 * from within the PEAR website or just submitted the login form.
 */
if (!isset($_COOKIE[session_name()]) &&
    ((strpos(@$_SERVER['HTTP_REFERER'], @$_GET['redirect']) !== false) ||
     (isset($_POST['handle']) && isset($_POST['password']))))
{
//    auth_reject(PEAR_AUTH_REALM, 'Cookies must be enabled to log in.');
}

/*
 * If they're already logged in, say so.
 */
if (!empty($auth_user)) {
    $page = new PeclPage('/developer/page_developer.html');
    $page->title = 'PECL :: Login page';
    $page->contents = '<div class="warnings">You are already logged in.</div>';
    $page->render();
    echo $page->html;
	exit;
}

$user = filter_input(INPUT_POST, 'handle', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
$old_url = filter_input(INPUT_POST, 'old_url', FILTER_SANITIZE_STRING);

if ($user && $password && auth_verify($user, $password)) {
	if (!empty($_POST['PEAR_PERSIST'])) {
		setcookie('REMEMBER_ME', 1, 2147483647, '/');
		setcookie(session_name(), session_id(), 2147483647, '/');
	} else {
	    $expire = 0;
	    setcookie('REMEMBER_ME', 0, 2147483647, '/');
	    setcookie(session_name(), session_id(), null, '/');
	}

    $_SESSION['handle'] = $user;

    /*
     * Update users lastlogin
     */
    $query = 'UPDATE users SET lastlogin = NOW() WHERE handle = ?';
    $dbh->query($query, array($user));

    /*
     * Update users password if it is held in the db
     * crypt()ed.
     */
    if (strlen(@$auth_user->password) == 13) { // $auth_user comes from auth_verify() function
        $query = 'UPDATE users SET password = ? WHERE handle = ?';
        $dbh->query($query, array(md5($password), $user));
    }

    /*
     * Determine URL
     */
    if ($old_url &&
        basename($old_url) != 'login.php')
    {
        localRedirect($old_url);
    } else {
	    localRedirect('/');
    }

    exit;
}

$msg = '';
if ($user || $password) {
    $msg = 'Invalid username or password.';
}

auth_reject(PEAR_AUTH_REALM, $msg);
