<?php
/*
 * Drop all accounts not active in any package and not having a SVN account
 * Author: pierre@php.net
 */
/* $Id$ */

include __DIR__ . '/../include/pear-config.php';
$svn_accounts = json_decode(file_get_contents(SVN_USERLIST), true);


$dh = new PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);

$sql = 'select handle from users  where registered=1 and handle NOT IN (select handle from maintains)';
$res = $dh->query($sql);
$sql_del = 'DELETE FROM users WHERE handle=';
$del = 0;
foreach ($res as $row) {
	if (!isset($svn_accounts[$row['handle']])) {
		$res = $dh->query($sql_del . "'" . $row['handle'] . "'");
		if ($res) $del++;
	}
}
echo "$del accounts deleted.\n";
