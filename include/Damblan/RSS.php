<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Author: Martin Jansen <mj@php.net>                                   |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "PEAR.php";
require_once "Damblan/Site.php";

/**
 * Class for generating RSS feeds
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @version $Revision$
 */
class Damblan_RSS {

    /**
     * Get RSS feed for given type
     *
     * @access public
     * @param  string Type
     * @return object Instance of Damblan_RSS_* or PEAR_Error
     */
    function getFeed($type) {
        $site = &Damblan_Site::factory();
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array(&$site, "error404"));

        if ($type == "latest") {
            require_once "Damblan/RSS/Latest.php";
            $rss_obj = new Damblan_RSS_Latest();
        } else if (substr($type, 0, 4) == "cat_") {
            require_once "Damblan/RSS/Category.php";
            $rss_obj = new Damblan_RSS_Category(substr($type, 4));
        } else if (substr($type, 0, 4) == "pkg_") {
            require_once "Damblan/RSS/Package.php";
            $rss_obj = new Damblan_RSS_Package(substr($type, 4));
        }

        if (isset($rss_obj)) {
            return $rss_obj;
        } else {
            PEAR::raiseError("The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.");
        }
    }

    /**
     * Print RSS feed for given type
     *
     * @access public
     * @param  string Type
     * @return void
     */
    function printFeed($type) {
        $ret =& Damblan_RSS::getFeed($type);

        if (PEAR::isError($ret)) {
            PEAR::raiseError($ret);
        } else {
            header("Content-Type: text/xml");
            print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
            print $ret->toString();
        }
    }
}
?>