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

function auth_reject($realm = null, $message = null, $refresh = false)
{
    global $format;
    if ($realm === null) {
        $realm = PEAR_AUTH_REALM;
    }
    if ($message === null) {
        $message = "Please enter your username and password:";
    }

    response_header($message);
    if ($format == 'xmlrpc') {
        Header("HTTP/1.0 401 Unauthorized");
        Header("WWW-authenticate: basic realm=\"$realm\"");
        report_error($message);
    } elseif ($format == 'html') {
        $GLOBALS['ONLOAD'] = "document.login.PEAR_USER.focus();";
        report_error($message);
        print "<form name=\"login\" action=\"/login.php\" method=\"POST\">\n";
        print "<table>\n";
        print " <tr>\n";
        print "  <td>Username:</td>\n";
        print "  <td><input size=\"20\" name=\"PEAR_USER\"></td>\n";
        print " </tr>\n";
        print " <tr>\n";
        print "  <td>Password:</td>\n";
        print "  <td><input size=\"20\" name=\"PEAR_PW\" type=\"password\"></td>\n";
        print " </tr>\n";
        print " <tr>\n";
        print "  <td>&nbsp;</td>\n";
        print "  <td><input type=\"checkbox\" name=\"PEAR_PERSIST\" value=\"on\" id=\"pear_persist_chckbx\"> <label for=\"pear_persist_chckbx\">Remember username and password.</label></td>\n";
        print " </tr>\n";
        print " <tr>\n";
        print "  <td>&nbsp;</td>\n";
        print "  <td><input type=\"submit\" value=\"Log in!\"></td>\n";
        print " </tr>\n";
        print "</table>\n";
        print '<input type="hidden" name="PEAR_OLDURL" value="';
        if (basename($_SERVER['PHP_SELF']) == 'login.php') {
            print '/';
        } elseif (isset($_POST['PEAR_OLDURL'])) {
            print htmlspecialchars($_POST['PEAR_OLDURL']);
        } else {
            print htmlspecialchars($_SERVER['REQUEST_URI']);
        }
        print "\" />\n";
        print "</form>\n";
    }
    response_footer();
    exit;
}

function auth_verify($user, $passwd)
{
    global $dbh, $auth_user;

    if (empty($auth_user)) {
        $auth_user = new PEAR_User($dbh, $user);
    }
    $error = '';
    $ok = false;
    switch (strlen(@$auth_user->password)) {
        // handle old-style DES-encrypted passwords
        case 13: {
            $seed = substr($auth_user->password, 0, 2);
            $crypted = crypt($passwd, $seed);
            if ($crypted == @$auth_user->password) {
                $ok = true;
            } else {
                $error = "pear-auth: user `$user': invalid password (des)";
            }
            break;
        }
        // handle new-style MD5-encrypted passwords
        case 32: {
			// Check if the passwd is already md5()ed
			if (preg_match('/^[a-z0-9]{32}$/', $passwd)) {
				$crypted = $passwd;
			} else {
				$crypted = md5($passwd);
			}
            
            if ($crypted == @$auth_user->password) {
                $ok = true;
            } else {
                $error = "pear-auth: user `$user': invalid password (md5)";
            }
            break;
        }
    }
    if (empty($auth_user->registered)) {
        if ($user) {
            $error = "pear-auth: user `$user' not registered";
        }
        $ok = false;
    }
    if ($ok) {
        $auth_user->_readonly = true;
        return true;
    }
    if ($error) {
        error_log($error, 0);
    }
    $auth_user = null;
    return false;
}

function auth_require($admin = false, $refresh = false)
{
    global $auth_user;

    $user = @$_COOKIE['PEAR_USER'];
    $passwd = @$_COOKIE['PEAR_PW'];
    if (!auth_verify($user, $passwd)) {
        auth_reject(null, null, $refresh); // exits
    }
    if ($admin && empty($auth_user->admin)) {
        response_header("Insufficient Privileges");
        report_error("Insufficient Privileges");
        response_footer();
        exit;
    }
    return true;
}

/**
 * Perform logout for the current user
 */
function auth_logout()
{
    if (isset($_COOKIE['PEAR_USER'])) {
        setcookie('PEAR_USER', '', 0, '/');
        unset($_COOKIE['PEAR_USER']);
    }
    if (isset($_COOKIE['PEAR_PW'])) {
        setcookie('PEAR_PW', '', 0, '/');
        unset($_COOKIE['PEAR_PW']);
    }
}

$cvspasswd_file = "/repository/CVSROOT/passwd";

function cvs_find_password($user)
{
    global $cvspasswd_file;
    $fp = fopen($cvspasswd_file,"r");
    while ($line = fgets($fp, 120)) {
        list($luser, $passwd, $groups) = explode(":", $line);
        if ($user == $luser) {
            fclose($fp);
            return $passwd;
        }
    }
    fclose($fp);
    return false;
}

function cvs_verify_password($user, $pass)
{
    $psw = cvs_find_password($user);
    if (strlen($psw) > 0) {
        if (crypt($pass,substr($psw,0,2)) == $psw) {
            return true;
        }
    }
    return false;
}

/*
* setup the $auth_user object
*/
function init_auth_user()
{
    global $auth_user, $dbh;
    if (empty($_COOKIE['PEAR_USER']) || empty($_COOKIE['PEAR_PW'])) {
        $auth_user = null;
        return false;
    }
    if (!empty($auth_user)) {
        return true;
    }
    $auth_user = new PEAR_User($dbh, $_COOKIE['PEAR_USER']);
    switch (strlen(@$auth_user->password)) {
        // handle old-style DES-encrypted passwords
        case 13: {
            $seed = substr($auth_user->password, 0, 2);
            if (crypt($_COOKIE['PEAR_PW'], $seed) == @$auth_user->password) {
                return true;
            }
            break;
        }
        // handle new-style MD5-encrypted passwords
        case 32: {
            if (md5($_COOKIE['PEAR_PW']) == @$auth_user->password) {
                return true;
            }
            break;
        }
    }
    $auth_user = null;
    return false;
}
?>