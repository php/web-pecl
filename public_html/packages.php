<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
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

$template_dir = __DIR__ . '/../templates/';
$script_name = htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES);


require_once('HTML/Table.php');
require_once('Pager/Pager.php');
require_once('Net/URL.php');

/**
* Returns an appropriate query string
* for a self referencing link
*/
function getQueryString($catname, $showempty = false, $moreinfo = false)
{
    $querystring = '/packages';

    if ($catname) {
        $querystring .= '/' . urldecode($catname);
    }

    if ($showempty) {
        $querystring .= '?showempty=1';
    }

    if ($moreinfo) {
        $querystring .= ($showempty ? '&' : '?' ). 'moreinfo=1';
    }

    return $querystring;
}

$category_name = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);
$category_name = preg_replace('/[^a-z0-9]/i', '', $category_name);

// the user is already at the top level
if (empty($category_name)) {
    $toplevel = true;
    $category_name = 'Top Level';
    $category_where = 'IS NULL';
    $category_title = "Top Level";
    $catpid = NULL;
} else {
    $toplevel = false;
    $category = $dbh->getRow('SELECT * FROM categories WHERE name="' . $dbh->escapeSimple($category_name) . '" ORDER BY name;', null, DB_FETCHMODE_ASSOC);
    $category_where = '=' . $category['id'];
    $category_title = $category_name;
}


/*
 * Main part of script
 */

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);
$extra_styles = array('/css/packages.css');
response_header($category_title);

$total_packages = 0;

if (!$toplevel) {
    $total_packages += $category['npackages'];

 $sql = '
            SELECT
                p.id, p.name, p.summary, p.license, p.unmaintained, p.newpk_id,
                (SELECT COUNT(package) FROM releases WHERE package = p.id) AS numreleases,
                (SELECT state FROM releases WHERE package = p.id ORDER BY id DESC LIMIT 1) AS status,
                (SELECT version FROM releases WHERE package = p.id ORDER BY id DESC LIMIT 1) AS version,
                (SELECT releasedate FROM releases WHERE package = p.id ORDER BY id DESC LIMIT 1) AS releasedate
            FROM packages p
            WHERE
              category=' . $category['id'];
    $category_package = $dbh->getAll($sql);

    $category_subcategory = $dbh->getAll('SELECT id, name, npackages FROM categories WHERE parent=' . $category['id'] . ' ORDER BY name;', null, DB_FETCHMODE_ASSOC);

    if ($category_subcategory) {
        foreach ($category_subcategory as $subcat) {
            $sub_category_package[$subcat['name']] = $dbh->getAll('SELECT name, summary, license FROM packages WHERE category=' . $subcat['id'] . ' ORDER BY name limit 0,' . $subcat['npackages']);
            $total_packages += $subcat['npackages'];
        }
        reset($category_subcategory);
    } else {
        $category_subcategory = NULL;
    }

    include($template_dir . 'packages-list.html');

} else {

    $categories = $dbh->getAll('SELECT c.*, COUNT(p.id) AS npackages' .
                       ' FROM categories c' .
                       ' LEFT JOIN packages p ON p.category = c.id ' .
                       " WHERE p.package_type = 'pecl'" .
                       "  AND c.parent $category_where " .
                       ' GROUP BY c.id ' .
                       'ORDER BY name');

    foreach ($categories as $category) {
        $category_package[$category['name']] = $dbh->getAll('SELECT name, summary, license FROM packages WHERE category=' . $category['id'] . ' ORDER BY name limit 0,' . $category['npackages']);
        $total_packages += $category['npackages'];

        $subcats = $dbh->getAll('SELECT name FROM categories WHERE parent=' . $category['id'] . ' ORDER BY name;',
                              null, DB_FETCHMODE_ASSOC);

        if ($subcats) {
            $category_subcategory[$category['name']] = $subcats;
        } else {
            $category_subcategory[$category['name']] = array();
        }
    }
    reset($categories);

    include($template_dir . 'packages.html');
}
response_footer();