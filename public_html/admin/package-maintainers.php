<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

use App\BorderBox;
use App\Maintainer;
use App\Package;
use App\User;

response_header("PECL Administration - Package maintainers");

if (isset($_GET['pid'])) {
    $id = (int)$_GET['pid'];
} else {
    $id = 0;
}

$self = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES);

// Select package first
if (empty($id)) {
    auth_require(true);

    $values = Package::listAllNames();

    $bb = new BorderBox("Select package");

    include __DIR__.'/../../templates/forms/admin_select_package.php';

    $bb->end();

} elseif (!empty($_GET['update'])) {
    if (!isAllowed($id)) {
        PEAR::raiseError("Only the lead maintainer of the package or PECL
                          administrators can edit the maintainers.");
        response_footer();
        exit();
    }

    $all = Maintainer::get($id);

    // Transform
    $new_list = [];
    foreach ((array)$_GET['maintainers'] as $maintainer) {
        list($handle, $role) = explode("||", $maintainer);
        $new_list[$handle] = $role;
    }

    $package = $database->run('SELECT name FROM packages WHERE id=?', [$id])->fetch()['name'];

    // Perform databases operations
    $sql = "SELECT role FROM maintains WHERE handle = ? AND package = ?";
    $check = $database->prepare($sql);

    $sql  = "INSERT INTO maintains VALUES (?, ?, ?, 1)";
    $insert = $database->prepare($sql);

    $sql  = "UPDATE maintains SET role = ? WHERE handle = ? AND package = ?";
    $update = $database->prepare($sql);

    $sql  = "DELETE FROM maintains WHERE handle = ? AND package = ?";
    $delete = $database->prepare($sql);

    // In a first run, we delete all maintainers which are not in the new list.
    // This isn't the best solution, but for now it works.
    foreach ($all as $handle => $role) {
        if (isset($new_list[$handle])) {
            continue;
        }

        echo 'Deleting user <b>' . $handle . '</b> ...<br />';
        $delete->execute([$handle, $id]);
    }

    // Update/Insert existing maintainers
    foreach ($new_list as $handle => $role) {
        $check->execute([$handle, $id]);

        $row = $check->fetch();
        if (!is_array($row)) {
            // Insert new maintainer
            echo 'Adding user <b>' . $handle . '</b> ...<br />';
            $insert->execute([$handle, $id, $role]);
        } else if ($role != $row['role']) {
            // Update role
            echo 'Updating user <b>' . $handle . '</b> ...<br />';
            $update->execute([$role, $handle, $id]);
        }
    }

    $rest->savePackageMaintainer($package);

    $url = $self;

    if (!empty($_GET['pid'])) {
        $url .= "?pid=" . urlencode(strip_tags($_GET['pid']));
    }

    echo '<br /><b>Done</b><br />';
    echo '<a href="' . $url . '">Back</a>';
} else {
    if (!isAllowed($id)) {
        PEAR::raiseError("Only the lead maintainer of the package or PECL
                          administrators can edit the maintainers.");
        response_footer();
        exit();
    }

    $package = $database->run('SELECT name FROM packages WHERE id=?', [$id])->fetch()['name'];

    $package = htmlentities($package, ENT_QUOTES);

    $bb = new BorderBox("Manage maintainers for $package", "100%");

    echo '<script src="/js/package-maintainers.js"></script>';
    echo '<form onSubmit="beforeSubmit()" name="form" method="get" action="' . $self . '">';
    echo '<input type="hidden" name="update" value="yes" />';
    echo '<input type="hidden" name="pid" value="' . $id . '" />';
    echo '<table border="0" cellpadding="0" cellspacing="4" border="0" width="100%">';
    echo '<tr>';
    echo '  <th>All users:</th>';
    echo '  <th></th>';
    echo '  <th>Package maintainers:</th>';
    echo '</tr>';

    echo '<tr>';
    echo '  <td>';
    echo '  <select onChange="activateAdd();" name="accounts" size="10">';

    $users = User::listAll();

    foreach ($users as $user) {
        if (empty($user['handle'])) {
            continue;
        }

        printf('<option value="%s">%s (%s)</option>',
               $user['handle'],
               $user['name'],
               $user['handle']
               );
    }

    echo '  </select>';
    echo '  </td>';

    echo '  <td>';
    echo '  <input type="button" onClick="addMaintainer(); return false" name="add" value="Add as" />';
    echo '  <select name="role" size="1">';
    echo '    <option value="lead">lead</option>';
    echo '    <option value="developer">developer</option>';
    echo '    <option value="helper">helper</option>';
    echo '  </select><br /><br />';
    echo '  <input type="button" onClick="removeMaintainer(); return false" name="remove" value="Remove" />';
    echo '  </td>';

    echo '  <td>';
    echo '  <select multiple="yes" name="maintainers[]" onChange="activateRemove();" size="10">';

    $maintainers = Maintainer::get($id);
    foreach ($maintainers as $handle => $role) {
        // XXX: This sucks
        $info = User::info($handle, "name");
        printf('<option value="%s||%s">%s (%s, %s)</option>',
               $handle,
               $role['role'],
               $info['name'],
               $handle,
               $role['role']
               );
    }

    echo '  </select>';
    echo '  </td>';
    echo '</tr>';
    echo '<tr>';
    echo '  <td colspan="3"><input type="submit" /></td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';

    echo '<script>';
    echo 'document.form.remove.disabled = true;';
    echo 'document.form.add.disabled = true;';
    echo 'document.form.role.disabled = true;';
    echo '</script>';

    $bb->end();
}

function isAllowed($package)
{
    global $auth_user;

    auth_require();

    $lead = in_array($auth_user->handle, array_keys(Maintainer::get($package, true)));
    $admin = $auth_user->isAdmin();

    return ($lead || $admin);
}

response_footer();
