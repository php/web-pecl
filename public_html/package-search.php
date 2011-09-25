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
   $Id$
*/

/**
* Searches for packages matching various user definable
* criteria including:
*  o Name
*  o Maintainer
*  o Category
*  o Release date (on/before/after)
*/

$template_dir = dirname(dirname(__FILE__)) . '/templates/';
require_once "HTML/Form.php";

$search_date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_NUMBER_INT);
$search_date_type = filter_input(INPUT_GET, 'date_type', FILTER_SANITIZE_STRING);
$search_maintainer = filter_input(INPUT_GET, 'maintainer', FILTER_SANITIZE_STRING);
$search_name_contains = filter_input(INPUT_GET, 'keywords', FILTER_SANITIZE_STRING);
$search_category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);

/**
* Setup code for the form
*/
$form = new HTML_Form('/package_search.php');

/**
* Code to fetch the current category list
*/
$category_rows = category::listAll();
if (!empty($_GET['pkg_category'])) {
    for ($i=0; $i<count($category_rows); $i++) {
        if ($_GET['pkg_category'] == $category_rows[$i]['id']) {
            $category_rows[$i]['selected'] = 'selected="selected"';
        }
    }
}

/**
* Fetch list of users/maintainers
*/
$users = $dbh->getAll('SELECT u.handle, u.name FROM users u, maintains m WHERE u.handle = m.handle GROUP BY handle ORDER BY u.name', DB_FETCHMODE_ASSOC);
for ($i=0; $i<count($users); $i++) {
    if (empty($users[$i]['name'])) {
        $users[$i]['name'] = $users[$i]['handle'];
    }
}

/**
* Is form submitted? Do search and show
* results.
*/
if (!empty($_GET)) {
    $dbh->setFetchmode(DB_FETCHMODE_ASSOC);
    $where = array();

    // Build package name part of query
    if (!empty($search_name_contains)) {
        $where[] = '(name LIKE'.$dbh->quote('%' . $search_name_contains . '%').' OR p.summary LIKE ' . $dbh->quote('%' . $search_name_contains . '%') . ')';
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
    if (!empty($search_date)) {
        $release_join        = '';
        $release_join = ', releases r';
        $where[] = "p.id = r.package";
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
    $where  = !empty($where) ? 'AND '.implode(' AND ', $where) : '';
    $sql    = "SELECT DISTINCT p.id,
                          p.name,
                          p.category,
                          p.summary
                     FROM packages p,
                          maintains m
                          $release_join
                    WHERE p.id = m.package " . $where . 
                 " ORDER BY p.name DESC";

    $result = $dbh->query($sql);

    // Run through any results
    if (($numrows = $result->numRows()) > 0) {
    
        // Paging
        include_once('Pager/Pager.php');
        $params['itemData'] = range(0, $numrows - 1);
        $params['perPage'] = 20;
        $pager = Pager::factory($params);
        list($from, $to) = $pager->getOffsetByPageId();
        $links = $pager->getLinks('<img src="gifs/prev.gif" border="0" alt="&lt;&lt;" width="10" height="10">Prev', 'Next<img src="gifs/next.gif" border="0" alt="&gt;&gt;" width="10" height="10">');
    
        // Row number
        $rownum = $from - 1;

        /**
        * Title html for results borderbox obj
        * Eww.
        */
        $title_html  = sprintf('<table border="0" width="100%%" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td align="left" width="50"><nobr>%s</nobr></td>
                                            <td align="center"><nobr>Search results (%s - %s of %s)</nobr></td>
                                            <td align="right" width="50"><nobr>%s</nobr></td>
                                        </tr>
                                    </table>',
                               $links['back'],
                               $from,
                               $to,
                               $numrows,
                               $links['next']);

        while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC, $rownum++) AND $rownum <= $to) {
			/**
            * If name or summary was searched on, highlight the search string
            */
			$row['raw_name']    = $row['name'];
			if (!empty($_GET['pkg_name'])) {
				$row['name']    = str_ireplace($_GET['pkg_name'], '<span style="background-color: #d5ffc1">'.$_GET['pkg_name'].'</span>', $row['name']);
				$row['summary'] = str_ireplace($_GET['pkg_name'], '<span style="background-color: #d5ffc1">'.$_GET['pkg_name'].'</span>', $row['summary']);
			}

            $search_results[] = $row;
        }
    }    
}

/**
 * Template stuff
 */
include($template_dir . 'package-search.html');
