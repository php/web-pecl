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

require_once "Log.php";
require_once "Log/observer.php";

require_once "Mail.php";

/**
 * Observer class for logging via email
 *
 * @author Martin Jansen <mj@php.net>
 * @extends Log_observer
 * @version $Revision$
 * @package Damblan
 */
class Damblan_Log_Mail extends Log_observer {

    var $_mailer = null;

    var $_headers = array();
    var $_recipients = "";

    function Damblan_Log_Mail() {
        $this->Log_observer();

        $this->_mailer =& Mail::factory("mail", "-f pear-sys@php.net");

        $this->_headers['From'] = "\"PEAR System Administrators\" <pear-sys@php.net>";
    }
    
    /**
     * Generate logging email
     *
     * @param array Array containing the log information
     * @return void
     */
    function notify($event) {
        if (DEVBOX) {
            return;
        }

        $ok = $this->_mailer->send($this->_recipients, $this->_headers, $event['message']);

        if ($ok === false) {
            trigger_error("Email notification routine failed.", 
                          E_USER_WARNING);
        }
    }

    /**
     * Logging method
     *
     * @access public
     * @param  string Log message
     * @return boolean
     */
    function log($text) {
        $event['message'] = $text;
        return $this->notify($event);
    }

    /**
     * Set mail recipients
     *
     * @access public
     * @param  string Recipients
     * @return void
     */
    function setRecipients($r) {
        $this->_recipients = $r;
    }

    /**
     * Set header line
     *
     * @access public
     * @param  string Name of the header (e.g. "From")
     * @param  string Value of the header
     * @return void
     */
    function setHeader($name, $value) {
        $this->_headers[$name] = $value;
    }
}
?>
