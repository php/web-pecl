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
$letter = strtolower(filter_input(INPUT_GET, 'letter', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z]?$/"))));
$paging_page_size = 20;

if (empty($paging_offset)) {
    $paging_offset = 1;
}

$account_total = $dbh->getOne("SELECT COUNT(handle) FROM users WHERE registered = 1");
$total_page = ceil($account_total / $paging_page_size);

// Calculation of the offset if a letter is specified
if (!empty($letter)) {
    $arrayLetters = $dbh->getAll('SELECT SUBSTRING(handle,1,1) AS letter, COUNT(SUBSTRING(handle,1,1)) AS nb FROM users WHERE registered = 1 GROUP BY letter ORDER BY letter');
    $bFound = false;
    $nbLettersBeforeChosenOne = 0;
    foreach ($arrayLetters as $item) {
        $existing_firstletters[] = strtolower($item[0]);
        if (strtolower($item[0]) == $letter) {
            $bFound = true;
        } else {
            if (!$bFound) $nbLettersBeforeChosenOne += $item[1];
        }
    }

    $paging_offset = floor($nbLettersBeforeChosenOne / $paging_page_size) + 1;

} else {
    $existing_firstletters = $dbh->getCol('SELECT DISTINCT SUBSTRING(handle,1,1) AS letter FROM users WHERE registered = 1 ORDER BY letter');
}

$result = $dbh->limitQuery('SELECT handle, name, homepage, wishlist, email, showemail FROM users WHERE registered = 1 ORDER BY handle',
                        ($paging_offset-1) * $paging_page_size, $paging_page_size);
$account_list_result = array();
while($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) $account_list_result[] = $row;

$data = array(
    'account_list_result' => $account_list_result,
    'existing_firstletters' => $existing_firstletters,
    'letter' => $letter,
    'page' => $paging_offset,
    'total_page' => $total_page,
    'account_total' => $account_total
);

$page = $twig->render('developers.html.twig', $data);
echo $page;
