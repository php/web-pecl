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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id: package-edit.php -1   $
*/
require_once "HTML/Form.php";
include PECL_INCLUDE_DIR . '/pear-database-package.php';
function error_package_edit($msg)
{
    $page = new PeclPage('/developer/page_developer.html');
    $page->title = 'Edit Package (Error no valid package name)';
    $page->contents = '<div class="warnigns">' . $msg . '</div>';
    $page->render();
    echo $page->html;
}

$package_name = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);

/**
 * Interface to update package information.
 */

if (!$package_name) {
    error_package_edit('Empty package name');
    exit();
}

$has_post = filter_has_var(INPUT_POST, 'name');
$form = new HTML_Form(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES));

/**
 * The user has to be either a lead developer of the package or
 * a PEAR administrator.
 */
$is_lead = user::maintains($auth_user->handle, $package_name, "lead");
$is_admin = user::isAdmin($auth_user->handle);

if (!$is_lead && !$is_admin) {
    error_package_edit("Only the lead maintainer of the package or PEAR
                      administrators can edit the package.");
    exit();
}

/** Update */
if (isset($_POST['submit'])) {

    if (!$_POST['name'] || !$_POST['license'] || !$_POST['summary']) {
        PEAR::raiseError("You have to enter values for name, license and summary!");
    }

    $query = 'UPDATE packages SET name = ?, license = ?,
              summary = ?, description = ?, category = ?,
              homepage = ?, package_type = ?, cvs_link = ?,
              doc_link = ?, bug_link = ?, unmaintained = ?,
              newpackagename = ?, newchannel = ?
              WHERE id = ?';

		if (!empty($_POST['newpk_id'])) {
			$_POST['new_channel'] = 'pecl.php.net';
			$_POST['new_package'] = $dbh->getOne('SELECT name from packages WHERE id = ?',
					array($_POST['newpk_id']));
			if (!$_POST['new_package']) {
				$_POST['new_channel'] = $_POST['newpk_id'] = null;
			}
		} else {
			if ($_POST['new_channel'] == 'pecl.php.net') {
				$_POST['newpk_id'] = $dbh->getOne('SELECT id from packages WHERE name = ?',
						array($_POST['new_package']));
				if (!$_POST['newpk_id']) {
					$_POST['new_channel'] = $_POST['new_package'] = null;
				}
			}
		}


    $qparams = array(
                  $_POST['name'],
                  $_POST['license'],
                  $_POST['summary'],
                  $_POST['description'],
                  $_POST['category'],
                  $_POST['homepage'],
                  $_POST['type'],
                  $_POST['cvs_link'],
                  $_POST['doc_link'],
                  $_POST['bug_link'],
                  (isset($_POST['unmaintained']) ? 1 : 0),
                  $_POST['new_package'],
                  $_POST['new_channel'],
                  $_GET['id']
                );

    $sth = $dbh->query($query, $qparams);

    if (PEAR::isError($sth)) {
        PEAR::raiseError("Unable to save data!");
    } else {
        $pear_rest->savePackageREST($_POST['name']);
        $pear_rest->savePackagesCategoryREST(package::info($_POST['name'], 'category'));
        echo "<b>Package information successfully updated.</b><br /><br />\n";
    }
} else if (isset($_GET['action'])) {

    switch ($_GET['action']) {

        case "release_remove" :
            if (!isset($_GET['release'])) {
                PEAR::raiseError("Missing package ID!");
                break;
            }

            if (release::remove($_GET['id'], $_GET['release'])) {
                echo "<b>Release successfully deleted.</b><br /><br />\n";
            } else {
                PEAR::raiseError("An error occured while deleting the
                                  release!");
            }

            break;
    }
}

$package = package::info($package_name);
$package_name = $package['name'];

if (empty($package['name'])) {
    error_package_edit("Illegal package id");
    exit();
}

$tmp_cat = $dbh->getAll('SELECT id, name FROM categories ORDER BY name', NULL, DB_FETCHMODE_OBJECT);

foreach ($tmp_cat as $category) {
    $category_list[$category->id] = $category->name;
}

$sql = 'SELECT handle, role FROM maintains WHERE package=' . $package['packageid'];
$maintainer_list = $dbh->getAll($sql, NULL, DB_FETCHMODE_ASSOC);

$data = array(
    'package'       => $package,
    'package_name'  => $package_name,
    'category_list' => $category_list,
    'form' => $form,
    'maintainer_list' => $maintainer_list,
);

$page = new PeclPage('/developer/page_developer.html');
$page->title = 'Edit Package ' . $package_name;
$page->jquery = true;
$page->addData($data);
$page->addJsSrc('/js/package-edit.js');
$page->setTemplate(PECL_TEMPLATE_DIR . '/developer/package-edit.html');
$page->render();

echo $page->html;