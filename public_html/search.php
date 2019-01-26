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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

require_once __DIR__.'/../include/pear-prepend.php';

$query = isset($_POST['search_string']) ? $_POST['search_string'] : '';
$type = isset($_POST['search_in']) ? $_POST['search_in'] : '';

switch ($type) {
    case 'packages':
        header('Location: /package-search.php?pkg_name='.urlencode($query));
        exit;
    break;

    case 'developers':
        // TODO: Enable searching for names instead of handles
        header('Location: /user/'.urlencode($query));
        exit;
    break;

    case 'pecl-dev':
    case 'pecl-cvs':
        // We forward the query to the mailing list archives at marc.info
        header('Location: https://marc.info/?l='.$type.'&w=2&r=1&q=b&s='.urlencode($query));
        exit;
    break;

    case 'site':
        header('Location: https://google.com/search?q=site%3Apecl.php.net+'.urlencode($query));
        exit;
    break;

    default:
        echo $template->render('pages/search.php', [
            'query' => $query,
        ]);
        exit;
    break;
}
