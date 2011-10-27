<?php
include PECL_INCLUDE_DIR . '/pear-database-package.php';
$package_name = filter_input(INPUT_GET, 'package', FILTER_SANITIZE_STRING);

if (!$package_name) {
    header("HTTP/1.1 404 Bad Request");
    echo "Error: No package name";
    exit();
}

$sql = 'select
    users.name, users.handle, role,
    md5(concat(users.handle, "@php.net")) as gravatar_id
    FROM
        maintains, packages, users
    WHERE
        users.handle=maintains.handle AND package=packages.id and packages.name=' . $dbh->quote($package_name);

$maintainer_list = $dbh->getAll($sql, NULL, DB_FETCHMODE_OBJECT);
/*
 * TODO: Convert the DBs on master or locally to UTF-8
*/
foreach ($maintainer_list as $maintainer) {
    $maintainer->name = utf8_encode($maintainer->name);
    
}
echo json_encode($maintainer_list);