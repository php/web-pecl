<?php

function auth_reject($realm = null, $message = null, $refresh = false)
{
    if ($realm === null) {
        $realm = PEAR_AUTH_REALM;
    }
    if ($message === null) {
        $message = "Please enter your username and password:";
    }
/*
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"$realm\"");
    if ($refresh) {
        Header("Refresh: 3; url=/");
    }
*/
    $GLOBALS['ONLOAD'] = "document.login.PEAR_USER.focus();";
    response_header($message);
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
    print "  <td><input type=\"checkbox\" name=\"PEAR_PERSIST\" value=\"on\"> Remember username and password.</td>\n";
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
    response_footer();
    exit;
}

function auth_verify($user, $passwd)
{
    global $dbh, $auth_user;

    $auth_user = new PEAR_User($dbh, $user);
    $ok = false;
    switch (strlen(@$auth_user->password)) {
        // handle old-style DES-encrypted passwords
        case 13: {
            $seed = substr($auth_user->password, 0, 2);
            $crypted = crypt($passwd, $seed);
            if ($crypted == @$auth_user->password) {
                $ok = true;
            } else {
                error_log("pear-auth: user `$user': invalid password (des)", 0);
            }
            break;
        }
        // handle new-style MD5-encrypted passwords
        case 32: {
            $crypted = md5($passwd);
            if ($crypted == @$auth_user->password) {
                $ok = true;
            } else {
                error_log("pear-auth: user `$user': invalid password (md5)", 0);
            }
            break;
        }
    }
    if (empty($auth_user->registered)) {
        error_log("pear-auth: user `$user' not registered", 0);
        $ok = false;
    }
    if (!$ok) {
        if (cvs_verify_password($user, $passwd)) {
            $auth_user = (object)array('handle' => $user);
            $ok = true;
        }
    }
    $auth_user->_readonly = true;
    return $ok;
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