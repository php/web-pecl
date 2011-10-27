<?php
/*
 * Drop unused tables 
 * Author: pierre@php.net
 */
/* $Id: drop_unused_tables.php 292139 2009-12-14 20:12:55Z pajoye $ */

include __DIR__ . '/../include/pear-config.php';

$drop_elections = '
DROP TABLE IF EXISTS
elections, election_votes_single, election_votes_multiple, election_votes_abstain, election_results, election_handle_votes, election_choices, election_account_request, zendinfo, trackbacks, apidoc_queue, tagnames, tag_package_link, `comments`, manual_notes
';

$dh = new PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);
$res = $dh->query($sql);


