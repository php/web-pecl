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
   | Authors: Martin Jansen <mj@php.net>                                  |
   |          Richard Heyes <richard@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
 */

zrequire_once "HTML/Form.php";

response_header("Package statistics");
?>

<h1>Package statistics</h1>

<script language="JavaScript">
<!--
function reloadMe()
{
    var newLocation = '<?php echo $_SERVER['PHP_SELF']; ?>?'
                      + 'cid='
                      + document.forms[1].cid.value
                      + '&pid='
                      + document.forms[1].pid.value
                      + '&rid='
                      + document.forms[1].rid.value;

    document.location.href = newLocation;
                             
}
//-->
</script>

<?php
$query = "SELECT * FROM packages"
         . (!empty($_GET['cid']) ? " WHERE category = '" . $_GET['cid'] . "'" : "")
         . " ORDER BY name";

$sth = $dbh->query($query);

while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $packages[$row['id']] = $row['name'];
}

$bb = new Borderbox("Select package");

// Don't use HTML_Form here since we need to use some custom javascript here
echo "<form action=\"package-stats.php\" method=\"get\">\n";
echo "<table>\n";
echo "<tr>\n";
echo "  <td>\n";
echo "  <select name=\"cid\" onchange=\"javascript:reloadMe();\">\n";
echo "    <option>Select category ...</option>\n";

foreach (category::listAll() as $value) {
    if (isset($_GET['cid']) && $_GET['cid'] == $value['id']) {
        echo "    <option value=\"" . $value['id'] . "\" selected>" . $value['name'] . "</option>\n";
    } else {
        echo "    <option value=\"" . $value['id'] . "\">" . $value['name'] . "</option>\n";
    }    
}

echo "  </select>\n";
echo "  </td>\n";
echo "  <td>\n";

if (isset($_GET['cid']) && $_GET['cid'] != "") {
    echo "  <select name=\"pid\" onchange=\"javascript:reloadMe();\">\n";
    echo "    <option>Select package ...</option>\n";

    foreach ($packages as $value => $name) {
        if (isset($_GET['pid']) && $_GET['pid'] == $value) {
            echo "    <option value=\"" . $value . "\" selected>" . $name . "</option>\n";
        } else {
            echo "    <option value=\"" . $value . "\">" . $name . "</option>\n";
        }    
    }

    echo "</select>\n";
} else {
    echo "<input type=\"hidden\" name=\"pid\" value=\"\" />\n";
}

echo "  </td>\n";
echo "  <td>\n";

if (isset($_GET['pid']) && $_GET['pid'] != "") {
    echo "  <select onchange=\"javascript:reloadMe();\" name=\"rid\" size=\"1\">\n";
    echo "  <option>Select release ...</option>\n";
    echo "  <option>All releases</option>\n";

    $query = "SELECT id, version FROM releases WHERE package = '" . $_GET['pid'] . "'";
    $rows = $dbh->getAll($query, DB_FETCHMODE_ASSOC);

    foreach ($rows as $row) {
        if (isset($_GET['rid']) && $_GET['rid'] == $row['id']) {
            echo "    <option value=\"" . $row['id'] . "\" selected>" . $row['version'] . "</option>\n";        
        } else {
            echo "    <option value=\"" . $row['id'] . "\">" . $row['version'] . "</option>\n";
        }
    }

    echo "  </select>\n";
} else {
    echo "<input type=\"hidden\" name=\"rid\" value=\"\" />\n";
}

echo "  </td>\n";

echo "</tr>\n";
echo "<tr>\n";
echo "  <td><input type=\"submit\" name=\"submit\" value=\"Go\"></td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";

$bb->end();

if (isset($_GET['pid']) && $_GET['pid'] != "") {

    $info = package::info($_GET['pid']);
         
    echo "<h2>Statistics for package \"" . $info['name'] . "\"</h2>\n";
    
    $bb = new Borderbox("General statistics");
    echo "Number of releases: <b>" . count($info['releases']) . "</b><br />\n";
    echo "Total downloads: <b>" . number_format(statistics::package($_GET['pid']), 0, '.', ',') . "</b><br />\n";
    $bb->end();
    
    if (count($info['releases']) > 0) {
        echo "<br />\n";
        $bb = new Borderbox("Release statistics");
    
        $release_statistics = statistics::release($_GET['pid'], (isset($_GET['rid']) ? $_GET['rid'] : ""));
    
        $i= 0;
        foreach ($release_statistics as $key => $value) {
            $bb2 = new Borderbox("Release: " . $value['version'], 400);
            echo "Number of downloads: <b>" . number_format($value['total'], 0, '.', ',') . "</b><br />\n";
    
            if ($value['total'] > 1) {
                echo "First download: <b>" . $value['first_download'] . "</b><br />\n";
                echo "Last download: <b>" . $value['last_download'] . "</b><br />\n";
            }
    
            $bb2->end();
            echo "<br />\n";
        }
        
        $bb->end();
    
        /**
        * Print the graph
        */
        printf('<br /><img src="package-stats-graph.php?pid=%s&releases=%s_339900" name="stats_graph" width="543" height="200" alt="">', $_GET['pid'], (int)$_GET['rid']);
    
    /**
    * Print the graph control stuff
    */
    $releases = $dbh->getAll('SELECT id, version FROM releases WHERE package = ' . $_GET['pid'], DB_FETCHMODE_ASSOC);
    ?>
<br /><br />
<script language="JavaScript" type="text/javascript">
<!--
    function clearGraphList()
    {
        graphForm = document.forms['graph_control'];
        for (i=0; i<graphForm.graph_list.options.length; i++) {
            graphForm.graph_list.options[i] = null;
        }
    }

    function addGraphItem()
    {
        graphForm = document.forms['graph_control'];
        selectedRelease = graphForm.releases.options[graphForm.releases.selectedIndex];
        selectedColour  = graphForm.colours.options[graphForm.colours.selectedIndex];

        if (selectedRelease.value != "" && selectedColour.value != "") {
            newText  = 'Release ' + selectedRelease.text + ' in ' + selectedColour.text;
            newValue = selectedRelease.value + '_' + selectedColour.value;
            graphForm.graph_list.options[graphForm.graph_list.options.length] = new Option(newText, newValue);

        } else {
            alert('Please select a release and a colour!');
        }
    }
    
    function removeGraphItem()
    {
        graphForm = document.forms['graph_control'];
        graphList = graphForm.graph_list;

        if (graphList.selectedIndex != null) {
            graphList.options[graphList.selectedIndex] = null;
        }
    }
    
    function updateGraph()
    {
        graphForm   = document.forms['graph_control'];
        releases_qs = '';

        if (graphForm.graph_list.options.length) {
            for (i=0; i<graphForm.graph_list.options.length; i++) {
                if (i == 0) {
                    releases_qs += graphForm.graph_list.options[i].value;
                } else {
                    releases_qs += ',' + graphForm.graph_list.options[i].value;
                }
            }
            graphForm.update.value = 'Updating...';
            document.images['stats_graph'].src = 'package-stats-graph.php?pid=<?=$_GET['pid']?>&releases=' + releases_qs;
            graphForm.update.value = 'Update graph';

        } else {
            alert('Please select one or more releases to show!');
        }
    }
//-->
</script>
<form name="graph_control"> <!-- No action as it should never be submitted -->
<table border="0">
    <tr>
        <td colspan="2">
            Show graph of:<br />
            <select style="width: 543px" name="graph_list" size="5">
            </select>
        </td>
    </tr>
    <tr>
        <td valign="top">
            Release:
            <select align="absmiddle" name="releases">
                <option value="">Select...</option>
                <option value="0">All</option>
                <?foreach($releases as $r):?>
                    <option value="<?=$r['id']?>"><?=$r['version']?></option>
                <?endforeach?>
            </select>
            Colour:
            <select align="absmiddle" name="colours">
                <option>Select...</option>
                <option value="339900">Green</option>
                <option value="dd0000">Red</option>
                <option value="003399">Blue</option>
                <option value="000000">Black</option>
                <option value="999900">Yellow</option>
            </select>
        </td>
        <td align="right">
            <input type="submit" style="width: 100px" name="add" value="Add" onclick="addGraphItem(); return false;">
            <input type="submit" style="width: 100px" name="remove" value="Remove" onclick="removeGraphItem(); return false" />
        </td>
    </tr>
    <tr>
        <td align="center" colspan="2">
            <input type="submit" name="update" value="Update graph" onclick="updateGraph(); return false" />
        </td>
    </tr>
</table>
</form>
<br />
        <?php
    }

/**
* Category based statistics
*/
} elseif (!empty($_GET['cid'])) {

	$category_name     = $dbh->getOne(sprintf("SELECT name FROM categories WHERE id = %d", $_GET['cid']));
	$total_packages    = $dbh->getOne(sprintf("SELECT COUNT(DISTINCT pid) FROM package_stats WHERE cid = %d", $_GET['cid']));
	$total_maintainers = $dbh->getOne(sprintf("SELECT COUNT(DISTINCT m.handle) FROM maintains m, packages p WHERE m.package = p.id AND p.category = %d", $_GET['cid']));
	$total_releases    = $dbh->getOne(sprintf("SELECT COUNT(*) FROM package_stats WHERE cid = %d", $_GET['cid']));
	$total_categories  = $dbh->getOne(sprintf("SELECT COUNT(*) FROM categories WHERE parent = %d", $_GET['cid']));

	// Query to get package list from package_stats_table
	$query = sprintf("SELECT dl_number, package, release, pid, rid, cid FROM package_stats WHERE cid = %s ORDER BY dl_number DESC", $_GET['cid']);

/**
* Global stats
*/
} else {

	$total_packages    = number_format($dbh->getOne("SELECT COUNT(DISTINCT pid) FROM package_stats"), 0, '.', ',');
	$total_maintainers = number_format($dbh->getOne("SELECT COUNT(DISTINCT handle) FROM maintains"), 0, '.', ',');
	$total_releases    = number_format($dbh->getOne("SELECT COUNT(*) FROM package_stats"), 0, '.', ',');
	$total_categories  = number_format($dbh->getOne("SELECT COUNT(*) FROM categories"), 0, '.', ',');
	$query             = "SELECT dl_number, package, release, pid, rid, cid FROM package_stats ORDER BY dl_number DESC";

}

/**
* Display this for Global and Category stats pages only
*/
if (@!$_GET['pid']) {
	echo '<br />';
	$bb = new BorderBox(!empty($_GET['cid']) ? 'Category statistics for: <i>' . $category_name . '</i>' : 'Global statistics');
	?>
<table border="0" width="100%">
	<tr>
		<td width="25%">Total Packages:</td>
		<td width="25%" align="center" bgcolor="#cccccc"><?=$total_packages?></td>
		<td width="25%">Total Releases:</td>
		<td width="25%" align="center" bgcolor="#cccccc"><?=$total_releases?></td>
	</tr>
	<tr>
		<td width="25%">Total Maintainers:</td>
		<td width="25%" align="center" bgcolor="#cccccc"><?=$total_maintainers?></td>
		<td width="25%">Total Categories:</td>
		<td width="25%" align="center" bgcolor="#cccccc"><?=$total_categories?></td>
	</tr>
</table>
	<?php
	$bb->end();
	
	echo '<br />';

	$bb = new BorderBox("Package statistics");

	$sth = $dbh->query($query); //$query defined above

	if (DB::isError($sth)) {
	    PEAR::raiseError("unable to generate stats");
	}

	echo "<table border=\"0\" width=\"100%\" cellpadding=\"2\" cellspacing=\"2\">\n";
	echo "<tr align=\"left\" bgcolor=\"#cccccc\">\n";
	echo "<th>Package name</th>\n";
	echo "<th>Release</th>\n";
	echo "<th><u># of downloads</u></th>\n";
	echo "<th>&nbsp;</th>\n";
	echo "</tr>\n";

	$lastPackage = "";

	while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
	    if ($row['package'] == $lastPackage) {
	        $row['package'] = "";
	    } else {
	        $lastPackage = $row['package'];
	        $row['package'] = "<a href=\"package-info.php?pacid=" .
	                            $row['pid'] . "\">" .
	                            $row['package'] . "</a>\n";
	    }

	    echo "<tr bgcolor=\"#eeeeee\">\n";
	    echo "<td>\n" . $row['package'] .  "</td>\n";
	    echo "<td>" . $row['release'] . "</td>\n";
	    echo "<td>" . number_format($row['dl_number'], 0, '.', ',') . "</td>\n";
	    echo "<td>[". make_link("/package-stats.php?cid=" . $row['cid'] . "&pid=" . $row['pid'] . "&rid=" . $row['rid'], "Details") . "]</td>\n";
	    echo "</tr>\n";
	}

	echo "</table>\n";

	$bb->end();
}

response_footer();
?>
