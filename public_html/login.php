<?php

auth_require();
$url = "http://$HTTP_HOST";
if ($SERVER_PORT != 80) {
    $url .= ":$SERVER_PORT";
}
$bn = str_replace('.', '\.', basename($PHP_SELF));
$url .= preg_replace(":/$bn\$:", "/", $REQUEST_URI);
header("Location: $url");
exit;

?>
