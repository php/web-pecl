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
  | Authors: Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

/**
* TODO
* o Number of packages in brackets does not include packages in subcategories
* o Make headers in package list clickable for ordering
*/

$script_name = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);

require_once 'Pager/Pager.php';

/**
* Returns an appropriate query string for a self referencing link
*/
function getQueryString($catpid, $catname, $showempty = false, $moreinfo = false)
{
    $querystring = [];
    $entries_cnt = 0;

    if ($catpid) {
        $querystring[] = 'catpid='.(int)$catpid;
        $entries_cnt++;
    }

    if ($catname) {
        $querystring[] = 'catname='.urlencode($catname);
        $entries_cnt++;
    }

    if ($showempty) {
        $querystring[] = 'showempty='.(int)$showempty;
        $entries_cnt++;
    }

    if ($moreinfo) {
        $querystring[] = 'moreinfo='.(int)$moreinfo;
        $entries_cnt++;
    }


    if ($entries_cnt) {
        return '?'.implode('&amp;', $querystring);
    } else {
        return '';
    }
}

// Check input variables. Expected url vars: catpid (category parent id), catname, showempty
$moreinfo = isset($_GET['moreinfo']) ? (int)$_GET['moreinfo'] : false;
$catpid  = isset($_GET['catpid'])  ? (int)$_GET['catpid']   : null;
$showempty = isset($_GET['showempty']) ? (bool)$_GET['showempty'] : false;

if (empty($catpid)) {
    $category_where = "IS NULL";
    $catname = "Top Level";
} else {
    $category_where = "= " . $catpid;

    if (isset($_GET['catname']) && preg_match('/^[0-9a-z_ ]{1,80}$/i', $_GET['catname'])) {
        $catname = $_GET['catname'];
    } else {
        $catname = '';
    }
}

// the user is already at the top level
if (empty($catpid)) {
    $showempty_link = 'Top Level';
} else {
    $showempty_link = '<a href="'. $script_name . getQueryString($catpid, $catname, !$showempty, $moreinfo) . '">' . ($showempty ? 'Hide empty' : 'Show empty').'</a>';
}

/*
 * Main part of script
 */

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

if ($catpid) {
    $catname = $dbh->getOne('SELECT name FROM categories WHERE id=' . $catpid);
    $category_title = "Package Browser :: " . htmlspecialchars($catname, ENT_QUOTES);
} else {
    $category_title = 'Package Browser :: Top Level';
}

response_header($category_title);

// 1) Show categories of this level
$sth = $dbh->query('SELECT c.*, COUNT(p.id) AS npackages' .
                   ' FROM categories c' .
                   ' LEFT JOIN packages p ON p.category = c.id ' .
                   " WHERE p.package_type = 'pecl'" .
                   "  AND c.parent $category_where " .
                   ' GROUP BY c.id ' .
                   'ORDER BY name');

// Get names of sub-categories
$subcats = $dbh->getAssoc("SELECT p.id AS pid, c.id AS id, c.name AS name, c.summary AS summary".
                          "  FROM categories c, categories p ".
                          " WHERE p.parent $category_where ".
                          "   AND c.parent = p.id ORDER BY c.name",
                          false, null, DB_FETCHMODE_ASSOC, true);

// Get names of sub-packages
$subpkgs = $dbh->getAssoc("SELECT p.category, p.id AS id, p.name AS name, p.summary AS summary".
                          "  FROM packages p, categories c".
                          " WHERE c.parent $category_where ".
                          "   AND p.package_type = 'pecl' ".
                          "   AND p.category = c.id ORDER BY p.name",
                          false, null, DB_FETCHMODE_ASSOC, true);

$max_sub_links = 4;
$totalpackages = 0;
$categories = [];

while ($sth->fetchInto($row)) {
    extract($row);
    $ncategories = ($cat_right - $cat_left - 1) / 2;

    // Show only categories with packages
    if (!$showempty AND $npackages < 1) {
        continue;
    }

    $sub_links = [];
    if (isset($subcats[$id])) {
        foreach ($subcats[$id] as $subcat) {
            $sub_links[] = '<b><a href="'. $script_name .'?catpid='.$subcat['id'].'&amp;catname='.
                            urlencode($subcat['name']).'" title="'.htmlspecialchars($subcat['summary'], ENT_QUOTES).'">'.$subcat['name'].'</a></b>';
            if (sizeof($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    if (isset($subpkgs[$id])) {
        foreach ($subpkgs[$id] as $subpkg) {
            $sub_links[] = '<a href="/package/' . $subpkg['name'] .'" title="'.
                            htmlspecialchars($subpkg['summary'], ENT_QUOTES).'">'.$subpkg['name'].'</a>';
            if (sizeof($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    if (sizeof($sub_links) >= $max_sub_links) {
        $sub_links = implode(', ', $sub_links).' <img src="/gifs/caret-r.gif" alt="[more]">';
    } else {
        $sub_links = implode(', ', $sub_links);
    }

    settype($npackages, 'string');
    settype($ncategories, 'string');

    $data  = '<font size="+1"><b><a href="'. $script_name .'?catpid='.$id.'&amp;catname='.urlencode($name).'">'.$name.'</a></b></font> ('.$npackages.')<br />';
    $data .= $sub_links.'<br />';

    $categories[] = $data;

    $totalpackages += $npackages;
}

// Begin code for showing packages if we aren't at the top level.
if (!empty($catpid)) {
    // Subcategories list
    $minPackages = ($showempty) ? 0 : 1;
    $subcats = $dbh->getAll("SELECT id, name, summary FROM categories WHERE " .
                            "parent = $catpid AND npackages >= $minPackages", DB_FETCHMODE_ASSOC);

    if (count($subcats) > 0) {
        foreach ($subcats as $subcat) {
            $subCategories[] = sprintf('<b><a href="%s?catpid=%d&catname=%s" title="%s">%s</a></b>',
                                       $script_name,
                                       $subcat['id'],
                                       urlencode($subcat['name']),
                                       htmlspecialchars($subcat['summary'], ENT_QUOTES),
                                       $subcat['name']);
        }

        $subCategories = implode(', ', $subCategories);
    }

    // Package list
    $packages = $dbh->getAll("SELECT id, name, summary, license FROM packages WHERE category=$catpid AND package_type = 'pecl' ORDER BY name");

    // Paging
    $total = count($packages);
    $pager = Pager::factory(['totalItems' => $total, 'perPage' => 15]);
    list($first, $last) = $pager->getOffsetByPageId();
    list($prev, $pages, $next) = $pager->getLinks('<nobr><img src="gifs/prev.gif" width="10" height="10" border="0" alt="&lt;&lt;" />Back</nobr>', '<nobr>Next<img src="gifs/next.gif" width="10" height="10" border="0" alt="&gt;&gt;" /></nobr>');

    $currentPage = $pager->getCurrentPageID();
    $numPages    = $pager->numPages();
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

    $defaultMoreInfoVis = $moreinfo ? 'inline' : 'none';
}

// Build URLs for hide/show all links
if ($moreinfo) {
    $showMoreInfoLink = '#';
    $hideMoreInfoLink = getQueryString($catpid, $catname, $showempty, 0);
} else {
    $showMoreInfoLink = getQueryString($catpid, $catname, $showempty, 1);
    $hideMoreInfoLink = '#';
}

// Template
include __DIR__.'/../templates/packages.php';
