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
   $Id$
*/

/**
 * Interface to update package information.
 */

auth_require();

require_once "HTML/Form.php";
$form = new HTML_Form($_SERVER['PHP_SELF']);

response_header("Edit package");
?>

<script language="javascript">
<!--

function confirmed_goto(url, message) {
    if (confirm(message)) {
        location = url;
    }
}
// -->
</script>

<?php
echo "<h1>Edit package</h1>";

if (!isset($_GET['id'])) {
    PEAR::raiseError("No package ID specified.");
    response_footer();
    exit();
}

/**
 * The user has to be either a lead developer of the package or
 * a PEAR administrator.
 */
$lead = in_array($_COOKIE['PEAR_USER'], array_keys(maintainer::get($_GET['id'], true)));
$admin = user::isAdmin($_COOKIE['PEAR_USER']);

if (!$lead && !$admin) {
    PEAR::raiseError("Only the lead maintainer of the package or PEAR
                      administrators can edit the package.");
    response_footer();
    exit();
}

/** Update */
if (isset($_POST['submit'])) {
    foreach (array("name", "license", "summary", "description", "category") as $key) {
        $_POST[$key] = addslashes($_POST[$key]);
    }

    if ($_POST['name'] == "" || $_POST['license'] == "" ||
        $_POST['summary'] == "")
    {
        PEAR::raiseError("You have to enter values for name, license and summary!");
    }

    $query = sprintf("UPDATE packages SET name = '%s', license = '%s',
                      summary = '%s', description = '%s', category = '%s',
                      homepage = '%s', package_type = '%s'
                      WHERE id = '%s'",
                      $_POST['name'],
                      $_POST['license'],
                      $_POST['summary'],
                      $_POST['description'],
                      $_POST['category'],
                      $_POST['homepage'],
                      $_POST['type'],
                      $_GET['id']
                    );

    $sth = $dbh->query($query);

    if (PEAR::isError($sth)) {
        PEAR::raiseError("Unable to save data!");
    } else {
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

$row = package::info((int)$_GET['id']);

if (empty($row['name'])) {
    PEAR::raiseError("Illegal package id");
    response_footer();
    exit();
}

$bb = new Borderbox("Edit package information");
?>

<form action="<?php echo $_SERVER['PHP_SELF']?>?id=<?php echo $_GET['id']; ?>" method="POST">
<table border="0">
<tr>
    <td>Package name:</td>
    <td valign="middle">
    <?php $form->displayText("name", $row['name'], 30); ?>
    </td>
</tr>
<tr>
    <td>Package type:</td>
    <td valign="middle">
    <?php $form->displaySelect("type", array("pear" => "PEAR", "pecl" => "PECL"), $row['type'], 1); ?>
    </td>
</tr>
<tr>
    <td>License:</td>
    <td valign="middle">
    <?php $form->displayText("license", $row['license'], 30); ?>
    </td>
</tr>
<tr>
    <td valign="top">Summary:</td>
    <td>
    <?php $form->displayTextarea("summary", $row['summary'], 40, 3, 255); ?>
    </td>
</tr>
<tr>
    <td valign="top">Description:</td>
    <td>
    <?php $form->displayTextarea("description", $row['description']); ?>
    </td>
</tr>
<tr>
    <td>Category:</td>
    <td>
<?php
$sth = $dbh->query('SELECT id, name FROM categories ORDER BY name');

while ($cat_row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $rows[$cat_row['id']] = $cat_row['name'];
}

$form->displaySelect("category", $rows, $row['categoryid']);
?>
    </td>
</tr>
<tr>
    <td>Homepage:</td>
    <td valign="middle">
    <?php $form->displayText("homepage", $row['homepage'], 30); ?>
    </td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="submit" value="Save changes" />&nbsp;
    <input type="reset" name="cancel" value="Cancel" onClick="javascript:window.location.href='/package-info.php?pacid=<?php echo $_GET['id']; ?>'; return false" />
    </td>
</tr>
</table>
</form>

<?php
$bb->end();

echo "<br /><br />\n";

$bb = new Borderbox("Manage releases");

echo "<table border=\"0\">\n";

echo "<tr><th>Version</th><th>Releasedate</th><th>Actions</th></tr>\n";

foreach ($row['releases'] as $version => $release) {
    echo "<tr>\n";
    echo "  <td>" . $version . "</td>\n";
    echo "  <td>" . $release['releasedate'] . "</td>\n";
    echo "  <td>\n";
    
    $url = $_SERVER['PHP_SELF'] . "?id=" . 
                     $_GET['id'] . "&release=" . 
                     $release['id'] . "&action=release_remove";
    $msg = "Are you sure that you want to delete the release?";

    echo "<a href=\"javascript:confirmed_goto('$url', '$msg')\">"
         . make_image("delete.gif")
         . "</a>\n";

    echo "</td>\n";
    echo "</tr>\n";
}

echo "</table>\n";

$bb->end();

response_footer();
?>
