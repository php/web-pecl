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

/*
 * If the PHPSESSID cookie isn't set, the user MAY have cookies turned off.
 * To figure out cookies are REALLY off, check to see if the person came
 * from within the PEAR website or just submitted the login form.
 */
if (!isset($_COOKIE['PHPSESSID']) &&
    ((strpos(@$_SERVER['HTTP_REFERER'], @$_GET['redirect']) !== false) ||
     (isset($_POST['PEAR_USER']) && isset($_POST['PEAR_PW']))))
{
//    auth_reject(PEAR_AUTH_REALM, 'Cookies must be enabled to log in.');
}

/*
 * If they're already logged in, say so.
 */
if (isset($_COOKIE['PEAR_USER']) && isset($_COOKIE['PEAR_PW'])) {
    if (auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
        response_header('Login');
        echo '<div class="warnings">You are already logged in.</div>';
        response_footer();
        exit;
    }
}

if (auth_verify(@$_POST['PEAR_USER'], @$_POST['PEAR_PW'])) {
    if (!empty($_POST['PEAR_PERSIST'])) {
        $expire = 2147483647;
    } else {
        $expire = 0;
    }
    setcookie('PEAR_USER', $_POST['PEAR_USER'], $expire, '/');
    setcookie('PEAR_PW', md5($_POST['PEAR_PW']), $expire, '/');

    /*
     * Update users password if it is held in the db
     * crypt()ed.
     */
    if (strlen(@$auth_user->password) == 13) { // $auth_user comes from auth_verify() function
        $query = 'UPDATE users SET password = ? WHERE handle = ?';
        $dbh->query($query, array(md5($_POST['PEAR_PW']), $_POST['PEAR_USER']));
    }

    /*
     * Determine URL
     */
    if (isset($_POST['PEAR_OLDURL']) &&
        basename($_POST['PEAR_OLDURL']) != 'login.php')
    {
        localRedirect($_POST['PEAR_OLDURL']);
    } else {
        response_header('Login');
        report_success('Welcome.');
        response_footer();
        exit;
    }

    exit;
}

$msg = '';
if (isset($_POST['PEAR_USER']) || isset($_POST['PEAR_PW'])) {
    $msg = 'Invalid username or password.';
}

auth_reject(PEAR_AUTH_REALM, $msg);

?>
