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
 * Interface to delete a package.
 */

use App\BorderBox;
use App\Package;

auth_require(true);

response_header('Delete Package');
echo '<h1>Delete Package</h1>';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    report_error('No package ID specified.');
    response_footer();
    exit;
}

if (!isset($_POST['confirm'])) {

    $bb = new BorderBox("Confirmation");

    echo '<form action="/package-delete.php?id='.htmlspecialchars($_GET['id'], ENT_QUOTES).'" method="post">'."\n";
    echo "Are you sure that you want to delete the package?<br /><br />";
    echo '<input type="submit" name="confirm" value="yes" />'."\n";
    echo "&nbsp;";
    echo '<input type="submit" name="confirm" value="no" />'."\n";
    echo "<br /><br /><font color=\"#ff0000\"><b>Warning:</b> Deleting
          the package will remove all package information and all
          releases!</font>";
    echo '</form>'."\n";

    $bb->end();

} else if ($_POST['confirm'] == "yes") {

    // XXX: Implement backup functionality
    // make_backup($_GET['id']);

    $tables = [
        'releases'  => 'package',
        'maintains' => 'package',
        'deps'      => 'package',
        'files'     => 'package',
        'packages'  => 'id'
    ];

    echo "<pre>\n";

    $file_rm = 0;

    $sql = "SELECT p.name, r.version FROM packages p, releases r
            WHERE p.id = r.package AND r.package = :id";

    foreach ($database->run($sql, [':id' => $_GET['id']])->fetchAll() as $value) {
        $file = sprintf("%s/%s-%s.tgz",
                        $config->get('packages_dir'),
                        $value[0],
                        $value[1]);

        if (@unlink($file)) {
            echo "Deleting release archive \"" . $file . "\"\n";
            $file_rm++;
        } else {
            echo "<font color=\"#ff0000\">Unable to delete file " . $file . "</font>\n";
        }
    }

    echo "\n" . $file_rm . " file(s) deleted\n\n";

    $catid = Package::info($_GET['id'], 'categoryid');
    $catname = Package::info($_GET['id'], 'category');
    $packagename = Package::info($_GET['id'], 'name');
    $database->query("UPDATE categories SET npackages = npackages-1 WHERE id=$catid");

    foreach ($tables as $table => $column) {
        $sql = "DELETE FROM $table WHERE $column = :id";
        echo 'Removing package information from table "'.$table.'": ';

        $statement = $database->run($sql, [':id' => $_GET['id']]);

        echo '<b>'.$statement->rowCount()."</b> rows affected.\n";
    }

    $rest->deletePackage($packagename);
    $rest->savePackagesCategory($catname);
    echo "</pre>\nPackage " . htmlspecialchars($_GET['id'], ENT_QUOTES) . " has been deleted.\n";

} else if ($_POST['confirm'] == "no") {
    echo "The package has not been deleted.\n<br /><br />\n";
    echo 'Go back to the <a href="/package-info.php?package='.htmlspecialchars($_GET['id'], ENT_QUOTES).'">package details</a>.';
}

response_footer();
