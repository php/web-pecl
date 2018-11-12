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
 * This is a distributed application and infrastructure configuration file.
 * These settings can be overridden either via the config/app_prod.php file in
 * production or the environment variables.
 */

return [
    // Application environment (dev for development, prod for production.)
    'env' => isset($_SERVER['PECL_ENV']) ? $_SERVER['PECL_ENV'] : 'prod',

    // PECL channel URL scheme (http or https)
    'scheme' => isset($_SERVER['PECL_SCHEME']) ? $_SERVER['PECL_SCHEME'] : 'https',

    // PECL channel URL host (domain name)
    'host' => isset($_SERVER['PECL_HOST']) ? $_SERVER['PECL_HOST'] : 'pecl.php.net',

    // REST static files directory
    'rest_dir' => isset($_SERVER['PECL_REST_DIR']) ? $_SERVER['PECL_REST_DIR'] : __DIR__.'/../public_html/rest',

    // Temporary directory for uploaded files
    'tmp_uploads_dir' => isset($_SERVER['PECL_TMP_UPLOADS_DIR']) ? $_SERVER['PECL_TMP_UPLOADS_DIR'] : __DIR__.'/../var/uploads',

    // Regex pattern for matching valid PECL accounts usernames
    'valid_usernames_regex' => '/^[a-z][a-z0-9]+$/i',
];
