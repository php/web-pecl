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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

/**
 * Interface to update package information.
 */

use App\BorderBox;
use App\Release;
use App\User;

$release = new Release();
$release->setDatabase($database);
$release->setAuthUser($auth_user);
$release->setRest($rest);
$release->setPackagesDir($config->get('packages_dir'));
$release->setPackage($packageEntity);

$auth->require();

response_header("Edit package");
?>

<script>
function confirmed_goto(url, message) {
    if (confirm(message)) {
        location = url;
    }
}
</script>

<?php
echo "<h1>Edit package</h1>";

if (!isset($_GET['id'])) {
    PEAR::raiseError("No package ID specified.");
    response_footer();
    exit();
}

// The user has to be either a lead developer of the package or a PECL
// administrator.
$lead = User::maintains($auth_user->handle, $_GET['id'], "lead");
$admin = $auth_user->isAdmin();

if (!$lead && !$admin) {
    PEAR::raiseError("Only the lead maintainer of the package or PECL
                      administrators can edit the package.");
    response_footer();
    exit();
}

// Update
if (isset($_POST['submit'])) {

    if (!$_POST['name'] || !$_POST['license'] || !$_POST['summary']) {
        PEAR::raiseError("You have to enter values for name, license and summary!");
    }

    $query = 'UPDATE packages SET name = ?, license = ?,
              summary = ?, description = ?, category = ?,
              homepage = ?, cvs_link = ?,
              doc_link = ?, bug_link = ?, unmaintained = ?,
              newpackagename = ?, newchannel = ?
              WHERE id = ?';

        if (!empty($_POST['newpk_id'])) {
            $_POST['new_channel'] = 'pecl.php.net';
            $_POST['new_package'] = $database->run('SELECT name from packages WHERE id = ?', [$_POST['newpk_id']])->fetch('name');
            if (!$_POST['new_package']) {
                $_POST['new_channel'] = $_POST['newpk_id'] = null;
            }
        } else {
            if ($_POST['new_channel'] == 'pecl.php.net') {
                $_POST['newpk_id'] = $database->run('SELECT id from packages WHERE name = ?', [$_POST['new_package']])->fetch()['id'];
                if (!$_POST['newpk_id']) {
                    $_POST['new_channel'] = $_POST['new_package'] = null;
                }
            }
        }

    $qparams = [
                  $_POST['name'],
                  $_POST['license'],
                  $_POST['summary'],
                  $_POST['description'],
                  $_POST['category'],
                  $_POST['homepage'],
                  $_POST['cvs_link'],
                  $_POST['doc_link'],
                  $_POST['bug_link'],
                  (isset($_POST['unmaintained']) ? 1 : 0),
                  $_POST['new_package'],
                  $_POST['new_channel'],
                  $_GET['id']
    ];

    $statement = $database->run($query, $qparams);

    $rest->savePackage($_POST['name']);
    $rest->savePackagesCategory($packageEntity->info($_POST['name'], 'category'));
    echo "<b>Package information successfully updated.</b><br /><br />\n";
} elseif (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case "release_remove" :
            if (!isset($_GET['release'])) {
                PEAR::raiseError("Missing package ID!");
                break;
            }

            if ($release->remove($_GET['id'], $_GET['release'])) {
                echo "<b>Release successfully deleted.</b><br /><br />\n";
            } else {
                PEAR::raiseError("An error occured while deleting the release!");
            }

            break;
    }
}

$row = $packageEntity->info((int)$_GET['id']);

if (empty($row['name'])) {
    PEAR::raiseError("Illegal package id");
    response_footer();

    exit();
}

$bb = new BorderBox("Edit package information");
?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES)?>?id=<?php echo (int)$_GET['id']; ?>" method="POST">
<table border="0">
<tr>
    <td>Package name:</td>
    <td valign="middle">
        <input type="text" name="name" value="<?= $row['name']; ?>" size="30">
    </td>
</tr>
<tr>
    <td>License:</td>
    <td valign="middle">
        <input type="text" name="license" value="<?= $row['license']; ?>" size="30">
    </td>
</tr>
<tr>
    <td valign="top">Summary:</td>
    <td>
        <textarea name="summary" cols="40" rows="3" maxlength="255"><?= $row['summary']; ?></textarea>
    </td>
</tr>
<tr>
    <td valign="top">Description:</td>
    <td>
        <textarea name="description" cols="40" rows="5"><?= $row['description']; ?></textarea>
    </td>
</tr>
<tr>
    <td>Category:</td>
    <td>
    <select name="category" size="1">
        <?php
        $sth = $database->query('SELECT id, name FROM categories ORDER BY name');
        foreach ($sth->fetchAll() as $cat_row): ?>
        <option value="<?= $cat_row['id']; ?>" <?= (int)$row['categoryid'] == $cat_row['id'] ? ' selected' : ''; ?>><?= $cat_row['name']; ?></option>
        <?php endforeach; ?>
    </select>
    </td>
</tr>
<tr>
    <td>Homepage:</td>
    <td valign="middle">
    <input type="text" name="homepage" value="<?= $row['homepage']; ?>" size="30">
    </td>
</tr>
<tr>
    <td>Documentation:</td>
    <td valign="middle">
    <input type="text" name="doc_link" value="<?= $row['doc_link']; ?>" size="30">
    </td>
</tr>
<tr>
    <td>Web CVS Url:</td>
    <td valign="middle">
    <input type="text" name="cvs_link" value="<?= $row['cvs_link']; ?>" size="30">
    </td>
</tr>
<tr>
    <td>Bug tracker Url:</td>
    <td valign="middle">
    <input type="text" name="bug_link" value="<?= $row['bug_link']; ?>" size="30">
    </td>
</tr>
<tr>
    <td>Unmaintained package?</td>
    <td valign="middle">
        <input type="checkbox" name="unmaintained" <?= $row['unmaintained'] == 1 ? 'checked' : ''; ?>>
    </td>
</tr>
<tr>
    <td>Superseded by:</td>
    <td valign="middle">
        <select name="new_package" size="1">
        <option value="" <?= $row['new_package'] == '' ? 'selected' : ''; ?>>Select package</option>
        <?php
        $sth = $database->query('SELECT name FROM packages WHERE package_type="pecl" ORDER BY name');
        foreach ($sth->fetchAll() as $package): ?>
        <option value="<?= $package['name']; ?>" <?= $row['new_package'] == $package['name'] ? 'selected' : '';?>><?= $package['name']; ?></option>
        <?php endforeach; ?>
        </select>
    </td>
</tr>
<!-- to be enabled later, link to the wiki.php.net package page -->
<!--
<tr>
    <td>Wiki:</td>
    <td valign="middle">
    <input type="text" name="wiki_link" value="<?= htmlspecialchars($row['wiki_link'], ENT_QUOTES); ?>" size="30">
    </td>
</tr>
-->
<tr>
    <td>New Home Link (if moved out of pecl):</td>
    <td valign="middle">
    <input type="text" name="new_channel" value="<?= htmlspecialchars($row['new_channel'], ENT_QUOTES); ?>" size="30">
    </td>
</tr>

<tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="submit" value="Save changes" />&nbsp;
    <input type="reset" name="cancel" value="Cancel" onClick="javascript:window.location.href='/package-info.php?package=<?php echo $_GET['id']; ?>'; return false" />
    </td>
</tr>
</table>
</form>

<?php
$bb->end();

echo "<br /><br />\n";

$bb = new BorderBox("Manage releases");

echo "<table border=\"0\">\n";

echo "<tr><th>Version</th><th>Releasedate</th><th>Actions</th></tr>\n";

foreach ($row['releases'] as $version => $item) {
    echo "<tr>\n";
    echo "  <td>" . $version . "</td>\n";
    echo "  <td>" . $item['releasedate'] . "</td>\n";
    echo "  <td>\n";

    $url = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?id=" .
                     (int)$_GET['id'] . "&release=" .
                     $item['id'] . "&action=release_remove";
    $msg = "Are you sure that you want to delete the release?";

    echo "<a href=\"javascript:confirmed_goto('$url', '$msg')\">"
         . '<img src="/img/delete.gif" alt="Delete">'
         . "</a>\n";

    echo "</td>\n";
    echo "</tr>\n";
}

echo "</table>\n";

$bb->end();

response_footer();
