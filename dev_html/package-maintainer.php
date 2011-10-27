<?php
include PECL_INCLUDE_DIR . '/pear-database-package.php';
include PECL_INCLUDE_DIR . '/pear-rest.php';

function save_maintainers($dbh, $package_name, $maintainers_new, $role)
{
    $package_id = $dbh->getOne('SELECT id FROM packages WHERE name=' . $dbh->quote($package_name));
    if (!$package_id) {
        return false;

    }

    $handle_all = json_decode(file_get_contents(SVN_USERLIST), true);

    $sql = 'select maintains.handle, maintains.role from maintains, packages where packages.id=maintains.package and packages.name=' .
            $dbh->quote($package_name);
    $maintainer_current = $dbh->getAll($sql, NULL, DB_FETCHMODE_ASSOC);

    $tmp = array();
    foreach ($maintainer_current as $m) {
        $tmp[$m['handle']] = $m['role'];
    }
    $maintainer_current = $tmp;

    /* Sanity check */
    $new_list = array();
    foreach ($maintainers_new as $key => $handle) {
        if (!isset($handle_all[$handle])) {
            /*
               TODO: remove that check once master is fixed
                     SVN user list from master is wrong due to the usage of 'enable (email)' as account being enabled
            */
            if (!isset($maintainer_current[$handle])) {
                echo "Handle '$handle' does not exist";
                return false;
            }

            if (!isset($role[$key])) {
                echo "Role for '$handle' (position $key) is not set";
                return false;
            }
        }
        $new_list[$handle] = $role[$key];
    }

    print_r($new_list);

    /*
        1. delete the removed maintainers
        2. update existing or insert new ones
    */
    $query = "INSERT INTO maintains VALUES (?, ?, ?, 1)";
    $insert = $dbh->prepare($query);

    $query = "UPDATE maintains SET role = ? WHERE handle = ? AND package = ?";
    $update = $dbh->prepare($query);

    $query = "DELETE FROM maintains WHERE handle = ? AND package = ?";
    $delete = $dbh->prepare($query);

    foreach ($maintainer_current as $handle => $role) {
        if (isset($new_list[$handle])) {
            continue;
        }
        echo "deleting $handle\n";
        $result = $dbh->execute($delete, array($handle, $package_id));
    }

    foreach ($new_list as $handle => $role) {
        if (!isset($maintainer_current[$handle])) {
            // Insert new maintainer
            echo "insert $handle\n";
            $result = $dbh->execute($insert, array($handle, $package_id, $role));
        } else if ($role != $maintainer_current[$handle]) {
            // Update role
            echo "updating $handle\n";
            $result = $dbh->execute($update, array($role, $handle, $package_id));
        }
    }
    var_dump(PEAR_REST_DIR);
    $pear_rest = new pear_rest(PEAR_REST_DIR);
    $pear_rest->savePackageMaintainerREST($package_name);
    return true;
}

$package_name = filter_input(INPUT_GET, 'package', FILTER_SANITIZE_STRING);
$maintainer_new = filter_input(INPUT_GET, 'maintainer', FILTER_SANITIZE_STRING, array('flags' => FILTER_REQUIRE_ARRAY));
$role_new = filter_input(INPUT_GET, 'role', FILTER_SANITIZE_STRING, array('flags' => FILTER_REQUIRE_ARRAY));
$lead = in_array($auth_user->handle, array_keys(maintainer::get($package_name, true)));
echo "<pre>";
print_r($maintainer_new);
print_r($role_new);
print_r($package_name);
if (!$package_name) {
    header("HTTP/1.1 404 Bad Request");
    echo "Error: No package name";
    exit();
}

if (!$lead && !$auth_user->isAdmin()) {
    header("HTTP/1.1 404 Bad Request");
    echo "Error only lead can edit maintainer.";
    exit();
}

if ($maintainer_new) {
    if (!save_maintainers($dbh, $package_name, $maintainer_new, $role_new)) {
        header("HTTP/1.1 404 Bad Request");
        print_r($maintainer_new);
        echo "Error: Cannot save";
    }
    echo "Done.";
    exit();
}
