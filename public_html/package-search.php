<?php
require_once "HTML/Form.php";

$form = new HTML_Form($_SERVER['PHP_SELF']);

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

$bb = new Borderbox("Search options");
?>

<form action="<?php echo $_SERVER['PHP_SELF']?>" method="GET">
<table border="0">
<tr>
    <td>Package name:</td>
    <td valign="middle">
    <?php $form->displayText("pkg_name", @$_GET['pkg_name']); ?>
    </td>
    <td>Match:
    <?php $form->displayRadio("bool", "AND", (@$_GET['bool'] == "AND" || !isset($_GET['bool']))); ?>
    All words
    <?php $form->displayRadio("bool", "OR", (@$_GET['bool'] == "OR")); ?>
    Any word
    </td>
</tr>
<tr>
    <td>Maintainer:</td>
    <td><?php $form->displayText("pkg_maintainer", @$_GET['pkg_maintainer']); ?></td>
</tr>
<tr>
    <td>Category:</td>
    <td>
<?php
$sth = $dbh->query('SELECT id, name FROM categories ORDER BY name');

$rows = array("" => "");

while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $rows[$row['id']] = $row['name'];
}

$form->displaySelect("pkg_category", $rows, @$_GET['pkg_category']);
?>
    </td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="submit" value="Search" /></td>
</tr>
</table>
</form>

<?php
$bb->end();

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

    // Build category part of query
    if(!empty($_GET['pkg_category'])) {
        $where[] = "category = '".addslashes($_GET['pkg_category'])."'";
    }
    
    // Compose query and execute
    $where  = !empty($where) ? ' AND '.implode(' AND ', $where) : '';
    $sql    = "select p.id, p.name, p.category, p.summary, m.handle from packages p, maintains m where p.id = m.package " . $where . " order by p.name";
    $result = $dbh->query($sql);

    // Print any results
    if($result->numRows() > 0) {        
        echo "<br /><br />\n";
        $bb = new Borderbox("Search results (" . $result->numRows() . ")");

        while($result->fetchInto($row)) {
            echo ' <dt><a href="package-info.php?pacid='.$row['id'].'">'.$row['name'].'</a> (<a href="/account-info.php?handle='.$row['handle'].'">'.$row['handle'].'</a>)</dt>';
            echo ' <dd>'.$row['summary'].'</dd>';
            echo ' <br /><br />';
        }

        $bb->end();
    } else {
        echo '<p><strong>No results found</strong></p>';
    }    
}

response_footer();
?>