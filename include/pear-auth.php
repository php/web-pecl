<?php

function auth_reject($realm = null, $message = null, $refresh = false)
{
    if ($realm === null) {
        $realm = PEAR_AUTH_REALM;
    }
    if ($message === null) {
        $message = "Login Failed!";
    }
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"$realm\"");
	if ($refresh) {
		Header("Refresh: 3; url=/");
	}
    response_header($message);
    report_error($message);
    response_footer();
    exit;
}

function auth_require($admin = false, $refresh = false)
{
    global $dbh, $auth_user;

    $user = @$_SERVER['PHP_AUTH_USER'];
	$passwd = @$_SERVER['PHP_AUTH_PW'];
    $auth_user = new PEAR_User($dbh, $user);
    $ok = false;
    switch (strlen(@$auth_user->password)) {
        // handle old-style DES-encrypted passwords
        case 13: {
            $seed = substr($auth_user->password, 0, 2);
			$crypted = crypt($_SERVER['PHP_AUTH_PW'], $seed);
            if ($crypted == @$auth_user->password) {
                $ok = true;
            }
            break;
        }
        // handle new-style MD5-encrypted passwords
        case 32: {
			$crypted = md5($_SERVER['PHP_AUTH_PW']);
            if ($crypted == @$auth_user->password) {
                $ok = true;
            }
            break;
        }
    }
    if (empty($auth_user->registered)) {
        $ok = false;
    }
    if (!$ok) {
        if (cvs_verify_password($user, $passwd)) {
            $auth_user = (object)array('handle' => $user);
        } else {
            auth_reject(null, null, $refresh);
        }
    }
    $auth_user->_readonly = true;
    if ($admin && empty($auth_user->admin)) {
        response_header("Insufficient Privileges");
        report_error("Insufficient Privileges");
        response_footer();
        exit;
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
    if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
        return false;
    }
    if (!empty($auth_user)) {
        return true;
    }
    $auth_user = new PEAR_User($dbh, $_SERVER['PHP_AUTH_USER']);
    switch (strlen(@$auth_user->password)) {
        // handle old-style DES-encrypted passwords
        case 13: {
            $seed = substr($auth_user->password, 0, 2);
            if (crypt($_SERVER['PHP_AUTH_PW'], $seed) == @$auth_user->password) {
                return true;
            }
            break;
        }
        // handle new-style MD5-encrypted passwords
        case 32: {
            if (md5($_SERVER['PHP_AUTH_PW']) == @$auth_user->password) {
                return true;
            }
            break;
        }
    }
    $auth_user = null;
    return false;
}
?>