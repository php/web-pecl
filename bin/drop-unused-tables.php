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
 * Drop unused tables
 */

require_once __DIR__.'/../include/bootstrap.php';

$drop_elections = '
DROP TABLE IF EXISTS
elections, election_votes_single, election_votes_multiple, election_votes_abstain, election_results, election_handle_votes, election_choices, election_account_request, zendinfo, trackbacks, apidoc_queue, tagnames, tag_package_link, `comments`, manual_notes
';

$res = $database->query($sql);
