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
 * Drop all karma not used by pecl and migrate pecl/pear.* to * only
 */

require_once __DIR__.'/../include/bootstrap.php';

$dh = new \PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);

$sql = "update karma set level='developer' where level='pear.dev' or level='pecl.dev';";
$res = $dh->query($sql);

$sql = "update karma set level='admin' where level='pear.admin';";
$res = $dh->query($sql);

$sql = "delete from karma where level='pear.pepr' or level='pear.pepr' or level='pear.pepr.admin' or level='pear.doc.chm-upload' or level='pear.election' or level='pear.planet.admin' or level='pear.group' or level='pear.voter';";
$res = $dh->query($sql);
