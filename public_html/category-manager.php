<?php
// manage categories

auth_require(true);
response_header("PEAR :: Packages");
include_once 'pear-category.php';
include_once 'pear-database.php';

// expected url vars: catid (category id)
$catid = (isset($catid)) ? (int) $catid : null;
// ** expected

if (empty($catid)) {
    $name   = 'Top Level';
    $parent = 0;
    $category_title = "Package Browser: Top Level Categories";
} else {
    if (isset($insert)) {
        $data = array(
            'name'   => $catname,
            'desc'   => $catdesc,
            'parent' => $catid);
        category::add($data);
    } elseif (isset($remove)) {
        // XXXX TODO: implement remove categories
    }
    // XXXX TODO extract the full category path with visitations
    $row = $dbh->getRow("SELECT name, parent FROM categories
                         WHERE id = $catid", DB_FETCHMODE_ASSOC);
    extract($row);
}
?>
<table border="0" cellpadding="2" cellspacing="1" width="100%">
<tr>
    <td rowspan="3" width="30%"><?php print get_categories_menu('tree');?></td>
    <td><h3>You are browsing category:</h3><br><?php print get_categories_menu('urhere');?>
    </td>
</tr>
</tr>
    <td><b>Insert a new sub-category in: <?php print $name; ?></b><br/><br/>
    <form action="<?php echo $GLOBALS['PHP_SELF'] . "?catid=$catid&insert=1"; ?>" method="post">
    Name: <input type="text" name="catname" size="20"><br>
    Summary: <input type="text" name="catdesc" size="30">
    <input type="submit" name="action" value="Insert">
    </form>
    <p>
    </p>
    </td>
</tr>
</tr>
    <td><b>Delete category: <?php print $name;?></b><br>
    <font color="red">(warning it will delete all subcategories and all packages!)</font>
    </td>
</tr>
</table>
<?php
response_footer();
?>