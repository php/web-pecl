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
   |          Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id: package-search.php 317298 2011-09-25 23:33:00Z pajoye $
*/

require_once dirname(__FILE__) . '/../include/pear-database-category.php';

/**
 * Searches for packages matching various user definable
 * criteria including:
 *  o Name
 *  o Maintainer
 *  o Category
 *  o Release date (on/before/after)
 */


$data = array();

/**
 * Code to fetch the current category list
 */
$category_rows = category::listAll();
$data['category_rows'] = $category_rows;

/**
 * Fetch list of users/maintainers
 */
$users = $dbh->getAll('SELECT u.handle, u.name FROM users u, maintains m WHERE u.handle = m.handle GROUP BY handle ORDER BY u.name', DB_FETCHMODE_ASSOC);
$data['users'] = $users;

/**
 * Is form submitted? Do search and show
 * results.
 */
if (!empty($_GET)) {
    $search_date          = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_NUMBER_INT);
    $search_date_type     = filter_input(INPUT_GET, 'date_type', FILTER_SANITIZE_STRING);
    $search_maintainer    = filter_input(INPUT_GET, 'maintainer', FILTER_SANITIZE_STRING);
    $search_name_contains = filter_input(INPUT_GET, 'keywords', FILTER_SANITIZE_STRING);
    $search_category      = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);

    $data['search']['date'] = $search_date;
    $data['search']['date_type'] = $search_date_type;
    $data['search']['keywords'] = $search_name_contains;
    $data['search']['category'] = $search_category;
    $data['search']['maintainer'] = $search_maintainer;

    $dbh->setFetchmode(DB_FETCHMODE_ASSOC);
    $where = array();

    // Build package name part of query
    if (!empty($search_name_contains)) {
        $where[] = '(name LIKE' . $dbh->quote('%' . $search_name_contains . '%') . ' OR p.summary LIKE ' . $dbh->quote('%' . $search_name_contains . '%') . ')';
    }

    // Build maintainer part of query
    if (!empty($search_maintainer)) {
        $where[] = sprintf("handle LIKE %s", $dbh->quote('%' . $search_maintainer . '%'));
    }

    // Build category part of query
    if (!empty($search_category)) {
        $where[] = sprintf("category = %s", $dbh->quote($search_category));
    }

    /**
     * Any release date checking?
     */
    $release_join = '';
    if (!empty($search_date)) {
        $release_join = ', releases r';
        $where[]      = "p.id = r.package";
        switch ($search_date_type) {
            case 'before':
                $where[] = 'r.releasedate < ' . "'$search_date'";
                break;

            case 'after':
                $where[] = 'r.releasedate >= ' . "'$search_date'";
                break;

            case 'on':
                $where[] = 'r.releasedate = ' . "'$search_date'";
            default:
                break;
        }
    }

    // Compose query and execute
    $where = !empty($where) ? 'AND ' . implode(' AND ', $where) : '';
    $sql   = "SELECT DISTINCT p.id,
                          p.name,
                          p.category,
                          p.summary
                     FROM packages p,
                          maintains m
                          $release_join
                    WHERE p.id = m.package " . $where .
        " ORDER BY p.name ASC";

    $result = $dbh->query($sql);

    // Run through any results
    $data['searched'] = true;
    $data['result'] = array();

    if (($numrows = $result->numRows()) > 0) {
        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)){
            $row['raw_name'] = $row['name'];
            if (!empty($_GET['keywords'])) {
                $row['name']    = str_ireplace($_GET['keywords'], '<span style="background-color: #d5ffc1">' . $_GET['keywords'] . '</span>', strip_tags($row['name']));
                $row['summary'] = str_ireplace($_GET['keywords'], '<span style="background-color: #d5ffc1">' . $_GET['keywords'] . '</span>', strip_tags($row['summary']));
            }

            $data['result'][] = $row;
        }
    }
}

$page = $twig->render('package-search.html.twig', $data);

echo $page;