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
 * Main application configuration settings.
 */

return [
    // Application environment (dev for development, prod for production, etc.)
    'env' => 'prod',

    // Regex pattern for matching valid PECL accounts usernames
    'valid_usernames_regex' => '/^[a-z][a-z0-9]+$/i',

    // PECL channel URL scheme (http or https)
    'scheme' => 'https',

    // PECL channel URL host (domain name)
    'host' => 'pecl.php.net',
];
