<?php
/*
* TODO
*  - Use coxs pager class
*  - Show all packages when no search terms entered or not?
*  - More stuff to search on, eg summary, version
*/

/***************************************
** Header
***************************************/

response_header("Package search");
echo '<h1>Package search</h1>';

/***************************************
** Is form submitted? Do search and show
** results.
***************************************/

if(!empty($_GET)) {
    $dbh->setErrorHandling(PEAR_ERROR_DIE);
    $dbh->setFetchmode(DB_FETCHMODE_ASSOC);
    $where = array();
    $bool  = @$_GET['bool'] == 'AND' ? ' AND ' : ' OR ';

    // Build package name part of query
    if(!empty($_GET['pkg_name'])) {
        $searchwords = preg_split('/\s+/', $_GET['pkg_name']);
        for($i=0; $i<count($searchwords); $i++) {
            $searchwords[$i] = "name like '%".addslashes($searchwords[$i])."%'";
        }
        $where[] = '('.implode($bool, $searchwords).')';
    }

    // Build maintainer part of query
    if(!empty($_GET['pkg_maintainer'])) {
        $where[] = "handle like '%".addslashes($_GET['pkg_maintainer'])."%'";
    }

    // Compose query and execute
    $where  = !empty($where) ? ' AND '.implode(' AND ', $where) : '';
    $sql    = "select p.id, p.name, p.category, p.summary, m.handle from packages p, maintains m where p.id = m.package " . $where . " order by p.name";
    $result = $dbh->query($sql);

    // Print any results
    if($result->numRows() > 0) {
        echo '<p><strong>Results:</strong></p>';
        while($result->fetchInto($row)) {
            echo ' <dt><a href="pkginfo.php?pacid='.$row['id'].'">'.$row['name'].'</a> (<a href="/account-info.php?handle='.$row['handle'].'">'.$row['handle'].'</a>)</dt>';
            echo ' <dd>'.$row['summary'].'</dd>';
            echo ' <br /><br />';
        }
    } else {
        echo '<p><strong>No results found</strong></p>';
    }
}

/***************************************
** Show the form
***************************************/

$e_reporting = error_reporting(~E_NOTICE);
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="GET">
<table border="0">
	<tr>
		<td>Package name:</td>
		<td>
			<input type="text" name="pkg_name" value="<?=$_GET['pkg_name']?>">
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			Match: <input type="RADIO" name="bool" value="AND" id="bool_and" <?=($bool == ' OR ' ? '' : 'checked="checked"')?>> <label for="bool_and">All words</label>
			       <input type="RADIO" name="bool" value="OR" id="bool_or" <?=($bool == ' OR ' ? 'checked="checked"' : '')?>> <label for="bool_or">Any word</label>
		</td>
	</tr>

	<tr>
		<td>Maintainer:</td>
		<td><input type="text" name="pkg_maintainer" value="<?=$_GET['pkg_maintainer']?>"></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" name="submit" value="Search"></td>
	</tr>
</table>
</form>
<?php
error_reporting($e_reporting);

/***************************************
** Footer
***************************************/

	response_footer();
?>