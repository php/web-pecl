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
*/

/**
 * Class to manage the PEAR Karma System
 *
 * This system makes it not only possible to provide a fully developed
 * permission system, but it also allows us to set up a php.net-wide
 * single-sign-on system some time in the future.
 *
 * @author  Martin Jansen <mj@php.net>
 * @version $Revision$
 */
class Damblan_Karma {

    var $_dbh;

    /**
     * Constructor
     *
     * @access public
     * @param  object Instance of PEAR::DB
     */
    function Damblan_Karma(&$dbh) {
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

    /**
     * Grant karma for $level to the given $user
     *
     * @access public
     * @param  string Handle of the user
     * @param  string Level
     * @return boolean
     */
    function grant($user, $level) {
        global $auth_user;

        $this->_requireKarma();

        // Abort if the karma level has already been granted to the user
        if ($this->has($user, $level)) {
            PEAR::raiseError("The karma level $level has already been "
                             . "granted to the user $user.");
            return false;
        }

        $id = $this->_dbh->nextId("karma");

        $query = "INSERT INTO karma VALUES (?, ?, ?, ?, NOW())";
        $sth = $this->_dbh->query($query, array($id, $user, $level, $auth_user->handle));

        if (!DB::isError($sth)) {
            $this->_notify($auth_user->handle, $user, "Added level \"" . $level . "\"");
            return true;
        }

        return false;
    }

    /**
     * Remove karma $level for the given $user
     *
     * @access public
     * @param  string Handle of the user
     * @param  string Level
     * @return boolean
     */
    function remove($user, $level) {
        global $auth_user;

        $this->_requireKarma();

        $query = "DELETE FROM karma WHERE user = ? AND level = ?";
        $sth = $this->_dbh->query($query, array($user, $level));

        if (!DB::isError($sth)) {
            $this->_notify($auth_user->handle, $user, "Removed level \"" . $level . "\"");
            return true;
        }

        return false;
    }

    /**
     * Get karma for given user
     *
     * @access public
     * @param  string Name of the user
     * @return array
     */
    function get($user) {
        $query = "SELECT * FROM karma WHERE user = ?";
        return $this->_dbh->getAll($query, array($user), DB_FETCHMODE_ASSOC);
    }

    /**
     * Get all users with given karma level
     *
     * @access public
     * @param  string Level
     * @return array
     */
    function getUsers($level) {
        $query = "SELECT * FROM karma WHERE level = ?";
        return $this->_dbh->getAll($query, array($level), DB_FETCHMODE_ASSOC);
    }

    /**
     * Get all available karma levels
     *
     * @access public
     * @return array Nested array containing the name of each leven and
     *               the number of occurrences of this level.
     */
    function getLevels() {
        $query = "SELECT level, COUNT(level) AS sum FROM karma GROUP BY level";
        return $this->_dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    /**
     * Require global.karma.manager level for write operations
     *
     * @access private
     * @return boolean False on error, true otherwise
     */
    function _requireKarma() {
        global $auth_user;

        if ($this->has($auth_user->handle, "global.karma.manager") == false) {
            PEAR::raiseError("Insufficient privileges");
            return false;
        }
        return true;
    }

    /**
     * Notification method
     *
     * Sends out an email to the administrative body when karma has
     * been updated.
     *
     * @access private
     * @param  string Handle of the administrator who granted karma
     * @param  string Handle of the user whose karma has been updated
     * @param  string Describes the type of karma update
     * @return void
     */
    function _notify($admin_user, $user, $action) {
        require_once "Damblan/Log.php";
        require_once "Damblan/Log/Mail.php";

        static $logger, $observer;

        if (!isset($logger)) {
            $logger = new Damblan_Log;
        }
        if (!DEVBOX && !isset($observer)) {
            $observer = new Damblan_Log_Mail;
            $observer->setRecipients("pear-group@php.net");
            $observer->setHeader("From", "\"PEAR Karma Manager\" <pear-sys@php.net>");
            $observer->setHeader("Reply-To", "<pear-group@php.net>");
            $observer->setHeader("Subject", "[PEAR Group] Karma update");
            $logger->attach($observer);
        }

        $text = $admin_user . " has updated karma for " . $user . ": " . $action;
        $logger->log($text);
    }
}
