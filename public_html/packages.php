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
   | Authors: Richard Heyes                                               |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
* TODO
* o Number of packages in brackets does not include packages in subcategories
* o Make headers in package list clickable for ordering
*/

$template_dir = dirname(dirname(__FILE__)) . '/templates/';

require_once('HTML/Table.php');
require_once('Pager/Pager.php');
require_once('Net/URL.php');

/**
* Returns an appropriate query string
* for a self referencing link
*/
function getQueryString($catpid, $catname, $showempty = false){
    if($catpid)
        $querystring[] = 'catpid='.$catpid;

    if($catname)
        $querystring[] = 'catname='.urlencode($catname);

    if($showempty)
        $querystring[] = 'showempty='.(int)$showempty;

    return '?'.implode('&', $querystring);
}

/**
* Check for the hide/show all extended info boxes
*/
if (!empty($_GET['hideMoreInfo'])) {
    setcookie('hideMoreInfo', '1', time() + (86400 * 7));
    localRedirect('packages.php' . getQueryString(@$_GET['catpid'], @$_GET['catname']));
} elseif (!empty($_GET['showMoreInfo'])) {
    setcookie('hideMoreInfo', '-1', time() + (86400 * 7));
    localRedirect('packages.php' . getQueryString(@$_GET['catpid'], @$_GET['catname']));
}

/**
* Check input variables
* Expected url vars: catpid (category parent id), catname, showempty
*/
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

// the user is already at the top level
if (empty($catpid)) {
    $showempty_link = 'Top Level';
} else {
    $showempty_link = '<a href="'.$_SERVER['PHP_SELF'].getQueryString($catpid, $catname, !$showempty).'">'.($showempty ? 'Hide empty' : 'Show empty').'</a>';
}

/**
* Main part of script
*/

response_header($category_title);

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

// 1) Show categories of this level

$sth     = $dbh->query("SELECT c.*, COUNT(p.id) AS npackages FROM categories c LEFT JOIN packages p ON p.category = c.id WHERE p.package_type = 'pecl' AND c.parent $category_where GROUP BY c.id ORDER BY name");
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
                          " WHERE c.parent $category_where AND p.category = c.id AND p.package_type = 'pecl' ORDER BY p.name",
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
            $sub_links[] = '<a href="/package/' . $subpkg['name'] .'" title="'.
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

/**
* Begin code for showing packages if we
* aren't at the top level.
*/
if (!empty($catpid)) {
    $nrow = 0;
    // Subcategories list
    $minPackages = ($showempty) ? 0 : 1;
    $subcats = $dbh->getAll("SELECT id, name, summary FROM categories WHERE " .
                            "parent = $catpid AND npackages >= $minPackages", DB_FETCHMODE_ASSOC);
    if (count($subcats) > 0) {
        foreach ($subcats as $subcat) {
            $subCategories[] = sprintf('<b><a href="%s?catpid=%d&catname=%s" title="%s">%s</a></b>',
                                       $_SERVER['PHP_SELF'],
                                       $subcat['id'],
                                       urlencode($subcat['name']),
                                       htmlspecialchars($subcat['summary']),
                                       $subcat['name']);
        }
        $subCategories = implode(', ', $subCategories);
    }
	
    // Package list
    $packages = $dbh->getAll("SELECT id, name, summary, license FROM packages WHERE category=$catpid AND package_type = 'pecl' ORDER BY name");
		
    // Paging
    $total = count($packages);
    $pager = new Pager(array('totalItems' => $total, 'perPage' => 15));
    list($first, $last) = $pager->getOffsetByPageId();
    list($prev, $pages, $next) = $pager->getLinks('<nobr><img src="gifs/prev.gif" width="10" height="10" border="0" alt="&lt;&lt;" />Back</nobr>', '<nobr>Next<img src="gifs/next.gif" width="10" height="10" border="0" alt="&gt;&gt;" /></nobr>');

    $packages = array_slice($packages, $first - 1, 15);
		
    foreach ($packages as $key => $pkg) {
        $extendedInfo['numReleases'] = $dbh->getOne('SELECT COUNT(*) FROM releases WHERE package = ' . $pkg['id']);
        $extendedInfo['status']      = $dbh->getOne('SELECT state FROM releases WHERE package = ' . $pkg['id'] . ' ORDER BY id DESC LIMIT 1');
        $extendedInfo['license']     = $dbh->getOne('SELECT license FROM packages WHERE id = ' . $pkg['id'] . ' ORDER BY id DESC LIMIT 1');
			

        // Make status coloured
        switch ($extendedInfo['status']) {
        case 'stable':
            $extendedInfo['status'] = '<span style="color: #006600">Stable</span>';
            break;

        case 'beta':
            $extendedInfo['status'] = '<span style="color: #ffc705">Beta</span>';
            break;
				
        case 'alpha':
            $extendedInfo['status'] = '<span style="color: #ff0000">Alpha</span>';
            break;
        }

        $packages[$key]['eInfo'] = $extendedInfo;
    }

    /**
     * More info visibility
     */		
    if (@$_COOKIE['hideMoreInfo'] == '1') {
        $defaultMoreInfoVis = 'none';

    } elseif (@$_COOKIE['hideMoreInfo'] == '-1') {
        $defaultMoreInfoVis = 'inline';

    } elseif ($_browser->is_ie5up) {
        $defaultMoreInfoVis = 'none';

    } else {
        $defaultMoreInfoVis = 'inline';
    }
}

/**
 * Build URLs for hide/show all links
 */
$url = new Net_URL();
$url->addQueryString('hideMoreInfo', '1');
$hideMoreInfoLink = $url->getURL();
	
$url->removeQueryString('hideMoreInfo');
$url->addQueryString('showMoreInfo', '1');
$showMoreInfoLink = $url->getURL();

/**
 * Template
 */
error_reporting(E_ALL & ~E_NOTICE);
include($template_dir . 'packages.html');

?>
