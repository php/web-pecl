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

require_once "pear-database.php";
require_once "Damblan/RSS/Common.php";

/**
 * Generates a RSS feed for the latest releases for a given package
 *
 * @author Martin Jansen <mj@php.net>
 * @package Damblan
 * @category RSS
 * @version $Revision$
 */
class Damblan_RSS_Package extends Damblan_RSS_Common {

    function Damblan_RSS_Package($value) {
        parent::Damblan_RSS_Common();

        $this->setTitle("Latest releases");
        $this->setDescription("The latest releases for the package " . $value);

        $items = package::getRecent(10, $value);
        $this->__addItems($items);
    }
}