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
  | Authors: Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

/**
 * Class to manage the PEAR Karma System
 *
 * This system makes it not only possible to provide a fully developed
 * permission system, but it also allows us to set up a php.net-wide
 * single-sign-on system some time in the future.
 */
class Damblan_Karma {

    var $_dbh;

    /**
     * Constructor
     *
     * @access public
     * @param  object Instance of PEAR::DB
     */
    public function __construct(&$dbh) {
        $this->_dbh = $dbh;
    }

    /**
     * Determine if the given user has karma for the given $level
     *
     * The given level is either a concrete karma level or an alias
     * that will be mapped to a karma group in this method.
     *
     * @access public
     * @param  string Username
     * @param  string Level
     * @return boolean
     */
    function has($user, $level) {
        switch ($level) {
        case "pear.pepr" :
        	$levels = array("pear.pepr", "pear.user", "pear.dev", "pear.admin", "pear.group" );
            break;

        case "pear.pepr.admin" :
            $levels = array("pear.admin", "pear.group", "pear.pepr.admin");
            break;

        case "pear.user" :
            $levels = array("pear.user", "pear.pepr", "pear.dev", "pear.admin", "pear.group");
            break;

        case "pear.dev" :
            $levels = array("pear.dev", "pear.admin", "pear.group");
            break;

        case "pear.admin" :
            $levels = array("pear.admin", "pear.group");
            break;

        case "pear.group" :
            $levels = array("pear.group");
            break;

        case "global.karma.manager" :
            $levels = array("pear.group");
            break;

        case "doc.chm-upload" :
            $levels = array("pear.doc.chm-upload", "pear.group");
            break;

        default :
            $levels = array($level);
            break;

        }

        $query = "SELECT * FROM karma WHERE user = ? AND level IN (!)";

        $sth = $this->_dbh->query($query, array($user, "'" . implode("','", $levels) . "'"));
        return ($sth->numRows() > 0);
    }
}
