<?php
if (!DEVBOX) {
    header("Location: /");
    exit;
}
auth_require(false);
$url = "http://$HTTP_HOST";
if ($SERVER_PORT != 80) {
    $url .= ":$SERVER_PORT";
}
$bn = str_replace('.', '\.', basename($_SERVER['PHP_SELF']));
$url .= preg_replace(":/$bn\$:", "/", $REQUEST_URI);
header("Location: $url");
exit;

?>
