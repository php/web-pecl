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

require_once "XML/Tree.php";

/**
 * Base class for generating RSS feeds
 *
 * @author Martin Jansen <mj@php.net>
 * @category RSS
 * @package Damblan
 * @version $Revision$
 */
class Damblan_RSS_Common {

    var $_tree = null;
    var $_root = null;
    var $_channel = null;

    function Damblan_RSS_Common() {
        $this->_tree = new XML_Tree;
        $this->_root = &$this->_tree->addRoot("rss");

        // For now we use RSS 0.91, but this might change one fine day
        $this->_root->setAttribute("version", "0.91");

        $this->_channel = &$this->_root->addChild("channel");
        $this->_channel->addChild("dc:creator", "pear-webmaster@php.net");
        $this->_channel->addChild("language", "en-us");
    }

    /**
     * Set the title of the RSS feed
     *
     * @param  string Title
     * @return void
     */
    function setTitle($title) {
        $this->_channel->addChild("title", $title);
    }

    /**
     * Set the description of the RSS feed
     *
     * @access public
     * @param  string Description
     * @return void
     */
    function setDescription($desc) {
        $this->_channel->addChild("description", $desc);
    }

    /**
     * Create a new item
     *
     * @access public
     * @param  string Content for the title tag
     * @param  string Content for the link tag
     * @param  string Content for the description tag
     * @return object XML_Tree_Node instance
     */
    function newItem($title, $link, $desc) {
        $item = new XML_Tree_Node("item");

        $item->addChild("title", htmlspecialchars($title));
        $item->addChild("link", htmlspecialchars($link));
        $item->addChild("description", htmlspecialchars($desc));

        return $item;
    }

    /**
     * Add new item to the RSS file
     *
     * @access public
     * @param  object XML_Tree_Node instance
     * @return void
     */
    function addItem(&$item) {
        $this->_channel->addChild($item);
    }

    /**
     * Return string representation of the RSS feed
     *
     * @access public
     * @return void
     */
    function toString() {
        return $this->_root->get();
    }

    function getRecent($type, $value, $n = 10) {
        switch ($type) {
        case "latest" :
            require_once "pear-database.php";
            return release::getRecent($n);
            break;

        case "category" :
            require_once "pear-database.php";
            return category::getRecent($n, $value);
            break;

        case "package" :
            require_once "pear-database.php";
            return package::getRecent($n, $value);
            break;
        }

        return PEAR::raiseError("The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.");
    }
}
?>