<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
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

// manage categories

auth_require(true);
response_header("PEAR :: Category Manager");
include_once '../include/pear-category.php';

// expected url vars: catid (category id)
$catid = (isset($catid)) ? (int) $catid : null;
// ** expected

do {
    // insert new category
    if (!empty($newcatname) && !empty($newcatdesc)) {
        $data = array(
            'name'   => $newcatname,
            'desc'   => $newcatdesc,
            'parent' => $catid);
        if (PEAR::isError(category::add($data))) {
            $message = "Error while saving category";
        } else {
            $message = "Successfully saved new category.";
        }
    }
    if (empty($catid)) {
        $name   = 'Top Level';
        $parent = 0;
    } else {
        $row = $dbh->getRow("SELECT name, parent FROM categories
                             WHERE id = $catid", DB_FETCHMODE_ASSOC);
        extract($row);
    }
} while (false);

if (isset($message)) {
    echo "<b><font color=\"#FF0000\">" . $message . "</font></b><br /><br />\n";
}
?>
<form action="<?php echo $GLOBALS['PHP_SELF'] . "?catid=$catid"; ?>" method="post">
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

$bb->plainRow("Name", "<input type=\"text\" name=\"newcatname\" size=\"15\" />");
$bb->plainRow("Summary", "<input type=\"text\" name=\"newcatdesc\" size=\"40\" />");
$bb->plainRow("<input type=\"submit\" name=\"action\" value=\"Insert\" />");

$bb->end();
?>
</tr>
</table>
</form>
<?php
response_footer();
?>
