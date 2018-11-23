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
  |          Martin Jansen <mj@php.net>                                  |
  |          Pierre Joye <pierre@php.net>                                |
  |          Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

/**
 * This is an application and infrastructure configuration file with sensible
 * default configuration settings. These settings can be overridden either via
 * the environment variables in any environment or in the config/app_prod.php
 * file for production when environment variables can't be set. In the
 * development and testing environments the .env* files are used that simulate
 * the environment variables.
 */

return [
    // Application environment (dev for development, prod for production.)
    'env' => isset($_SERVER['PECL_ENV']) ? $_SERVER['PECL_ENV'] : 'prod',

    // PECL channel URL scheme (http or https)
    'scheme' => isset($_SERVER['PECL_SCHEME']) ? $_SERVER['PECL_SCHEME'] : 'https',

    // PECL channel URL host (domain name)
    'host' => isset($_SERVER['PECL_HOST']) ? $_SERVER['PECL_HOST'] : 'pecl.php.net',

    // Database username
    'db_username' => isset($_SERVER['PECL_DB_USERNAME']) ? $_SERVER['PECL_DB_USERNAME'] : 'nobody',

    // Database password
    'db_password' => isset($_SERVER['PECL_DB_PASSWORD']) ? $_SERVER['PECL_DB_PASSWORD'] : 'password',

    // Database name
    'db_name' => isset($_SERVER['PECL_DB_NAME']) ? $_SERVER['PECL_DB_NAME'] : 'pecl',

    // Database host
    'db_host' => isset($_SERVER['PECL_DB_HOST']) ? $_SERVER['PECL_DB_HOST'] : 'localhost',

    // Database DSN string. Optional and can be overridden by the environment
    // variable. Setting the DSN string also overrides other db_* values. Naming
    // PEAR_DATABASE_DSN key is used historically until production can be changed.
    'db_dsn' => isset($_SERVER['PEAR_DATABASE_DSN']) ? $_SERVER['PEAR_DATABASE_DSN'] : '',

    // REST static files directory. The PEAR_ prefix for the key is used
    // historically from the pearweb application until production can be updated.
    'rest_dir' => isset($_SERVER['PEAR_REST_DIR']) ? $_SERVER['PEAR_REST_DIR'] : __DIR__.'/../public_html/rest',

    // Temporary generated application files
    'tmp_dir' => isset($_SERVER['PECL_TMP_DIR']) ? $_SERVER['PECL_TMP_DIR'] : __DIR__.'/../var',

    // Temporary directory for uploaded files
    'tmp_uploads_dir' => isset($_SERVER['PECL_TMP_UPLOADS_DIR']) ? $_SERVER['PECL_TMP_UPLOADS_DIR'] : __DIR__.'/../var/uploads',

    // Path where new PECL account requests are sent when requesting a SVN account
    'php_master_api_url' => isset($_SERVER['PECL_MASTER_API_URL']) ? $_SERVER['PECL_MASTER_API_URL'] : 'https://master.php.net/entry/svn-account.php',

    // Packages directory
    'packages_dir' => isset($_SERVER['PECL_PACKAGES_DIR']) ? $_SERVER['PECL_PACKAGES_DIR'] : __DIR__.'/../public_html/packages',

    // Regex pattern for matching valid PECL accounts usernames
    'valid_usernames_regex' => '/^[a-z][a-z0-9]+$/i',
];
