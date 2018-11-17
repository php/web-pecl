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
  | Authors: Stig S. Bakken <ssb@fast.no>                                |
  +----------------------------------------------------------------------+
*/

/**
 * Debugging and development script to export certain data from database.
 */

require_once __DIR__.'/../include/bootstrap.php';

if ($argc < 2) {
    die('Please provide argument what you want to output'."\n");
}

$what = $argv[1];

if ($what === 'avail') {
    echo "unavail\n";

    $statement = $database->query("SELECT username, path FROM cvs_acl");

    $results = [];

    foreach ($statement->fetchAll() as $row) {
        if (isset($results[$row['path']])) {
            $results[$row['path']] .= ','.$row['username'];
        } else {
            $results[$row['path']] = $row['username'];
        }
    }

    foreach ($results as $path => $users) {
        echo "avail|$users|$path\n";
    }
} elseif ($what === 'cvsusers') {
    $statement = $database->query("SELECT handle, name, email FROM users");

    foreach ($statement->fetchAll() as $row) {
        echo implode(":", $row) . "\n";
    }
} elseif ($what === 'passwd') {
    $statement = $database->query("SELECT handle, password FROM users");

    foreach ($statement->fetchAll() as $row) {
        echo implode(":", $row) . ":cvs\n";
    }
} elseif ($what === 'writers') {
    $statement = $database->query("SELECT DISTINCT username FROM cvs_acl WHERE access = 1");

    foreach ($statement->fetchAll() as $row) {
        echo $row['username']."\n";
    }
}
