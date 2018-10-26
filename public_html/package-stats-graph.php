<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Richard Heyes <richard@php.net>                             |
   +----------------------------------------------------------------------+
*/

/**
 * Use JPGraph library to display graphical
 * stats of downloads.
 *
 * TODO:
 *  o Dropdown on stats page to determine between
 *    monthly/weekly stats
 *  o Multiple releases per graph, ie side by side
 *    bar chart.
 */

include("../include/jpgraph/jpgraph.php");
include("../include/jpgraph/jpgraph_bar.php");

/**
 * Cache time in secs
 */
$cache_time = 300;

/**
 * This is the x axis labels. May change when
 * selectable dates is added.
 */
$year   = date('Y') - 1;
$month  = date('n') + 1;
$x_axis = [];
for ($i = 0; $i < 12; $i++) {
    $time = mktime(0, 0, 0, $month + $i, 1, $year);
    $x_axis[date('Ym', $time)] = date('M', $time);
}

/**
 * Determine the stats based on the supplied
 * package id (pid) and release id (rid).
 * If release id is empty a group bar chart is
 * drawn with each release having a different
 * color.
 */
if (!empty($_GET['releases'])) {
    $releases = explode(',', $_GET['releases']);
}

if (!isset($releases) || !is_array($releases)) {
    exit;
}

foreach ($releases as $release) {
    $y_axis = [];
    list($rid, $colour) = explode('_', $release);
    $colour = '#' . $colour;
    foreach (array_keys($x_axis) as $key) {
        $y_axis[$key] = 0;
    }

    $sql = sprintf("SELECT YEAR(yearmonth) AS dyear, MONTH(yearmonth) AS dmonth, SUM(downloads) AS downloads
                        FROM aggregated_package_stats a, releases r
                        WHERE a.package_id = %d
                            AND r.id = a.release_id
                            AND r.package = a.package_id
                            AND yearmonth > (now() - INTERVAL 1 YEAR)
                            %s
                        GROUP BY dyear, dmonth
                        ORDER BY dyear DESC, dmonth DESC",
                   (int) $_GET['pid'],
                   $release_clause = $rid > 0 ? 'AND a.release_id = ' . (int) $rid : '');

    if ($result = $dbh->query($sql)) {
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
            $key = sprintf('%04d%02d', $row['dyear'], $row['dmonth']);
            if (isset($y_axis[$key])) {
                $y_axis[$key] = $row['downloads'];
            }
        }
    }

    // Create the bar plot
    $bplots[$rid] = new BarPlot(array_values($y_axis));
    $bplots[$rid]->SetWidth(0.6);
    $bplots[$rid]->SetFillGradient("white", $colour, GRAD_HOR);
    //$bplot->setFillColor("#339900");
    $bplots[$rid]->SetColor("black");
    $bplots[$rid]->value->setFormat('%d');
    $bplots[$rid]->value->Show();
}

$x_axis = array_values($x_axis);
$bplots = array_values($bplots);

/**
 * Get package name
 */
$package_name = $dbh->getOne('SELECT name FROM packages WHERE id = ' . (int)$_GET['pid']);
$package_rel  = !empty($_GET['rid']) ? $dbh->getOne('SELECT version FROM releases WHERE id = ' . (int)$_GET['rid']) : '';

/**
 * Go through setting up the graph
 */
if (!DEVBOX) {
    // Send some caching headers to prevent unnecessary requests
    header('ETag: ' . md5($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']));
    header('Last-Modified: ' . date('c'));
    header('Expires: ' . date('r', time() + $cache_time));
    header('Cache-Control: public, max-age=' . $cache_time);
    header('Pragma: cache');

    // Main graph object
    $graph = new Graph(543, 200, md5($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING']), $cache_time);
} else {
    // Main graph object
    $graph = new Graph(543, 200);
}
$graph->img->SetMargin(40,20,30,30);
$graph->SetScale("textlin");
$graph->SetMarginColor("#cccccc");

// Set up the title for the graph
$graph->title->Set(sprintf("Download statistics for %s %s", $package_name, $package_rel));
$graph->title->SetColor("black");

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($x_axis);

// Add a spacing between the bars and the top of the graph
$graph->yaxis->scale->setGrace(15);

// Add the grouped or single bar chartplot
if (count($bplots) > 1) {
    $gbplot = new GroupBarPlot($bplots);
    $graph->Add($gbplot);
} else {
    $graph->Add($bplots[0]);
}

// Finally send the graph to the browser
$graph->Stroke();
?>
