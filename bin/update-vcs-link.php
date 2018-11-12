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

require_once __DIR__.'/../include/pear-config.php';

$sql_movetosvn = "UPDATE packages SET cvs_link =
IF (cvs_link REGEXP('cvs.php.net\/cvs.php(.*)'),
    REPLACE(cvs_link, 'cvs.php.net/cvs.php', 'svn.php.net'),
    IF (cvs_link REGEXP('cvs.php.net\/viewvc.cgi(.*)'),
        REPLACE(cvs_link, 'cvs.php.net/viewvc.cgi', 'svn.php.net'),
        IF (cvs_link REGEXP('cvs.php.net\/pecl(.*)'),
            REPLACE(cvs_link, 'cvs.php.net', 'svn.php.net'),
            IF (cvs_link REGEXP('viewcvs.php.net\/viewvc.cgi(.*)'),
                REPLACE(cvs_link, 'viewcvs.php.net/viewvc.cgi', 'svn.php.net'),
                IF (cvs_link REGEXP('cvs.php.net\/viewcvs.cgi(.*)'),
                    REPLACE(cvs_link, 'cvs.php.net/viewcvs.cgi', 'svn.php.net'),
                    IF (cvs_link REGEXP('cvs.php.net\/php-src(.*)'),
                        REPLACE(cvs_link, 'cvs.php.net/php-src', 'svn.php.net/php/php-src/trunk'),
                        cvs_link
                    )
                )
            )
        )
    )
)
where package_type='pecl' and cvs_link like '%cvs.php.net%';
";

$dh = new PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);

$res = $dh->query($sql_movetosvn);
if (!$res) {
    var_dump($dh->errorInfo());
}
