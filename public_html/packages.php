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
    $category_where = "categories.parent = $catpid";
    $category_title = "Package Browser: " . urldecode($catname);
}

$dbh->setErrorHandling(PEAR_ERROR_DIE);
$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

// 1) Show categories of this level
echo '<table border="0" width="100%"><tr><td>';

$sth = $dbh->query("SELECT * from categories WHERE $category_where");

$table = new HTML_Table('border="1" cellpadding="1" cellspacing="1" width="90%" align="center"');
$nrow = 0;
while ($sth->fetchInto($row)) {
    extract($row);
    if (!$npackages) {
        //continue;  // XXXX Uncomment me to only show categories with packages
    } // XXXX change me with elseif
    if ($nrow == 0) {
        $table->addRow(array("Categories", "Sub.<br>categories", "Num.<br>Packages", "Summary"),
                             null, 'TH');
    }
    $bg = ($nrow++ % 2) ? '#f0f0f0' : '#e0e0e0';
    $childs = ($rightvisit - $leftvisit - 1) / 2;
    if (!$childs) {
        $childs = '0';
    }
    $name = "<a href=\"$PHP_SELF?catpid=$id&catname=".urlencode($name)."\">$name</a>";

    $table->addRow(array($name, $childs, $npackages, $summary));
    $table->setCellAttributes($nrow, 0, "width=\"20%\" bgcolor=\"$bg\"");
    $table->setCellAttributes($nrow, 1, "width=\"10%\" bgcolor=\"$bg\" align=\"center\"");
    $table->setCellAttributes($nrow, 2, "width=\"10%\" bgcolor=\"$bg\" align=\"center\"");
    $table->setCellAttributes($nrow, 3, "width=\"60%\" bgcolor=\"$bg\"");
}
if ($nrow == 0) {
    echo "<center><p>No sub-categories in this level yet</p></center>";
}
echo $table->toHTML();
echo '</td></tr></table><br><hr width="60%">';

// 2) Show packages of this level
echo '<table border="0" width="100%"><tr><td>';
echo "<center><p>No packages in this level yet</p></center>";
echo '</td></tr></table>';

response_footer();
?>