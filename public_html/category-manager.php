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
        category::add($data);
    } elseif (isset($remove)) {
        // XXXX TODO: implement remove categories
    }
    $row = $dbh->getRow("SELECT name, parent FROM categories
                         WHERE id = $catid", DB_FETCHMODE_ASSOC);
    extract($row);
}
?>
<form action="<?php echo $GLOBALS['PHP_SELF'] . "?catid=$catid&insert=1"; ?>" method="post">
<table border="0" cellpadding="2" cellspacing="1" width="100%">
<tr>
    <td rowspan="3" width="30%"><?php print get_categories_menu('tree');?></td>
    <td><h3>You are browsing category:</h3><br><?php print get_categories_menu('urhere');?>
    </td>
</tr>
</tr>
    <td><b>Insert a new sub-category under: <?php print $name; ?></b><br/><br/>

    <table border="0" width="100%">
    <tr>
        <td>Name:</td>
        <td><input type="text" name="catname" size="15"></td>
    </tr>
    <tr>
         <td>Summary:</td>
         <td><input type="text" name="catdesc" size="40"></td>
    </tr>
    <tr>
        <td align="center" colspan="2"><input type="submit" name="action" value="Insert"></td>
    </tr>
    </table>

    </td>
</tr>
</tr>
    <td><b>Delete category: <?php print $name;?></b><br>
    <font color="red">(warning it will delete all subcategories and all packages!)</font>
    </td>
</tr>
</table>
</form>
<?php
response_footer();
?>