<?php
/**
 * Interface to update package information.
 *
 * $Id$
 */

auth_require(true);

require_once "HTML/Form.php";
$form = new HTML_Form($_SERVER['PHP_SELF']);

response_header("Edit package");
echo "<h1>Edit package</h1>";

if (!isset($_GET['id'])) {
    PEAR::raiseError("No package ID specified.");
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
                      summary = '%s', description = '%s', category = '%s'
                      WHERE id = '%s'",
                      $_POST['name'],
                      $_POST['license'],
                      $_POST['summary'],
                      $_POST['description'],
                      $_POST['category'],
                      $_GET['id']
                    );

    $sth = $dbh->query($query);

    if (PEAR::isError($sth)) {
        PEAR::raiseError("Unable to save data!");
    } else {
        echo "<b>Package information successfully updated.</b><br /><br />\n";
    }
}

$query = sprintf("SELECT * FROM packages WHERE id = '%s'",
                 $_GET['id']
                 );

$sth = $dbh->query($query);

$row = $sth->fetchRow(DB_FETCHMODE_ASSOC);

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

$form->displaySelect("category", $rows, $row['category']);
?>
    </td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="submit" value="Save changes"></td>
</tr>
</table>
</form>

<?php
$bb->end();

response_footer();
?>
