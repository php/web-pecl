<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Utils;

/**
 * Service class to convert DSN string to array.
 */
class DsnConverter
{
    /**
     * Convert DSN string 'scheme://username:password@host/database' to array.
     */
    public function toArray($dsn)
    {
        $array = [
            'scheme' => '',
            'username' => '',
            'password' => '',
            'host' => '',
            'database' => '',
        ];

        $scheme = preg_split('/\:\/\//', $dsn, -1, PREG_SPLIT_NO_EMPTY);
        $array['scheme'] = $scheme[0];

        $username = preg_split('/\:/', $scheme[1], -1, PREG_SPLIT_NO_EMPTY);
        $array['username'] = $username[0];

        $password = preg_split('/\@/', $username[1], -1, PREG_SPLIT_NO_EMPTY);
        $array['password'] = $password[0];

        $host = preg_split('/\//', $password[1], -1, PREG_SPLIT_NO_EMPTY);
        $array['host'] = $host[0];
        $array['database'] = $host[1];

        return $array;
    }
}
