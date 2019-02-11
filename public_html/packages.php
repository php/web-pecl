<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
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

use App\Utils\Breadcrumbs;
use App\Utils\Pagination;

require_once __DIR__.'/../include/pear-prepend.php';

$scriptName = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);

/**
* Returns an appropriate query string for a self referencing link
*/
function getQueryString($catpid, $catname, $showempty = false, $moreinfo = false)
{
    $querystring = [];
    $count = 0;

    if ($catpid) {
        $querystring[] = 'catpid='.(int)$catpid;
        $count++;
    }

    if ($catname) {
        $querystring[] = 'catname='.urlencode($catname);
        $count++;
    }

    if ($showempty) {
        $querystring[] = 'showempty='.(int)$showempty;
        $count++;
    }

    if ($moreinfo) {
        $querystring[] = 'moreinfo='.(int)$moreinfo;
        $count++;
    }

    if ($count) {
        return '?'.implode('&amp;', $querystring);
    } else {
        return '';
    }
}

// Check input variables. Expected url vars: catpid (category parent id), catname, showempty
$moreinfo = isset($_GET['moreinfo']) ? (int) $_GET['moreinfo'] : false;
$catpid  = isset($_GET['catpid']) ? (int) $_GET['catpid'] : null;
$showempty = isset($_GET['showempty']) ? (bool) $_GET['showempty'] : false;

if (empty($catpid)) {
    $categoryWhere = 'IS NULL';
    $catname = 'Top Level';
} else {
    $categoryWhere = '= '.$catpid;

    if (isset($_GET['catname']) && preg_match('/^[0-9a-z_ ]{1,80}$/i', $_GET['catname'])) {
        $catname = $_GET['catname'];
    } else {
        $catname = '';
    }
}

// The user is already at the top level
if (empty($catpid)) {
    $showEmptyLink = 'Top Level';
} else {
    $showEmptyLink = '<a href="'.$scriptName.getQueryString($catpid, $catname, !$showempty, $moreinfo).'">'.($showempty ? 'Hide empty' : 'Show empty').'</a>';
}

// Main part of script
if ($catpid) {
    $catname = $database->run('SELECT name FROM categories WHERE id=:id', [':id' => $catpid])->fetch()['name'];
    $categoryTitle = "Package Browser :: " . htmlspecialchars($catname, ENT_QUOTES);
} else {
    $categoryTitle = 'Package Browser :: Top Level';
}

// 1) Show categories of this level
$statement = $database->run("SELECT c.*, COUNT(p.id) AS npackages
                   FROM categories c
                   LEFT JOIN packages p ON p.category = c.id
                   WHERE p.package_type = 'pecl'
                   AND c.parent $categoryWhere
                   GROUP BY c.id ORDER BY name");

// Get names of sub-categories
$subcats = $database->run("SELECT p.id AS pid, c.id AS id, c.name AS name, c.summary AS summary
                          FROM categories c, categories p
                          WHERE p.parent $categoryWhere
                          AND c.parent = p.id ORDER BY c.name")->fetchAll();

// Get names of sub-packages
$subpkgs = $database->run("SELECT p.category, p.id AS id, p.name AS name, p.summary AS summary
                          FROM packages p, categories c
                          WHERE c.parent $categoryWhere
                          AND p.package_type = 'pecl'
                          AND p.category = c.id ORDER BY p.name")->fetchAll();

$maxSubLinks = 4;
$totalPackages = 0;
$categories = [];

foreach ($statement->fetchAll() as $row) {
    // Show only categories with packages
    if (!$showempty && $row['npackages'] < 1) {
        continue;
    }

    $subLinks = [];

    foreach ($subcats as $subcat) {
        if ($subcat['pid'] === $row['id']) {
            $subLinks[] = '<b><a href="'.$scriptName.'?catpid='.$subcat['id'].'&amp;catname='
                         . urlencode($subcat['name'])
                         . '" title="'.htmlspecialchars($subcat['summary'], ENT_QUOTES).'">'
                         . $subcat['name'].'</a></b>';
            if (count($subLinks) >= $maxSubLinks) {
                break;
            }
        }
    }

    foreach ($subpkgs as $subpkg) {
        if ($subpkg['category'] === $row['id']) {
            $subLinks[] = '<a href="/package/'.$subpkg['name'].'" title="'
                         . htmlspecialchars($subpkg['summary'], ENT_QUOTES).'">'.$subpkg['name'].'</a>';
            if (count($subLinks) >= $maxSubLinks) {
                break;
            }
        }
    }

    if (count($subLinks) >= $maxSubLinks) {
        $subLinks = implode(', ', $subLinks).' <img src="/img/caret-r.gif" alt="[more]">';
    } else {
        $subLinks = implode(', ', $subLinks);
    }

    settype($row['npackages'], 'string');

    $data  = '<font size="+1"><b><a href="'.$scriptName.'?catpid='.$row['id'].'&amp;catname='.urlencode($row['name']).'">'.$row['name'].'</a></b></font> ('.$row['npackages'].')<br>';
    $data .= $subLinks.'<br>';
    $categories[] = $data;
    $totalPackages += $row['npackages'];
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
                                       $scriptName,
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
    $currentPage = isset($_GET['pageID']) ? (int) $_GET['pageID'] : 1;
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

        $prev = '<a href="'.$link.'"><img src="/img/prev.gif" width="10" height="10" border="0" alt="&lt;&lt;" />Back</a>';
    }

    $next = '';
    if ($to < $total) {
        $nextPage = $currentPage + 1;

        $link = str_replace('pageID='.$currentPage, '', $_SERVER['REQUEST_URI']);
        if (strpos($_SERVER['REQUEST_URI'], 'pageID') === false) {
            $link .= '&';
        }
        $link .= 'pageID='.$nextPage;

        $next = '<a href="'.$link.'">Next<img src="/img/next.gif" width="10" height="10" border="0" alt="&gt;&gt;" /></a>';
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

echo $template->render('pages/packages.php', [
    'title' => $categoryTitle,
    'breadcrumbs' => $container->get(Breadcrumbs::class)->getBreadcrumbs($catpid, false),
    'showEmptyLink' => $showEmptyLink,
    'categories' => $categories,
    'catpid' => $catpid,
    'packages' => isset($packages) ? $packages : null,
    'subCategories' => isset($subCategories) ? $subCategories : null,
    'hideMoreInfoLink' => $hideMoreInfoLink,
    'showMoreInfoLink' => $showMoreInfoLink,
    'prev' => isset($prev) ? $prev : null,
    'from' => isset($from) ? $from : null,
    'to' => isset($to) ? $to : null,
    'total' => isset($total) ? $total : null,
    'next' => isset($next) ? $next : null,
    'defaultMoreInfoVis' => isset($defaultMoreInfoVis) ? $defaultMoreInfoVis : null,
    'totalPackages' => $totalPackages,
    'catname' => $catname,
]);
