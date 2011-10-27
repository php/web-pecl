<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id: package-maintainers.php 315662 2011-08-29 00:07:22Z tyrael $
*/
include PECL_INCLUDE_DIR . '/pear-database-package.php';

function save_maintainers($dbh, $package_name, $maintainers_new)
{
    $all = maintainer::get($id);
    $maintainer_list = json_decode(file_get_contents(SVN_USERLIST));

    // Transform
    $new_list = array();
    foreach ($maintainers_new as $maintainer) {
        list($handle, $role) = explode("|", $maintainer);
        if (!in_array($handle, $maintainer_list)) {
            return false;
        }
        $new_list[$handle] = $role;
    }

    $package = $dbh->getOne('SELECT name FROM packages WHERE id=?', array($id));

    /*
        1. delete the removed maintainers
        2. update existing or insert new ones
    */

    $query = "SELECT role FROM maintains WHERE handle = ? AND package = ?";
    $check = $dbh->prepare($query);

    $query  = "INSERT INTO maintains VALUES (?, ?, ?, 1)";
    $insert = $dbh->prepare($query);

    $query  = "UPDATE maintains SET role = ? WHERE handle = ? AND package = ?";
    $update = $dbh->prepare($query);

    $query  = "DELETE FROM maintains WHERE handle = ? AND package = ?";
    $delete = $dbh->prepare($query);

    foreach ($all as $handle => $role) {
        if (isset($new_list[$handle])) {
            continue;
        }
        $result = $dbh->execute($delete, array($handle, $id));
    }

    foreach ($new_list as $handle => $role) {
        $result = $dbh->execute($check, array($handle, $id));

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        if (!is_array($row)) {
            // Insert new maintainer
            $result = $dbh->execute($insert, array($handle, $id, $role));
        } else if ($role != $row['role']) {
            // Update role
            $result = $dbh->execute($update, array($role, $handle, $id));
        }
    }

    $pear_rest->savePackageMaintainerREST($package);
    return true;
}

auth_require();

$package_name = filter_input(INPUT_GET, 'package', FILTER_SANITIZE_STRING);
$maintainers_new = filter_input(INPUT_GET, 'maintainers', FILTER_SANITIZE_STRING, array('flags' => FILTER_REQUIRE_ARRAY));
$lead = in_array($auth_user->handle, array_keys(maintainer::get($package, true)));

if ($maintainers && !$package_name) {
    header("HTTP/1.1 404 Bad Request");
    exit();
}

if ($maintainers && $package_name) {
    print_r($maintainers);
    if (!save_maintainers($dbh, $package_name, $maintainers_new)) {
        header("HTTP/1.1 404 Bad Request");
    }
    exit();
}

$admin = user::isAdmin($auth_user->handle);
if (!$package_name) {
    if (!$admin) {
        PEAR::raiseError("Only the administrators can edit any package.");
        response_footer();
        exit();
    }
} elseif (!$admin || !$lead) {
        PEAR::raiseError("Only the lead maintainer of the package or PEAR administrators can edit the maintainers.");
        response_footer();
        exit();
}

$get_package_list = filter_input(INPUT_GET, 'getpkg', FILTER_VALIDATE_BOOLEAN);

if ($package_name) {
    $mode = 'edit';
    $package_id = $dbh->getOne('SELECT id FROM packages WHERE name=' . $dbh->quote($package_name));
    if (!$package_id) {
        exit('invalid package name');
    }
    $sql = 'SELECT handle, role FROM maintains WHERE package=' . $package_id;
    $maintainer_list = $dbh->getAll($sql, NULL, DB_FETCHMODE_ASSOC);

} elseif ($get_package_list) {
    $search_term = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_STRING);

    $sql = 'SELECT name FROM packages WHERE name LIKE ' . "'%" . $search_term . "%' ORDER BY name";

    $res = $dbh->getCol($sql, DB_FETCHMODE_DEFAULT);
    echo json_encode($res);
} else {
    $mode = 'select';
}
include PECL_TEMPLATE_DIR . '/package-maintainer.html';
