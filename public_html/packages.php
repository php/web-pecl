<?php
/*
	TODO
	 - Number of packages in brackets does not include packages in subcategories
*/

require 'HTML/Table.php';

/***************************************
** Returns an appropriate query string
** for a self referencing link
***************************************/

function getQueryString($catpid, $catname, $showempty = false){
    if($catpid)
        $querystring[] = 'catpid='.$catpid;

    if($catname)
        $querystring[] = 'catname='.urlencode($catname);

    if($showempty)
        $querystring[] = 'showempty='.(int)$showempty;

    return '?'.implode('&', $querystring);
}

/***************************************
** Check input variables
***************************************/

// Expected url vars: catpid (category parent id), catname, showempty
$catpid  = isset($_GET['catpid'])  ? (int)$_GET['catpid']   : null;
$showempty = isset($_GET['showempty']) ? (bool)$_GET['showempty'] : false;

if (empty($catpid)) {
    $category_where = "IS NULL";
    $catname = "Top Level";
} else {
    $category_where = "= " . $catpid;
    $catname = isset($_GET['catname']) ? $_GET['catname'] : '';
}

$category_title = "Package Browser :: " . htmlspecialchars($catname);

$showempty_link = '<a href="'.$_SERVER['PHP_SELF'].getQueryString($catpid, $catname, !$showempty).'">'.($showempty ? 'Hide empty' : 'Show empty').'</a>';

/***************************************
** Main part of script
***************************************/

response_header($category_title);

//	$dbh->setErrorHandling(PEAR_ERROR_DIE);
$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

// 1) Show categories of this level

$sth     = $dbh->query("SELECT * from categories WHERE parent $category_where ORDER BY name");
$table   = new HTML_Table('border="0" cellpadding="6" cellspacing="2" width="100%"');
$nrow    = 0;
$catdata = array();

// Get names of sub-categories
$subcats = $dbh->getAssoc("SELECT p.id AS pid, c.id AS id, c.name AS name, c.summary AS summary".
                          "  FROM categories c, categories p ".
                          " WHERE p.parent $category_where ".
                          "   AND c.parent = p.id ORDER BY c.name",
                          false, null, DB_FETCHMODE_ASSOC, true);

// Get names of sub-packages
$subpkgs = $dbh->getAssoc("SELECT p.category, p.id AS id, p.name AS name, p.summary AS summary".
                          "  FROM packages p, categories c".
                          " WHERE c.parent $category_where AND p.category = c.id ORDER BY p.name",
                          false, null, DB_FETCHMODE_ASSOC, true);

$max_sub_links = 4;
$totalpackages = 0;
while ($sth->fetchInto($row)) {

    extract($row);

    $ncategories = ($cat_right - $cat_left - 1) / 2;

    if (!$showempty AND $npackages < 1) {
        continue;  // Show categories with packages
    }

    $sub_items = 0;

    $sub_links = array();
    if (isset($subcats[$id])) {
        foreach ($subcats[$id] as $subcat) {
            $sub_links[] = '<b><a href="'.$_SERVER['PHP_SELF'].'?catpid='.$subcat['id'].'&catname='.
                            urlencode($subcat['name']).'" title="'.htmlspecialchars($subcat['summary']).'">'.$subcat['name'].'</a></b>';
            if (sizeof($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    if (isset($subpkgs[$id])) {
        foreach ($subpkgs[$id] as $subpkg) {
            $sub_links[] = '<a href="package-info.php?pacid='.$subpkg['id'].'" title="'.
                            htmlspecialchars($subpkg['summary']).'">'.$subpkg['name'].'</a>';
            if (sizeof($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    if (sizeof($sub_links) >= $max_sub_links) {
        $sub_links = implode(', ', $sub_links) . ' ' . make_image("caret-r.gif", "[more]");
    } else {
        $sub_links = implode(', ', $sub_links);
    }


    settype($npackages, 'string');
    settype($ncategories, 'string');

    $data  = '<font size="+1"><b><a href="'.$_SERVER['PHP_SELF'].'?catpid='.$id.'&catname='.urlencode($name).'">'.$name.'</a></b></font> ('.$npackages.')<br />';//$name; //array($name, $npackages, $ncategories, $summary);
    $data .= $sub_links.'<br />';
    $catdata[] = $data;

    $totalpackages += $npackages;

    if($nrow++ % 2 == 1) {
        $table->addRow(array($catdata[0], $catdata[1]));
        $table->setCellAttributes($table->getRowCount()-1, 0, 'width="50%"');
        $table->setCellAttributes($table->getRowCount()-1, 1, 'width="50%"');
        $catdata = array();
    }
} // End while

// Any left over (odd number of categories).
if(count($catdata) > 0){
    $table->addRow(array($catdata[0]));
    $table->setCellAttributes($table->getRowCount()-1, 0, 'width="50%"');
    $table->setCellAttributes($table->getRowCount()-1, 1, 'width="50%"');
}

/***************************************
** Print the urhere text, showempty link
** and the categories
***************************************/

echo '<table border="0" width="100%"><tr><th valign="top" align="left">Contents of :: ';
html_category_urhere($catpid, false);
echo '</th><td valign="top" align="right">'.$showempty_link.'</td></tr>';
//	echo '<tr><td colspan="2">Looking for something specific? Try the <a href="package-search.php">package search</a>.</td></tr>';
print '</table>';

/***************************************
** Begin code for showing packages if we
** aren't at the top level.
***************************************/

if (!empty($catpid)) {
    $nrow = 0;
    // Subcategories list
    $more = ($showempty) ? 0 : 1;
    $subcats = $dbh->getAll("SELECT id, name, summary FROM categories WHERE ".
                            "parent = $catpid AND npackages >= $more", DB_FETCHMODE_ASSOC);
    if (count($subcats) > 0) {
        $sub_links = array();
        foreach ($subcats as $subcat) {
            $sub_links[] = '<b><a href="'.$_SERVER['PHP_SELF'].'?catpid='.$subcat['id'].'&catname='.
                            urlencode($subcat['name']).'" title="'.htmlspecialchars($subcat['summary']).'">'.$subcat['name'].'</a></b>';
        }
        print '<br />Sub-categories: ' . implode(', ', $sub_links) . '<br />';
    }

    // Package list
    $sth = $dbh->query("SELECT id, name, summary FROM packages WHERE category=$catpid ORDER BY name");
    print "<dl>\n";
    while ($sth->fetchInto($row)) {
        extract($row);
        print " <dt><a href=\"package-info.php?pacid=$id\">$name</a></dt>\n";
        print " <dd>$summary</dd>\n";
        print " <br /><br />\n";
    }
}

echo $nrow != 0 ? $table->toHtml() : '';
$sth->free();
echo "Total number of packages: " . $totalpackages;

response_footer();
?>
