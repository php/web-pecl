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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id: 404.php 317276 2011-09-25 13:40:05Z pajoye $
*/

include_once 'twig.inc.php';

function create_html_file(Twig_Environment $twig, $name, $pages) {
    $buffer = $twig->render($name . '.html.twig', array('title' => $pages[$name]));
    file_put_contents(PECL_STATIC_HTML_DIR . '/' . $name . '.html', $buffer);
    echo $buffer;
}

$pages = array (
    'copyright' => 'Copyright and License',
    'about'     => 'About',
    'about/privacy'   => 'Privacy',
    'about/damblan'   => 'About Damblan',
    'support'   => 'Support',
    'takeover'  => 'Takeover a package',
    '404'       => 'Ooops cannot find this page',
    'dtd'       => 'Document Type Definitions',
    'feeds'      => 'Syndication Feeds ss',
);

$url = $_SERVER['SCRIPT_URL'];

if ($url{0} == '/') {
    $last = strlen($url) - 1;

    if ($url{$last} == '/') {
        $page_name = substr($url, 1, $last - 1);
    } else {
        $page_name = substr($url, 1, $last);
    }

    if (isset($pages[$page_name])) {
        create_html_file($twig, str_replace('/', '_', $page_name), $pages);
        return;
    }
}

/* fail over and try to find a pkg using the name or display the search result */
include PECL_INCLUDE_DIR . '/pear-database-package.php';

/**
 * On 404 error this will search for a package with the same
 * name as the requested document. Thus enabling urls such as:
 *
 * http://pear.php.net/Mail_Mime
 */

/**
 * Requesting something like /~foobar will redirect to the account
 * information page of the user "foobar".
 */
if (strlen($_SERVER['REDIRECT_URL']) > 0 && $_SERVER['REDIRECT_URL']{1} == '~') {
    $user = substr($_SERVER['REDIRECT_URL'], 2);
    if (preg_match(PEAR_COMMON_USER_NAME_REGEX, $user) && user::exists($user)) {
        localRedirect("/user/" . urlencode($user));
    }
}

$pkg = strtr($_SERVER['REDIRECT_URL'], '-','_');
$pinfo_url = '/package/';

// Check strictly
$name = package::info(basename($pkg), 'name');
if (!DB::isError($name) && !empty($name)) {
    if (!empty($name)) {
        localRedirect($pinfo_url . $name);
    } else {
        $name = package::info(basename($pkg), 'name', true);
        if (!empty($name)) {
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: http://pear.php.net/package/' . $name);
            header('Connection: close');
            exit();
        }
    }
}

// Check less strictly if nothing has been found previously
$sql = "SELECT p.id, p.name, p.summary
            FROM packages p
            WHERE package_type = 'pecl' AND approved = 1 AND name LIKE ?
            ORDER BY p.name LIMIT 0, 5";
$term = "%" . basename($pkg) . "%";

$packages = $dbh->getAll($sql, array($term), DB_FETCHMODE_ASSOC);

$data = array();
if (count($packages) > 1) {
	$show_search_link = true;
    $data['packages'] = $packages;
} else {
	$show_search_link = false;
}
$data['pinfo_url'] = $pinfo_url;
$data['show_search_link'] = $show_search_link;

echo $twig->render('404.html.twig', array(
    'title' => '404 Page Not found',
    'data' => $data,
    'uri' => $_SERVER['REQUEST_URI'],
    'basename' => basename($_SERVER['REQUEST_URI'])));
