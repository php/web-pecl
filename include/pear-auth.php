<?php

function auth_reject($realm)
{
    Header("HTTP/1.0 401 Unauthorized");
    Header("WWW-authenticate: basic realm=\"$realm\"");
    response_header("access denied");
    report_error("access denied");
    response_footer();
    exit;
}

function auth_require($level = 0)
{
    global $PHP_AUTH_USER, $PHP_AUTH_PW, $dbh;

    if ($level > 0) {
        $realm = "PEAR administrator";
    } else {
        $realm = "PEAR maintainer";
    }
    $user = new PEAR_User(&$dbh, strtoupper($PHP_AUTH_USER));
    if (DB::isError($user) || md5($PHP_AUTH_PW) != $user->password) {
        auth_reject($domain);
    }
}

?>
