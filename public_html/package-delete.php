<?php
/**
 * Interface to delete a package.
 *
 * $Id$
 */

// XXX: This interface does *not* delete the release files on the
//      harddisk yet!

auth_require(true);

require_once "HTML/Form.php";

response_header("Delete package");
echo "<h1>Delete package</h1>";

if (!isset($_GET['id'])) {
    PEAR::raiseError("No package ID specified.");
    response_footer();
    exit();
}

$form = new HTML_Form($_SERVER['PHP_SELF'] . "?id=" . $_GET['id'], "POST");

if (!isset($_POST['confirm'])) {

    $bb = new Borderbox("Confirmation");
    
    $form->start();

    echo "Are you sure that you want to delete the package?<br /><br />";
    $form->displaySubmit("yes", "confirm");
    echo "&nbsp;";
    $form->displaySubmit("no", "confirm");
    
    echo "<br /><br /><font color=\"#ff0000\"><b>Warning:</b> Deleting
          the package will remove all package information and all
          releases!</font>";

    $form->end();

    $bb->end();

} else if ($_POST['confirm'] == "yes") {

    // XXX: Implement backup functionality
    // make_backup($_GET['id']);

    $tables = array("releases" => "package", "maintains" => "package",
                    "deps" => "package", "files" => "package",
                    "packages" => "id");

    echo "<pre>\n";

    foreach ($tables as $table => $field) {
        $query = sprintf("DELETE FROM %s WHERE %s = '%s'",
                         $table,
                         $field,
                         $_GET['id']
                         );

        echo "Removing package information from table \"" . $table . "\": ";
        $dbh->query($query);
        
        echo "<b>" . $dbh->affectedRows() . "</b> rows affected.\n";
    }

    echo "</pre>\nPackage " . $_GET['id'] . " has been deleted.\n";

} else if ($_POST['confirm'] == "no") {
    echo "The package has not been deleted.\n<br /><br />\n";
    echo "Go back to the " . make_link("/pkginfo.php?pacid=" . $_GET['id'], "package details") . ".";
}

response_footer();
?>
