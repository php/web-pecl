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

require_once 'DB.php';
require_once __DIR__.'/../include/pear-prepend.php';
require_once __DIR__.'/../include/pear-database.php';

if(!ini_get('register_globals')){
    extract($_SERVER);
}

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "data_error_handler");
list($progname, $type, $user, $pass, $db) = $argv;
$dbh = DB::connect("$type://$user:$pass@localhost/$db");
$dbh->query('SET NAMES utf8');
$me = getenv("USER");
$now = gmdate("Y-m-d H:i:s");

include __DIR__.'/addusers.php';
include __DIR__.'/addcategories.php';
include __DIR__.'/addpackages.php';
include __DIR__.'/addacls.php';

function data_error_handler($obj) {
    print "Error when adding users: ";
    print $obj->getMessage();
    print "\nMore info: ";
    print $obj->getUserInfo();
    print "\n";
    exit;
}
