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

/**
 * Basic RSS feed caching
 *
 * The class looks for a file, whose name is determined by the given
 * parameter $file and in the directory DAMBLAN_RSS_CACHE_DIR. If that
 * file exists and it isn't older than DAMBLAN_RSS_CACHE_TIME (default
 * is 1800 seconds), it is used instead of performing the database queries.
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @category RSS
 * @version $Revision$
 */
class Damblan_RSS_Cache {

    /**
     * Determines if there is a valid cache file for the given feed
     *
     * @access public
     * @param  string Name of the feed
     * @return boolean
     */
    function isCached($file) {
        $file = DAMBLAN_RSS_CACHE_DIR . "/" . $file;

        if (file_exists($file)) {
            $ts = filemtime($file);

            // If the chache file is too old, don't use it
            $diff = time() - $ts;
            if ($diff <= DAMBLAN_RSS_CACHE_TIME) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the content of the cache file for the given feed
     *
     * @access public
     * @param  string Name of the feed
     * @return string
     */
    function get($file) {
        return @file_get_contents(DAMBLAN_RSS_CACHE_DIR . "/" . $file);
    }

    /**
     * Writes the content of a cache file
     *
     * @todo   Implement locking
     * @access public
     * @param  string Name of the feed
     * @param  string Content of the cache file
     * @return boolean
     */
    function write($file, $data) {
        $fp = @fopen(DAMBLAN_RSS_CACHE_DIR . "/" . $file, "w");
        if (!$fp) {
            return false;
        }

        if (!@fputs($fp, $data)) {
            return false;
        }
        fclose($fp);

        return true;
    }

    /**
     * Flushes the cache for the given feed
     *
     * @access public
     * @param  string Name of the feed
     * @return boolean
     */
    function flush($file) {
        return @unlink(DAMBLAN_RSS_CACHE_DIR . "/" . $file);
    }
}
?>
