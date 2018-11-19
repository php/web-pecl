<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Martin Jansen <mj@php.net>                                  |
  |          Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

use App\BorderBox;
use App\Repository\CategoryRepository;
use App\Repository\PackageRepository;
use App\Repository\PackageStatsRepository;
use App\Repository\ReleaseRepository;

$packageRepository = new PackageRepository($database);
$packageStatsRepository = new PackageStatsRepository($database);

response_header('Package Statistics');
?>

<h1>Package Statistics</h1>

<script>
function reloadMe()
{
    var newLocation = '<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>?'
                      + 'cid='
                      + document.forms[1].cid.value
                      + '&pid='
                      + document.forms[1].pid.value
                      + '&rid='
                      + document.forms[1].rid.value;

    document.location.href = newLocation;

}
</script>

<?php

$_GET['cid'] = isset($_GET['cid']) ? (int) $_GET['cid'] : 0;
$_GET['pid'] = isset($_GET['pid']) ? (int) $_GET['pid'] : 0;
$_GET['rid'] = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;

$bb = new BorderBox('Select Package');

echo ' <form action="package-stats.php" method="get">'."\n";
echo ' <table>'."\n";
echo '  <tr>'."\n";
echo '  <td>'."\n";
echo '   <select name="cid" onchange="javascript:reloadMe();">'."\n";
echo '    <option value="">Select category ...</option>'."\n";
$categoryRepository = new CategoryRepository($database);
foreach ($categoryRepository->findAll() as $category) {
    $selected = '';
    if (isset($_GET['cid']) && $_GET['cid'] == $category['id']) {
        $selected = ' selected="selected"';
    }
    echo '    <option value="' . $category['id'] . '"' . $selected . '>' . $category['name'] . "</option>\n";
}

echo "  </select>\n";
echo "  </td>\n";
echo "  <td>\n";

if (isset($_GET['cid']) && $_GET['cid'] != '') {
    echo "  <select name=\"pid\" onchange=\"javascript:reloadMe();\">\n";
    echo '    <option value="">Select package ...</option>'."\n";

    $packages = $packageRepository->findAllByCategory($_GET['cid']);

    foreach ($packages as $id => $name) {
        $selected = '';

        if (isset($_GET['pid']) && $_GET['pid'] == $id) {
            $selected = ' selected="selected"';
        }

        echo '    <option value="'.$id.'"'.$selected.'>'.$name."</option>\n";
    }

    echo "</select>\n";
} else {
    echo "<input type=\"hidden\" name=\"pid\" value=\"\" />\n";
}

echo "  </td>\n";
echo "  <td>\n";

if (isset($_GET['pid']) && (int)$_GET['pid']) {
    echo "  <select onchange=\"javascript:reloadMe();\" name=\"rid\" size=\"1\">\n";
    echo '   <option value="">All releases</option>'."\n";

    $releaseRepository = new ReleaseRepository($database);
    $rows = $releaseRepository->findByPackageId($_GET['pid']);

    foreach ($rows as $row) {
        $selected = '';

        if (isset($_GET['rid']) && $_GET['rid'] == $row['id']) {
            $selected = ' selected="selected"';
        }

        echo '    <option value="' . $row['id'] . '"' . $selected . '>' . $row['version'] . "</option>\n";
    }

    $releases = $rows;
    echo "  </select>\n";
} else {
    $releases = [];
    echo '<input type="hidden" name="rid" value="" />'."\n";
}

echo "  </td>\n";

echo "</tr>\n";
echo "<tr>\n";
echo '  <td><input type="submit" name="submit" value="Go" /></td>'."\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";

$bb->end();

if (isset($_GET['pid']) && (int)$_GET['pid']) {

    $info = $packageEntity->info($_GET['pid'],null,false);

    if (isset($info['releases']) && count($info['releases'])>0) {
        echo '<h2>&raquo; Statistics for Package &quot;<a href="/package/' . $info['name'] . '">' . $info['name'] . "</a>&quot;</h2>\n";
        $bb = new BorderBox("General Statistics");
        echo "Number of releases: <strong>" . count($info['releases']) . "</strong><br />\n";
        echo 'Total downloads: <strong>' . number_format($packageStatsRepository->getDownloadsByPackageId($_GET['pid']), 0, '.', ',') . "</strong><br />\n";
        $bb->end();
    } else {
        $bb = new BorderBox('General Statistics');
        echo 'No package or release found.';
        $bb->end();
    }

    if (count($info['releases']) > 0) {
        echo "<br />\n";
        $bb = new BorderBox('Release Statistics');
?>
    <table cellspacing="0" cellpadding="3" style="border: 0px; width: 100%;">
    <tr>
        <th style="text-align: left;">Version</th>
        <th style="text-align: left;">Downloads</th>
        <th style="text-align: left;">Released</th>
        <th style="text-align: left;">Last Download</th>
    </tr>
<?php
        $releasesStats = $packageStatsRepository->getReleasesStats(
            $_GET['pid'],
            (isset($_GET['rid']) ? $_GET['rid'] : null)
        );

        foreach ($releasesStats as $value) {
            $version = '<a href="/package/'.$info['name'].'/'.$value['release'].'">'.$value['release'].'</a>';
            echo ' <tr>';
            echo '  <td>' . $version . "</td>\n";
            echo '  <td>' . number_format($value['dl_number'], 0, '.', ',');
            echo "  </td>\n";
            echo '  <td>';
            echo $formatDate->utc($value['releasedate'], 'Y-m-d');
            echo "  </td>\n";
            echo '  <td>';
            echo $formatDate->utc($value['last_dl']);
            echo "  </td>\n";
            echo " </tr>\n";
        }
        echo "</table>\n";
        $bb->end();

        // Print the graph
        printf('<br /><img src="package-stats-graph.php?pid=%s&releases=%s_339900" name="stats_graph" width="543" height="200" alt="" />',
               $_GET['pid'],
               isset($_GET['rid']) ? (int)$_GET['rid'] : ''
               );

        // Print the graph control stuff
        $releases = $database->run('SELECT id, version FROM releases WHERE package = ?', [$_GET['pid']])->fetchAll();
    ?>
<br /><br />

<script>
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
            document.images['stats_graph'].src = 'package-stats-graph.php?pid=<?php echo $_GET['pid']; ?>&releases=' + releases_qs;
            graphForm.update.value = 'Update graph';

        } else {
            alert('Please select one or more releases to show!');
        }
    }
</script>
<form name="graph_control" action="#">
 <input type="hidden" name="pid" value="<?php echo isset($_GET['pid']) ? $_GET['pid'] : ''; ?>" />
 <input type="hidden" name="rid" value="<?php echo isset($_GET['rid']) ? $_GET['rid'] : ''; ?>" />
 <input type="hidden" name="cid" value="<?php echo isset($_GET['cid']) ? $_GET['cid'] : ''; ?>" />
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
    <select name="releases">
     <option value="">Select...</option>
     <option value="0">All</option>
     <?php foreach($releases as $r) {?>
      <option value="<?php echo $r['id']; ?>"><?php echo $r['version']; ?></option>
     <?php } ?>
    </select>
    Colour:
    <select name="colours">
     <option>Select...</option>
     <option value="339900">Green</option>
     <option value="dd0000">Red</option>
     <option value="003399">Blue</option>
     <option value="000000">Black</option>
     <option value="999900">Yellow</option>
    </select>
   </td>
   <td align="right">
    <input type="submit" style="width: 100px" name="add" value="Add" onclick="addGraphItem(); return false;" />
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

// Category based statistics
} elseif (!empty($_GET['cid'])) {

    $category_name     = $database->run("SELECT name FROM categories WHERE id = ?", [$_GET['cid']])->fetch()['name'];
    $total_packages    = $database->run("SELECT COUNT(DISTINCT pid) AS count FROM package_stats WHERE cid = ?", [$_GET['cid']])->fetch()['count'];
    $total_maintainers = $database->run("SELECT COUNT(DISTINCT m.handle) AS count FROM maintains m, packages p WHERE m.package = p.id AND p.category = ?", [$_GET['cid']])->fetch()['count'];
    $total_releases    = $database->run("SELECT COUNT(*) AS count FROM package_stats WHERE cid = ?", [$_GET['cid']])->fetch()['count'];
    $total_categories  = $database->run("SELECT COUNT(*) AS count FROM categories WHERE parent = ?", [$_GET['cid']])->fetch()['count'];

    // Query to get package list from package_stats_table
    $query = sprintf("SELECT SUM(ps.dl_number) AS dl_number, ps.package, ps.release, ps.pid, ps.rid, ps.cid
                      FROM package_stats ps, packages p
                      WHERE p.package_type = 'pecl' AND p.id = ps.pid AND
                      p.category = %s GROUP BY ps.pid ORDER BY ps.dl_number DESC",
                     (int)$_GET['cid']
                     );

// Global stats
} else {

    $total_packages    = number_format($database->run('SELECT COUNT(id) AS count FROM packages WHERE package_type="pecl"')->fetch()['count'], 0, '.', ',');
    $total_maintainers = number_format($database->run('SELECT COUNT(DISTINCT handle) AS count FROM maintains')->fetch()['count'], 0, '.', ',');
    $total_releases    = number_format($database->run('SELECT COUNT(*) AS count FROM releases r, packages p
                       WHERE r.package = p.id AND p.package_type="pecl"')->fetch()['count'], 0, '.', ',');
    $total_categories  = number_format($database->run('SELECT COUNT(*) AS count FROM categories')->fetch()['count'], 0, '.', ',');
    $total_downloads   = number_format($database->run('SELECT SUM(dl_number) AS downloads FROM package_stats, packages p
                       WHERE package_stats.pid = p.id AND p.package_type="pecl"')->fetch()['downloads'], 0, '.', ',');
    $query             = "SELECT sum(ps.dl_number) as dl_number, ps.package, ps.pid, ps.rid, ps.cid
                          FROM package_stats ps, packages p
                          WHERE p.id = ps.pid AND p.package_type = 'pecl'
                          GROUP BY ps.pid ORDER BY dl_number DESC";

}

// Display this for Global and Category stats pages only
if (@!$_GET['pid']) {
    echo '<br />';
    $bb = new BorderBox(!empty($_GET['cid']) ? 'Category Statistics for: <i><a href="packages.php?catpid='.$_GET['cid'].'&amp;catname='.str_replace(' ', '+', $category_name).'">' . $category_name . '</a></i>' : 'Global Statistics');
    ?>
<table border="0" width="100%">
 <tr>
  <td style="width: 25%;">Total&nbsp;Packages:</td>
  <td align="center" style="width: 25%; background-color: #CCCCCC;"><?php echo $total_packages; ?></td>
  <td style="width: 25%;">Total&nbsp;Releases:</td>
  <td align="center" style="width: 25%; background-color: #CCCCCC;"><?php echo $total_releases; ?></td>
 </tr>
 <tr>
  <td style="width: 25%;">Total&nbsp;Maintainers:</td>
  <td align="center" style="width: 25%; background-color: #CCCCCC;"><?php echo $total_maintainers; ?></td>
  <td style="width: 25%;">Total&nbsp;Categories:</td>
  <td align="center" style="width: 25%; background-color: #CCCCCC;"><?php echo $total_categories; ?></td>
 </tr>
    <?php
     if(empty($_GET['cid'])) {
         echo " <tr>\n  <td width=\"25%\">Total&nbsp;Downloads:</td>\n  <td width=\"25%\" align=\"center\" bgcolor=\"#cccccc\">$total_downloads</td>\n </tr>\n";
     }
   ?>
</table>
    <?php
    $bb->end();

    echo '<br />';

    $bb = new BorderBox('Package Statistics');

    $statement  = $database->run($query); //$query defined above
    $results = $statement->fetchAll();

    if (PEAR::isError($statement)) {
        PEAR::raiseError('unable to generate stats');
    }

    echo " <table border=\"0\" width=\"100%\" cellpadding=\"2\" cellspacing=\"2\">\n";
    echo "  <tr align=\"left\" bgcolor=\"#cccccc\">\n";
    echo "   <th>Package Name</th>\n";
    echo '   <th><span class="accesskey"># of downloads</span></th>' . "\n";
    echo "   <th>&nbsp;</th>\n";
    echo "  </tr>\n";

    $lastPackage = "";

    foreach ($results as $row) {
        if ($row['package'] == $lastPackage) {
            $row['package'] = '';
        } else {
            $lastPackage = $row['package'];
            $row['package'] = '<a href="/package/' .
                                $row['package'] . '">' .
                                $row['package'] . "</a>";
        }

        echo "  <tr bgcolor=\"#eeeeee\">\n";
        echo "   <td>" . $row['package'] .  "</td>\n";
        echo "   <td>" . number_format($row['dl_number'], 0, '.', ',') . "</td>\n";
        echo '   <td>[<a href="/package-stats.php?cid='.$row['cid'].'&amp;pid='.$row['pid'].'">Details</a>]</td>'."\n";
        echo "  </tr>\n";
    }
    echo " </table>\n";

    $bb->end();
}

response_footer();
