<?php

if (empty($user)) {
    $user = @$_GET['handle'];
}
if (empty($user)) {
    $user = basename($_SERVER['PATH_INFO']);
}

PEAR::setErrorHandling(PEAR_ERROR_RETURN);
$url = $dbh->getOne('SELECT wishlist FROM users WHERE handle = ?',
                    array($user));
if (empty($url) || PEAR::isError($url)) {
    header("HTTP/1.0 404 Not found");
    die("<h1>User not found</h1>\n");
}

header("Location: $url");

print "<a href=\"$url\">click here to go to $_GET[handle]'s wishlist</a>\n";

?>