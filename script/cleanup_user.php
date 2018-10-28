#!/usr/bin/env php
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
  | Authors: Pierre Joye <pierre@php.net>                                |
  +----------------------------------------------------------------------+
*/

/**
 * Drop all accounts not active in any package and not having a SVN account
 */

include __DIR__ . '/../include/pear-config.php';
$svnusers = '/home/pierre/project/pecl/migration/svnusers';
$svn_accounts = file($svnusers);
function nonl(&$var) {$var = str_replace(["\n","\r", "\r\n"], '', $var);}
array_walk($svn_accounts, 'nonl');

$sql = 'select handle from users  where handle NOT IN (select handle from maintains)';

$dh = new PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);

$res = $dh->query($sql);
$sql_del = 'DELETE FROM users WHERE handle=';
$del = 0;
foreach ($res as $row) {
	if (!in_array($row['handle'], $svn_accounts)) {
		$res = $dh->query($sql_del . "'" . $row['handle'] . "'");
		if ($res) $del++;
	}
}
echo "$del accounts deleted.\n";
