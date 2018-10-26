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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
*/

if (!isset($_POST['search_in'])) {
    response_header("Search");
    echo "<h2>Search</h2>\n";
    echo "<font color=\"#990000\"><b>Please use the search system via the search form above.</b></font>\n";
    response_footer();
    exit();
}

switch ($_POST['search_in']) {

	case "packages":
		header('Location: /package-search.php?pkg_name='.urlencode($_POST['search_string']).'&bool=AND&submit=Search');
		exit;
		break;

    case 'developers':
        // XXX: Enable searching for names instead of handles
        localRedirect('/user/' . urlencode($_POST['search_string']));
        break;

    case "pecl-dev" :
    case "pecl-cvs" :
        /**
         * We forward the query to the mailing list archive
         * at marc.thaimsgroup.com
         */
        $location = "http://marc.info/";
        $query = "l=".$_POST['search_in']."&w=2&r=1&q=b&s=".urlencode($_POST['search_string']);
        header("Location: ".$location."?".$query);

        break;

    case 'site':
        header('Location: http://google.com/search?as_sitesearch=pecl.php.net'
               . '&as_q=' . urlencode($_POST['search_string']));
        break;

    default :
        response_header("Search");
        echo "<h2>Search</h2>\n";
        echo "<font color=\"#990000\"><b>Invalid search target.</b></font>\n";
        response_footer();
        break;
}
