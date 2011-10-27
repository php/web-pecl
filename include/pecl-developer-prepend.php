<?php
include "PEAR.php";
include __DIR__ . "/pear-config.php";
include __DIR__ . "/pear-auth.php";
include __DIR__ . "/pear-format-html.php";


session_start();

init_auth_user();

$options = array(
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
);

$dbh = DB::connect(PECL_DATABASE_DSN, $options);

if (!isset($_POST['handle'])) {
    auth_require();
}


