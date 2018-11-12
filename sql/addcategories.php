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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

use App\Category;

require_once __DIR__.'/../include/bootstrap.php';

print "Adding categories...\n";

$dbh->expectError(DB_ERROR_NOSUCHTABLE);
$dbh->query('DELETE FROM categories');
$dbh->dropSequence('categories');
$dbh->popExpect();

$categories = '
Authentication;;
Benchmarking;;
Caching;;
Configuration;;
Console;;
Encryption;;
Database;;
Date and Time;;
File System;;
HTML;;
HTTP;;
Images;;
Logging;;
Mail;;
Math;;
Networking;;
Numbers;;
Payment;;
PEAR;PEAR infrastructure;
Scheduling;;
Science;;
XML;;
XML-RPC;;XML
';

$catids = [];
foreach (explode("\n", $categories) as $line) {
    if (trim($line) == '') {
        continue;
    }
    list($name, $desc, $parent) = explode(";", trim($line));
    $params = ['name' => $name, 'desc' => $desc];
    if (!empty($parent)) {
        $params['parent'] = $catids[$parent];
    }
    $catid = Category::add($params);
    $catids[$name] = $catid;
    print "Category: $name\n";
}
