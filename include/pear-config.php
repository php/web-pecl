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

/**
 * The PEAR::DB DSN connection string
 *
 * To override default, set the value in $_ENV['PEAR_DATABASE_DSN'] before this
 * file is included.
 */
if (isset($_SERVER['PEAR_DATABASE_DSN'])) {
    define('PEAR_DATABASE_DSN', $_SERVER['PEAR_DATABASE_DSN']);
} else {
    define('PECL_DB_USER', 'pear');
    define('PECL_DB_PASSWORD', 'pear');
    define('PECL_DB_HOST', 'localhost');
    define('PECL_DB_NAME', 'pear');

    if (function_exists('mysql_connect')) {
        $driver = 'mysql';
    } elseif (function_exists('mysqli_connect')) {
        $driver = 'mysqli';
    }
    define('PEAR_DATABASE_DSN', $driver . '://' . PECL_DB_USER . ':' . PECL_DB_PASSWORD. '@' . PECL_DB_HOST. '/' . PECL_DB_NAME);
    define('PECL_DB_DSN', 'mysql:host=' . PECL_DB_HOST . ';dbname=' . PECL_DB_NAME);
}
