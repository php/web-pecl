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
  |          Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

/**
* Searches for packages matching various user definable
* criteria including:
*  o Name
*  o Maintainer
*  o Category
*  o Release date (on/before/after)
*/

require_once 'HTML/Form.php';
require_once 'Pager/Pager.php';

/**
* Setup code for the form
*/
$form = new HTML_Form(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES));

/**
* Months for released date dropdowns
*/
$months     = [];
$months[1]  = 'January';
$months[2]  = 'February';
$months[3]  = 'March';
$months[4]  = 'April';
$months[5]  = 'May';
$months[6]  = 'June';
$months[7]  = 'July';
$months[8]  = 'August';
$months[9]  = 'September';
$months[10] = 'October';
$months[11] = 'November';
$months[12] = 'December';

/**
* Code to fetch the current category list
*/
$category_rows = Category::listAll();
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
$numrows = 0;
if (!empty($_GET)) {
    $dbh->setFetchmode(DB_FETCHMODE_ASSOC);
    $where = [];

    // Build package name part of query
    if (!empty($_GET['pkg_name'])) {
        $where[] = '(name LIKE'.$dbh->quote('%'.$_GET['pkg_name'].'%').' OR summary LIKE '.$dbh->quote('%'.$_GET['pkg_name'].'%').')';
    }

    // Build maintainer part of query
    if (!empty($_GET['pkg_maintainer'])) {
        $where[] = sprintf("handle LIKE %s", $dbh->quote('%' . $_GET['pkg_maintainer'] . '%'));
    }

    // Build category part of query
    if (!empty($_GET['pkg_category'])) {
        $where[] = sprintf("category = %s", $dbh->quote($_GET['pkg_category']));
    }

    /**
     * Any release date checking?
     */
    $release_join        = '';
    $set_released_on     = false;
    $set_released_before = false;
    $set_released_since  = false;
    // RELEASED_ON
    if (!empty($_GET['released_on_year']) AND !empty($_GET['released_on_month']) AND !empty($_GET['released_on_day'])) {
        $release_join = ', releases r';
        $where[] = "p.id = r.package";
        $where[] = sprintf("DATE_FORMAT(r.releasedate, '%%Y-%%m-%%d') = '%04d-%02d-%02d'",
                           (int)$_GET['released_on_year'],
                           (int)$_GET['released_on_month'],
                           (int)$_GET['released_on_day']);
        $set_released_on = true;

    } else {
        // RELEASED_BEFORE
        if (!empty($_GET['released_before_year']) AND !empty($_GET['released_before_month']) AND !empty($_GET['released_before_day'])) {
            $release_join = ', releases r';
            $where[] = "p.id = r.package";
            $where[] = sprintf("DATE_FORMAT(r.releasedate, '%%Y-%%m-%%d') <= '%04d-%02d-%02d'",
                               (int)$_GET['released_before_year'],
                               (int)$_GET['released_before_month'],
                               (int)$_GET['released_before_day']);
            $set_released_before = true;
        }

        // RELEASED_SINCE
        if (!empty($_GET['released_since_year']) AND !empty($_GET['released_since_month']) AND !empty($_GET['released_since_day'])) {
            $release_join = ', releases r';
            $where[] = "p.id = r.package";
            $where[] = sprintf("DATE_FORMAT(r.releasedate, '%%Y-%%m-%%d') >= '%04d-%02d-%02d'",
                               (int)$_GET['released_since_year'],
                               (int)$_GET['released_since_month'],
                               (int)$_GET['released_since_day']);
            $set_released_since = true;
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
                    WHERE p.id = m.package " . $where . "
                 AND p.package_type='pecl'
                 ORDER BY p.name LIKE ".$dbh->quote('%'.$_GET['pkg_name'].'%')." DESC, p.name";

    $result = $dbh->query($sql);

    // Run through any results
    if (($numrows = $result->numRows()) > 0) {

        // Paging
        $params['itemData'] = range(0, $numrows - 1);
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
include __DIR__.'/../templates/package-search.php';
