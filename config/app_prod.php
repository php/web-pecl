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
 * Main application production settings that override default distributed
 * config/app.php settings. This allows to have configuration in the file or using
 * the environment variables.
 */

return [
    // Database username
    'db_username' => isset($_SERVER['PECL_DB_USERNAME']) ? $_SERVER['PECL_DB_USERNAME'] : 'pear',

    // Database password
    'db_password' => isset($_SERVER['PECL_DB_PASSWORD']) ? $_SERVER['PECL_DB_PASSWORD'] : 'pear',

    // Database name
    'db_name' => isset($_SERVER['PECL_DB_NAME']) ? $_SERVER['PECL_DB_NAME'] : 'pear',

    // Database host
    'db_host' => isset($_SERVER['PECL_DB_HOST']) ? $_SERVER['PECL_DB_HOST'] : 'localhost',

    // REST static files directory
    'rest_dir' => isset($_SERVER['PECL_REST_DIR']) ? $_SERVER['PECL_REST_DIR'] : '/var/lib/peclweb/rest',

    // Temporary generated application files
    'tmp_dir' => isset($_SERVER['PECL_TMP_DIR']) ? $_SERVER['PECL_TMP_DIR'] : '/var/tmp/pear',

    // Temporary directory for uploaded files
    'tmp_uploads_dir' => isset($_SERVER['PECL_TMP_UPLOADS_DIR']) ? $_SERVER['PECL_TMP_UPLOADS_DIR'] : '/var/tmp/pear/uploads',

    // Packages directory
    'packages_dir' => isset($_SERVER['PECL_PACKAGES_DIR']) ? $_SERVER['PECL_PACKAGES_DIR'] : '/var/lib/pear',
];
