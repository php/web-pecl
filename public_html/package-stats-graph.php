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
   | Authors: Richard Heyes <richard@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
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

    include ("../include/jpgraph/jpgraph.php");
    include ("../include/jpgraph/jpgraph_bar.php");

/**
* Cache time in secs
*/
	$cache_time = 300;

/**
* This is the x axis labels. May change when
* selectable dates is added.
*/
    for ($i=date('n'), $year=date('Y'); count(@$x_axis) < 12; $i--) {
        if ($i == 0) {
            $i = 12;
            $year--;
        }
        $x_axis[$i] = date("M", mktime(0,0,0,$i,1,$year));
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

	foreach ($releases as $release) {
		$y_axis = array();
		list($rid, $colour) = explode('_', $release);
		$colour = '#' . $colour;
		foreach (array_keys($x_axis) as $key) {
			$y_axis[$key] = 0;
		}

	    $sql = sprintf("SELECT UNIX_TIMESTAMP(d.dl_when) AS date, COUNT(*) AS downloads
	                      FROM packages p, downloads d
	                     WHERE d.package = p.id
	                       AND p.id = %s
	                       %s
	                  GROUP BY MONTH(d.dl_when)
	                  ORDER BY YEAR(d.dl_when) DESC, MONTH(d.dl_when) DESC",
	                   $_GET['pid'],
	                   $release_clause = $rid > 0 ? 'AND d.release = ' . $rid : '');

	    if ($result = $dbh->query($sql)) {
	        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
	            $y_axis[date('n', $row['date'])] = $row['downloads'];
	        }
	    }

            // Create the bar plot
            $bplots[$rid] = new BarPlot(array_reverse(array_values($y_axis)));
            $bplots[$rid]->SetWidth(0.6);
            $bplots[$rid]->SetFillGradient("white", $colour, GRAD_HOR);
            //$bplot->setFillColor("#339900");
            $bplots[$rid]->SetColor("black");
            $bplots[$rid]->value->setFormat('%d'); 
            $bplots[$rid]->value->Show();
	}

    $x_axis = array_reverse(array_values($x_axis));
	$bplots = array_values($bplots);

	/**
    * Get package name
    */
	$package_name = $dbh->getOne('SELECT name FROM packages WHERE id = ' . $_GET['pid']);
	$package_rel  = !empty($_GET['rid']) ? $dbh->getOne('SELECT version FROM releases WHERE id = ' . $_GET['rid']) : '';

/**
* Go through setting up the graph
*/
	if (!DEVBOX) {
		// Send some caching headers to prevent unnecessary requests
		header('Last-Modified: ' . date('r', md5($_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'])));
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
