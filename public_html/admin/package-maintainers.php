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
   $Id$
*/

require_once "HTML/Form.php";

response_header("PEAR Administration - Package maintainers");

if (isset($_GET['pid'])) {
    $id = (int)$_GET['pid'];
} else {
    $id = 0;
}

// Select package first
if (empty($id)) {
    auth_require(true);

    $packages = package::listAll(false);
    $values   = array();

    foreach ($packages as $name => $package) {
        $values[$package['packageid']] = $name;
    }

    $bb = new BorderBox("Select package");

    $form = new HTML_Form($_SERVER['PHP_SELF']);
    $form->addSelect("pid", "Package:", $values);
    $form->addSubmit();
    $form->display();

    $bb->end();

} else if (!empty($_GET['update'])) {
    if (!isAllowed($id)) {
        PEAR::raiseError("Only the lead maintainer of the package or PEAR
                          administrators can edit the maintainers.");
        response_footer();
        exit();
    }

    $all = maintainer::get($id);

    // Transform
    $new_list = array();
    foreach ((array)$_GET['maintainers'] as $maintainer) {
        list($handle, $role) = explode("||", $maintainer);
        $new_list[$handle] = $role;
    }

    // Perform databases operations
    $query = "SELECT role FROM maintains WHERE handle = ? AND package = ?";
    $check = $dbh->prepare($query);

    $query  = "INSERT INTO maintains VALUES (?, ?, ?, 1)";
    $insert = $dbh->prepare($query);

    $query  = "UPDATE maintains SET role = ? WHERE handle = ? AND package = ?";
    $update = $dbh->prepare($query);

    $query  = "DELETE FROM maintains WHERE handle = ? AND package = ?";
    $delete = $dbh->prepare($query);

    /**
     * In a first run, we delete all maintainers which are not in the
     * new list.
     * This isn't the best solution, but for now it works.
     */
    foreach ($all as $handle => $role) {
        if (isset($new_list[$handle])) {
            continue;
        }
        echo 'Deleting user <b>' . $handle . '</b> ...<br />';
        $result = $dbh->execute($delete, array($handle, $id));
    }

    // Update/Insert existing maintainers
    foreach ($new_list as $handle => $role) {
        $result = $dbh->execute($check, array($handle, $id));

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        if (!is_array($row)) {
            // Insert new maintainer
            echo 'Adding user <b>' . $handle . '</b> ...<br />';
            $result = $dbh->execute($insert, array($handle, $id, $role));
        } else if ($role != $row['role']) {
            // Update role
            echo 'Updating user <b>' . $handle . '</b> ...<br />';
            $result = $dbh->execute($update, array($role, $handle, $id));
        }
    }

    $url = $_SERVER['PHP_SELF'];
    if (!empty($_GET['pid'])) {
        $url .= "?pid=" . $_GET['pid'];
    }
    echo '<br /><b>Done</b><br />';
    echo '<a href="' . $url . '">Back</a>';
} else {
    if (!isAllowed($id)) {
        PEAR::raiseError("Only the lead maintainer of the package or PEAR
                          administrators can edit the maintainers.");
        response_footer();
        exit();
    }

    $bb = new BorderBox("Manage maintainers", "100%");

    echo '<script src="/javascript/package-maintainers.js" type="text/javascript"></script>';
    echo '<form onSubmit="beforeSubmit()" name="form" method="get" action="' . $_SERVER['PHP_SELF'] . '">';
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

    $users = user::listAll();
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
    echo '  <input type="submit" onClick="addMaintainer(); return false" name="add" value="Add as" />';
    echo '  <select name="role" size="1">';
    echo '    <option value="lead">lead</option>';
    echo '    <option value="developer">developer</option>';
    echo '    <option value="helper">helper</option>';
    echo '  </select><br /><br />';
    echo '  <input type="submit" onClick="removeMaintainer(); return false" name="remove" value="Remove" />';
    echo '  </td>';

    echo '  <td>';
    echo '  <select multiple="yes" name="maintainers[]" onChange="activateRemove();" size="10">';

    $maintainers = maintainer::get($id);
    foreach ($maintainers as $handle => $role) {
        $info = user::info($handle, "name");   // XXX: This sucks
        printf('<option value="%s||%s">%s (%s, %s)</option>',
               $handle,
               $role,
               $info['name'],
               $handle,
               $role
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

    echo '<script language="JavaScript" type="text/javascript">';
    echo 'document.form.remove.disabled = true;';
    echo 'document.form.add.disabled = true;';
    echo 'document.form.role.disabled = true;';
    echo '</script>';

    $bb->end();
}

function isAllowed($package)
{
    auth_require();
    $lead = in_array($_COOKIE['PEAR_USER'], array_keys(maintainer::get($package, true)));
    $admin = user::isAdmin($_COOKIE['PEAR_USER']);

    return ($lead || $admin);
}

response_footer();
?>
