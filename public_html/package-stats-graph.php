<?php
/**
* Filename.......: package-stats-graph.php
* Project........: Pearweb
* Last Modified..: $Date$
* CVS Revision...: $Revision$
*
* Use JPGraph library to display graphical
* stats of downloads.
*
* TODO:
*  o Dropdown on stats page to determine between
*    monthly/weekly stats
*  o Multiple releases per graph, ie side by side
*    bar chart.
*/

	include ("../jpgraph/jpgraph.php");
	include ("../jpgraph/jpgraph_bar.php");

/**
* Determine the stats based on the supplied
* package id (pid) and release id (rid).
* Release id may be empty implying all releases.
*/
	for ($i=date('n'), $year=date('Y'); count(@$data_x) < 12; $i--) {
		if ($i == 0) {
			$i = 12;
			$year--;
		}
		$data_x[$i] = date("M", mktime(0,0,0,$i,1,$year));
		$data_y[$i] = 0;
	}

	$sql = sprintf("SELECT UNIX_TIMESTAMP(d.dl_when) AS date, COUNT(*) AS downloads
	                  FROM packages p, downloads d
	                 WHERE d.package = p.id
	                   AND p.id = %s
	                   %s
	              GROUP BY MONTH(d.dl_when)
	              ORDER BY YEAR(d.dl_when) DESC, MONTH(d.dl_when) DESC",
				   $_GET['pid'],
	               $release_clause = !empty($_GET['rid']) ? ' AND d.release = ' . $_GET['rid'] : '');

	if ($result = $dbh->query($sql)) {
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
			$data_y[date('n', $row['date'])] = $row['downloads'];
		}
	}

	$data_x = array_reverse(array_values($data_x));
	$data_y = array_reverse(array_values($data_y));

/**
* Go through setting up the graph
*/
	// Main graph object
	$graph = new Graph(543,200,"jabba", 5);	
	$graph->img->SetMargin(40,20,30,30);
	$graph->SetScale("textlin");
	$graph->SetMarginColor("#cccccc");

	// Set up the title for the graph
	$graph->title->Set("Download statistics for " . $dbh->getOne('SELECT name FROM packages WHERE id = ' . $_GET['pid']));
	$graph->title->SetColor("black");

	// Show 0 label on Y-axis (default is not to show)
	$graph->yscale->ticks->SupressZeroLabel(false);

	// Setup X-axis labels
	$graph->xaxis->SetTickLabels($data_x);

	// Create the bar pot
	$bplot = new BarPlot($data_y);
	$bplot->SetWidth(0.6);
	$bplot->SetFillGradient("white","#339900",GRAD_HOR);
	//$bplot->setFillColor("#339900");

	// Set color for the frame of each bar
	$bplot->SetColor("black");
	$graph->Add($bplot);

	// Finally send the graph to the browser
	$graph->Stroke();
?>
