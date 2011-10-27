<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Pierre Joye <pierre@php.net>                                |
   +----------------------------------------------------------------------+
   $Id: accounts.php 317314 2011-09-26 13:23:30Z pajoye $
*/

$paging_offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
$letter = filter_input(INPUT_GET, 'letter', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z]?$/")));
$paging_page_size = 20;

if (is_null($paging_offset)) {
    $paging_offset = 0;
}

$existing_firstletters = $dbh->getCol(' SELECT DISTINCT(SUBSTRING(handle,1,1)) as letter  FROM users WHERE registered = 1 ORDER BY letter');
$account_total = $dbh->getOne("SELECT COUNT(handle) FROM users WHERE registered = 1");


if (!empty($letter)) {
    /* No paging when only a give letter is displayed */
    $account_filter_total = $dbh->getOne('SELECT COUNT(handle) FROM users WHERE SUBSTRING(handle,1,1)=' . "'$letter'");

    $account_list_result = $dbh->limitQuery('SELECT handle, name, homepage, wishlist FROM users WHERE SUBSTRING(handle,1,1)=' .
                                 "'" . $letter . "'" . 'ORDER BY handle', 0, $paging_page_size);

} else {
	$account_list_result = $dbh->limitQuery('SELECT handle, name, homepage, wishlist FROM users WHERE registered = 1 ORDER BY handle',
	                        $paging_offset, $paging_page_size);
    $account_filter_total = $account_total;

    $last_shown = $paging_offset + $paging_page_size - 1;

    if (($paging_offset - $paging_page_size) > 0) {
        $last = $paging_offset - $paging_page_size;
        $paging_prev_link = '/accounts.php?offset=' . $last;
    } else {
        $last = 0;
        $paging_prev_link = '/accounts.php';
    }

    if ($paging_offset == 0) {
        $paging_prev_link = NULL;
    }

    $paging_next_offset = $paging_offset + $paging_page_size;
    $paging_next_page_total = $account_filter_total - $paging_offset;
    if ($paging_next_page_total > $account_filter_total) {
        $paging_next_page_total = $account_filter_total - $paging_offset;
    }
    if ($paging_next_offset > $account_filter_total) {
        $paging_next_link = NULL;
    } else {
        $paging_next_link =  '/accounts.php?offset=' . $paging_next_offset;
    }
}

if (empty($letter) && ($paging_offset + $paging_page_size < $account_filter_total)) {
	$paging_next_page_total = min($paging_page_size, $account_filter_total - $paging_offset);
    $paging_last_in_page = min($paging_offset + $paging_page_size, $account_filter_total);
}

$data = array(
    'account_list_result' => $account_list_result,
    'existing_firstletters' => $existing_firstletters,
    'letter' => $letter,
    'account_filter_total' => $account_filter_total,

    'paging_next_page_total' => $paging_next_page_total,
    'paging_last_in_page' => $paging_last_in_page,
    'paging_next_link' => $paging_next_link,
    'paging_next_offset' => $paging_next_offset,
    'paging_prev_link' => $paging_prev_link,
    'paging_last_in_page' =>$paging_last_in_page,
    'paging_prev_link' => $paging_prev_link,
    'paging_page_size' => $paging_page_size,
    'paging_last_in_page' => $paging_last_in_page,
    'paging_offset' => $paging_offset,
    'account_total' => $account_total,
);

$page = new PeclPage();
$page->title = 'Developers';
$page->setTemplate(PECL_TEMPLATE_DIR . '/account-browser.html');
$page->addData($data);
$page->render();

echo $page->html;

