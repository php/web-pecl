<?php

/*
	TODO
	 - Number of packages in brackets does not include packages in subcategories
*/

	require 'HTML/Table.php';
	response_header("PEAR :: Packages");

/***************************************
** Returns an appropriate query string
** for a self referencing link
***************************************/

	function getQueryString($catpid, $catname, $noempty = false){

		if($catpid)
			$querystring[] = 'catpid='.$catpid;

		if($catname)
			$querystring[] = 'catname='.urlencode($catname);

		if($noempty)
			$querystring[] = 'noempty='.(int)$noempty;

		return '?'.implode('&', $querystring);
	}

/***************************************
** Check input variables
***************************************/

	// Expected url vars: catpid (category parent id), catname, noempty
	$catpid  = isset($HGV['catpid'])  ? (int)$HGV['catpid']   : null;
	$noempty = isset($HGV['noempty']) ? (bool)$HGV['noempty'] : false;
	
	if (empty($catpid)) {
	    $category_where = "categories.parent IS NULL";
	    $category_title = "Package Browser: Top Level Categories";
	    $catname = "Top Level";
	} else {
	    $category_where = "categories.parent = " . $catpid;
	    $category_title = "Package Browser: " . urldecode($catname);
	}

	$noempty_link = '<a href="'.$HVV['PHP_SELF'].getQueryString($catpid, $catname, !$noempty).'">'.($noempty ? 'Show all' : 'Hide empty').'</a>';

/***************************************
** Main part of script
***************************************/

	$dbh->setErrorHandling(PEAR_ERROR_DIE);
	$dbh->setFetchmode(DB_FETCHMODE_ASSOC);
	
	// 1) Show categories of this level
	
	$sth     = $dbh->query("SELECT * from categories WHERE $category_where");
	$table   = new HTML_Table('border="0" cellpadding="2" cellspacing="4" width="100%"');
	$nrow    = 0;
	$catdata = array();

	while ($sth->fetchInto($row)) {

	    extract($row);

		// Get names of sub categories
		$sc = $dbh->query('SELECT id, name FROM categories WHERE parent = '.$id);
		$subcat_links = array();

		if($sc->numRows() != 0){
			while($sc->fetchInto($subcat) AND @count($subcat_links) < 3){
				$subcat_links[] = '<a href="'.$HVV['PHP_SELF'].'?catpid='.$subcat['id'].'&catname='.urlencode($subcat['name']).'">'.$subcat['name'].'</a>';
			}
		}
		$subcat_links = $sc->numrows() <= 3 ? implode(', ', $subcat_links) : implode(', ', $subcat_links).'...';

	    $ncategories = ($cat_right - $cat_left - 1) / 2;
	    if ($noempty AND ($npackages <= 0)) {
	        continue;  // Show categories with packages
	    }

	    settype($npackages, 'string');
	    settype($ncategories, 'string');

		$data  = '<font size="+1"><a href="'.$HVV['PHP_SELF'].'?catpid='.$id.'&catname='.urlencode($name).'"><strong>'.$name.'</strong></a></font> ('.$npackages.')<br />';//$name; //array($name, $npackages, $ncategories, $summary);
		$data .= '<font size="-1">'.$subcat_links.'</font><br />';
		$catdata[] = $data;
		
		if($nrow % 2 == 1){
			$table->addRow(array($catdata[0], $catdata[1]));
		    $table->setCellAttributes($table->getRowCount()-1, 0, 'width="50%"');
	    	$table->setCellAttributes($table->getRowCount()-1, 1, 'width="50%"');
			$catdata = array();			
		}
		++$nrow;
		$sc->free();
	} // End while
	
	// Any left over (odd number of categories).
	if(count($catdata) > 0){
		$table->addRow(array($catdata[0]));
	    $table->setCellAttributes($table->getRowCount()-1, 0, 'width="50%"');
    	$table->setCellAttributes($table->getRowCount()-1, 1, 'width="50%"');
	}

/***************************************
** Print the urhere text, noempty link
** and the categories
***************************************/

	echo '<table border="0" width="100%"><tr><td valign="top">';
	html_category_urhere($catpid, $catname);
	echo '</td><td valign="top" align="right">'.$noempty_link.'</td></tr>';
	echo '<tr><td colspan="2" align="center">Looking for something specific? Try the <a href="package-search.php">package search</a>.</td></tr></table>';

	echo $nrow != 0 ? $table->toHtml() : '';
	$sth->free();

/***************************************
** Begin code for showing packages if we
** aren't at the top level.
***************************************/	

	if(isset($catpid)){
		print '<h1 align="center">Packages</h1>';
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
	
		/***************************************
		** Print the packages
		***************************************/
	
		if ($nrow == 0) {
		    print '<center><p>No packages in this category</p></center>';
		}else{
			html_table_border($table);
		}
	}
	
	response_footer();
	
?>
