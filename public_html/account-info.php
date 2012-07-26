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
   $Id: account-info.php 317017 2011-09-19 18:26:50Z pajoye $
*/

/**
 * Details about PEAR accounts
 */

$handle = filter_input(INPUT_GET, 'handle', FILTER_SANITIZE_STRING);
/**
 * Redirect to the accounts list if no handle was specified
 */
if (empty($handle)) {
    localRedirect("/accounts.php");
}

$user_info = $dbh->getRow("SELECT * FROM users WHERE registered = 1 " .
    "AND handle = ?", array($handle), DB_FETCHMODE_OBJECT);

if ($user_info === null) {
    // XXX: make_404();
    //    PEAR::raiseError("No account information found!");
    echo $twig->render('404.html.twig');
    exit();
}

$access = $dbh->getCol("SELECT path FROM cvs_acl WHERE username = ?", 0,
    array($handle));

if ($user_info->homepage != "") {
    $url = parse_url($user_info->homepage);
    if (empty($url['scheme'])) {
        $user_info->homepage = 'http://' . $user_info->homepage;
    }
}

$query = "SELECT p.id, p.name, m.role
          FROM packages p, maintains m
          WHERE m.handle = '$handle'
          AND p.id = m.package
          ORDER BY p.name";

$packages = $dbh->getAll($query, NULL, DB_FETCHMODE_OBJECT);

$data = array(
    'user_info' => $user_info,
    'packages' => $packages,
);

//$page = new PeclPage();
//$page->title = 'PECL :: Account ' . $user_info->handle;
//$page->setTemplate(PECL_TEMPLATE_DIR . '/account-info.html');
//$page->addData($data);
//$page->saveTo(PECL_STATIC_HTML_DIR . '/user/' . $user_info->handle . '.html');
//$page->render();
//echo $page->html;

$page = $twig->render('account-info.html.twig', $data);
file_put_contents(__DIR__ . '/static/user/' . $user_info->handle . ".html", $page);
echo $page;