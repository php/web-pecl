<?php

function auth_reject($realm = null, $message = null)
{
    if ($realm === null) {
        $realm = PEAR_AUTH_REALM;
    }
    if ($message === null) {
        $message = "Login Failed!";
    }
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"$realm\"");
    Header("Refresh: 3; url=/");
    response_header($message);
    report_error($message);
    response_footer();
    exit;
}

function auth_require($admin = false)
{
    global $PHP_AUTH_USER, $PHP_AUTH_PW, $dbh;
    global $auth_user;

    $user = $PHP_AUTH_USER;
    $auth_user =& new PEAR_User($dbh, $user);
    $ok = false;
    switch (strlen(@$auth_user->password)) {
        // handle old-style DES-encrypted passwords
        case 13: {
            $seed = substr($auth_user->password, 0, 2);
            if (crypt($PHP_AUTH_PW, $seed) == @$auth_user->password) {
                $ok = true;
            }
            break;
        }
        // handle new-style MD5-encrypted passwords
        case 32: {
            if (md5($PHP_AUTH_PW) == @$auth_user->password) {
                $ok = true;
            }
            break;
        }
    }
    if (empty($auth_user->registered)) {
        $ok = false;
    }
    if (!$ok) {
        if (cvs_verify_password($user, $PHP_AUTH_PW)) {
            $auth_user = (object)array('handle' => $user);
        } else {
            auth_reject();
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

?>