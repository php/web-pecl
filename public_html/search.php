<?php
/**
 * Search interface for the PEAR website
 *
 * $Id$
 */
if (!isset($HTTP_POST_VARS['search_in'])) {
    response_header("Search");
    echo "<h2>Search</h2>\n";
    echo "<font color=\"#990000\"><b>Please use the search system via the search form above.</b></font>\n";
    response_footer();
    exit();
}

switch ($HTTP_POST_VARS['search_in']) {

	case "packages":
		header('Location: /package-search.php?pkg_name='.urlencode($HTTP_POST_VARS['search_string']).'&bool=AND&submit=Search');
		exit;
		break;

    case "pear-dev" :
    case "pear-cvs" :
    case "pear-general" :
        /**
         * We forward the query to the mailing list archive
         * at marc.thaimsgroup.com
         */
        $location = "http://marc.theaimsgroup.com/";
        $query = "l=".$HTTP_POST_VARS['search_in']."&w=2&r=1&q=b&s=".urlencode($HTTP_POST_VARS['search_string']);
        header("Location: ".$location."?".$query);
        
        break;

    default :
        response_header("Search");
        echo "<h2>Search</h2>\n";
        echo "<font color=\"#990000\"><b>Invalid search target.</b></font>\n";
        response_footer();
        break;
}
?>
