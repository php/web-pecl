<?php
/**
 * Search interface for the PEAR website
 *
 * $Id$
 */
switch ($HTTP_POST_VARS['search_in']) {

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
        response_footer();
        break;
}
?>
