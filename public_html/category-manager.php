<?php
// manage categories

auth_require(true);
response_header("PEAR :: Category Manager");
include_once '../include/pear-category.php';

// expected url vars: catid (category id)
$catid = (isset($catid)) ? (int) $catid : null;
// ** expected

if (empty($catid)) {
    $name   = 'Top Level';
    $parent = 0;
} else {
    if (isset($insert)) {
        $data = array(
            'name'   => $catname,
            'desc'   => $catdesc,
            'parent' => $catid);
        if (PEAR::isError(category::add($data))) {
            $message = "Error while saving category";
        } else {
            $message = "Successfully saved new category.";
        }
    } elseif (isset($remove)) {
        // XXXX TODO: implement remove categories
    }
    $row = $dbh->getRow("SELECT name, parent FROM categories
                         WHERE id = $catid", DB_FETCHMODE_ASSOC);
    extract($row);
}

if (isset($message)) {
    echo "<b><font color=\"#FF0000\">" . $message . "</font></b><br /><br />\n";
}
?>
<form action="<?php echo $GLOBALS['PHP_SELF'] . "?catid=$catid&insert=1"; ?>" method="post">
<table border="0" cellpadding="2" cellspacing="1" width="100%">
<tr>
    <td rowspan="4" width="30%"><?php print get_categories_menu('tree');?></td>
    <td valign="top"><h3>You are browsing category:</h3><?php print get_categories_menu('urhere');?>
    </td>
</tr>
</tr>
    <td valign="top">
<?php
$bb = new Borderbox("Insert new sub-category under: " . $name, "90%", "", 2, true);

$bb->plainRow("Name", "<input type=\"text\" name=\"catname\" size=\"15\" />");
$bb->plainRow("Summary", "<input type=\"text\" name=\"catdesc\" size=\"40\" />");
$bb->plainRow("<input type=\"submit\" name=\"action\" value=\"Insert\" />");

$bb->end();

if (isset($catid)) {
    echo "<br /><br />\n";
    echo make_link($_SERVER['PHP_SELF'] . "?remove=" . $name, "Delete") . " category " . $name . "<br />\n";
    echo "<font color=\"red\">(Warning: This will delete <b>all</b> subcategories and <b>all</b> packages!)</font><br /><br />\n";
    print_link("/packages.php?catpid=" . $catid . "&catname=" . $name, "List");
    echo ' all packages from this category.</b>';
} ?>
</tr>
</table>
</form>
<?php
response_footer();
?>
