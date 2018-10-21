<?php
/*
 * Drop all karma not used by pecl and migrate pecl/pear.* to * only
 * Author: pierre@php.net
 */

include __DIR__ . '/../include/pear-config.php';

$dh = new PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);

$sql = "update karma set level='developer' where level='pear.dev' or level='pecl.dev';";
$res = $dh->query($sql);

$sql = "update karma set level='admin' where level='pear.admin';";
$res = $dh->query($sql);

$sql = "delete from karma where level='pear.pepr' or level='pear.pepr' or level='pear.pepr.admin' or level='pear.doc.chm-upload' or level='pear.election' or level='pear.planet.admin' or level='pear.group' or level='pear.voter';";
$res = $dh->query($sql);
