<?php

function auth_reject($realm, $message = "Login Failed!")
{
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"$realm\"");
    response_header($message);
    report_error($message);
    response_footer();
    exit;
}

function auth_require($level = 0)
{
    global $PHP_AUTH_USER, $PHP_AUTH_PW, $dbh, $auth_user;

    $realm = "PEAR user";
    $auth_user =& new PEAR_User(&$dbh, strtolower($PHP_AUTH_USER));
    if (DB::isError($auth_user) || md5($PHP_AUTH_PW) != $auth_user->password) {
        auth_reject($realm);
    }
}

?>
