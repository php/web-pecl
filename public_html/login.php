<?php

if (auth_verify(@$_POST['PEAR_USER'], @$_POST['PEAR_PW'])) {
    if (!empty($_POST['PEAR_PERSIST'])) {
        $expire = 2147483647;
    } else {
        $expire = 0;
    }
    setcookie('PEAR_USER', $_POST['PEAR_USER'], $expire, '/');
    setcookie('PEAR_PW', $_POST['PEAR_PW'], $expire, '/');
    if (isset($_POST['PEAR_OLDURL'])) {
        $gotourl = $_POST['PEAR_OLDURL'];
    } else {
        $gotourl = '/';
    }
    Header("Refresh: 0; url=$gotourl");
    print "<a href=\"$gotourl\">Click here if your browser does not redirect you automatically.</a>\n";
    exit;
}

auth_reject();

?>
