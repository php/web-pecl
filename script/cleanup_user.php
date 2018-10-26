<?php
/*
 * Drop all accounts not active in any package and not having a SVN account
 * Author: pierre@php.net
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
