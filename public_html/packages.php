<?php
require 'HTML/Table.php';
response_header("PEAR :: Packages");

// expected url vars: catpid (category parent id), catname
$catpid = (isset($catpid)) ? (int) $catpid : null;
// ** expected

if (empty($catpid)) {
    $category_where = "categories.parent IS NULL";
    $category_title = "Package Browser: Top Level Categories";
} else {
    $category_where = "categories.parent = " . $catpid;
    $category_title = "Package Browser: " . urldecode($catname);
}

$dbh->setErrorHandling(PEAR_ERROR_DIE);
$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

// 1) Show categories of this level

$sth = $dbh->query("SELECT * from categories WHERE $category_where");

$table = new HTML_Table('border="0" cellpadding="2" cellspacing="1" width="100%"');
$nrow = 0;
while ($sth->fetchInto($row)) {
    extract($row);
    $npackages = ($rightvisit - $leftvisit - 1) / 2;
    if ($npackages == 0) {
        //continue;  // XXXX Uncomment me to only show categories with packages
    } // XXXX change me with elseif (show table head only when there are values)
    if ($nrow == 0) {
        $table->addRow(array("Category", "#&nbsp;Packages", "Summary"),
                             'bgcolor="#ffffff"', 'TH');
    }
    settype($npackages, 'string');
    $bg = ($nrow++ % 2) ? '#f0f0f0' : '#e0e0e0';
    $name = "<a href=\"$PHP_SELF?catpid=$id&catname=".urlencode($name)."\">$name</a>";

    $table->addRow(array($name, $npackages, $summary));
    $table->setCellAttributes($nrow, 0, "width=\"20%\" bgcolor=\"$bg\"");
    $table->setCellAttributes($nrow, 1, "width=\"10%\" bgcolor=\"$bg\" align=\"center\"");
    $table->setCellAttributes($nrow, 2, "width=\"70%\" bgcolor=\"$bg\"");
}
if ($nrow == 0) {
    print '<center><p>No sub-categories in this level</p></center>';
}
html_table_border($table);
$sth->free();

print '<br /><hr width="60%" />';

// 2) Show packages of this level
/* XXXX TODO:
- Show link to direct download
- Paginate results (use my Pager?)
*/
$nrow = 0;
$table = new HTML_Table('border="0" cellpadding="2" cellspacing="1" width="100%"');
if (!empty($catpid)) {
    $sth = $dbh->query("SELECT id, name, summary FROM packages WHERE category=$catpid");

    while ($sth->fetchInto($row)) {
        extract($row);
        if ($nrow == 0) {
            $table->addRow(array("Name", "Summary"), 'bgcolor="#ffffff"', 'TH');
        }
        $bg = ($nrow++ % 2) ? '#f0f0f0' : '#e0e0e0';
        $name = "<a href=\"pkginfo.php?pacid=$id\">$name</a>";
        $table->addRow(array($name, $summary));
        $table->setCellAttributes($nrow, 0, "width=\"20%\" bgcolor=\"$bg\"");
        $table->setCellAttributes($nrow, 1, "width=\"80%\" bgcolor=\"$bg\"");
    }
}
if ($nrow == 0) {
    print '<center><p>No packages in this level</p></center>';
}
html_table_border($table);

response_footer();

?>