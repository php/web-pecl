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

use App\Utils\Pagination;

$script_name = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);

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

// The user is already at the top level
if (empty($catpid)) {
    $showempty_link = 'Top Level';
} else {
    $showempty_link = '<a href="'. $script_name . getQueryString($catpid, $catname, !$showempty, $moreinfo) . '">' . ($showempty ? 'Hide empty' : 'Show empty').'</a>';
}

// Main part of script
if ($catpid) {
    $catname = $database->run('SELECT name FROM categories WHERE id=:id', [':id' => $catpid])->fetch()['name'];
    $category_title = "Package Browser :: " . htmlspecialchars($catname, ENT_QUOTES);
} else {
    $category_title = 'Package Browser :: Top Level';
}

response_header($category_title);

// 1) Show categories of this level
$statement = $database->run("SELECT c.*, COUNT(p.id) AS npackages
                   FROM categories c
                   LEFT JOIN packages p ON p.category = c.id
                   WHERE p.package_type = 'pecl'
                   AND c.parent ".$category_where."
                   GROUP BY c.id ORDER BY name");

// Get names of sub-categories
$subcats = $database->run("SELECT p.id AS pid, c.id AS id, c.name AS name, c.summary AS summary".
                          "  FROM categories c, categories p ".
                          " WHERE p.parent $category_where ".
                          "   AND c.parent = p.id ORDER BY c.name")->fetchAll();

// Get names of sub-packages
$subpkgs = $database->run("SELECT p.category, p.id AS id, p.name AS name, p.summary AS summary".
                          "  FROM packages p, categories c".
                          " WHERE c.parent $category_where ".
                          "   AND p.package_type = 'pecl' ".
                          "   AND p.category = c.id ORDER BY p.name")->fetchAll();

$max_sub_links = 4;
$totalpackages = 0;
$categories = [];

foreach ($statement->fetchAll() as $row) {
    extract($row);

    // Show only categories with packages
    if (!$showempty AND $row['npackages'] < 1) {
        continue;
    }

    $sub_links = [];

    foreach ($subcats as $subcat) {
        if ($subcat['pid'] === $row['id']) {
            $sub_links[] = '<b><a href="'.$script_name.'?catpid='.$subcat['id'].'&amp;catname='
                         . urlencode($subcat['name'])
                         . '" title="'.htmlspecialchars($subcat['summary'], ENT_QUOTES).'">'
                         . $subcat['name'].'</a></b>';
            if (count($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    foreach ($subpkgs as $subpkg) {
        if ($subpkg['category'] === $row['id']) {
            $sub_links[] = '<a href="/package/'.$subpkg['name'].'" title="'
                         . htmlspecialchars($subpkg['summary'], ENT_QUOTES).'">'.$subpkg['name'].'</a>';
            if (count($sub_links) >= $max_sub_links) {
                break;
            }
        }
    }

    if (count($sub_links) >= $max_sub_links) {
        $sub_links = implode(', ', $sub_links).' <img src="/img/caret-r.gif" alt="[more]">';
    } else {
        $sub_links = implode(', ', $sub_links);
    }

    settype($npackages, 'string');

    $data  = '<font size="+1"><b><a href="'. $script_name .'?catpid='.$id.'&amp;catname='.urlencode($name).'">'.$name.'</a></b></font> ('.$npackages.')<br />';
    $data .= $sub_links.'<br />';

    $categories[] = $data;

    $totalpackages += $npackages;
}

// Begin code for showing packages if we aren't at the top level.
if (!empty($catpid)) {
    // Subcategories list
    $minPackages = ($showempty) ? 0 : 1;
    $sql = "SELECT id, name, summary FROM categories WHERE parent = :parent AND npackages >= :min_packages";
    $arguments = [
        ':parent' => $catpid,
        ':min_packages' => $minPackages
    ];
    $subcats = $database->run($sql, $arguments)->fetchAll();

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

    // Paging
    $total = $database->run("SELECT count(*) FROM packages WHERE category = ? AND package_type='pecl'", [$catpid])->fetchColumn();
    $pagination = new Pagination();
    $pagination->setNumberOfItems($total);
    $currentPage = isset($_GET['pageID']) ? (int)$_GET['pageID'] : 1;
    $pagination->setCurrentPage($currentPage);
    $from = $pagination->getFrom();
    $to = $pagination->getTo();

    $sql = "SELECT id, name, summary, license
            FROM packages
            WHERE category = :category_id AND package_type = 'pecl'
            ORDER BY name
            LIMIT :limit OFFSET :offset";

    $packages = $database->run($sql, [
        ':category_id' => $catpid,
        ':limit' => $pagination->getItemsPerPage(),
        ':offset' => $from - 1,
    ])->fetchAll();

    $prev = '';
    if ($currentPage > 1) {
        $previousPage = $currentPage - 1;

        $link = str_replace('pageID='.$currentPage, '', $_SERVER['REQUEST_URI']);
        if (strpos($_SERVER['REQUEST_URI'], 'pageID') === false) {
            $link .= '&';
        }
        $link .= 'pageID='.$previousPage;

        $prev = '<a href="'.$link.'"><img src="img/prev.gif" width="10" height="10" border="0" alt="&lt;&lt;" />Back</a>';
    }

    $next = '';
    if ($to < $total) {
        $nextPage = $currentPage + 1;

        $link = str_replace('pageID='.$currentPage, '', $_SERVER['REQUEST_URI']);
        if (strpos($_SERVER['REQUEST_URI'], 'pageID') === false) {
            $link .= '&';
        }
        $link .= 'pageID='.$nextPage;

        $next = '<a href="'.$link.'">Next<img src="img/next.gif" width="10" height="10" border="0" alt="&gt;&gt;" /></a>';
    }

    foreach ($packages as $key => $pkg) {
        $extendedInfo['numReleases'] = $database->run('SELECT COUNT(*) AS count FROM releases WHERE package = ?', [$pkg['id']])->fetch()['count'];
        $extendedInfo['status']      = $database->run('SELECT state FROM releases WHERE package = ? ORDER BY id DESC LIMIT 1', [$pkg['id']])->fetch()['state'];
        $extendedInfo['license']     = $database->run('SELECT license FROM packages WHERE id = ? ORDER BY id DESC LIMIT 1', [$pkg['id']])->fetch()['license'];

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
