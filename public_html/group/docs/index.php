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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("The PEAR Group: Administrative Documents");
?>

<h1>PEAR Group - Administrative Documents</h1>

<ul>
  <li>04th September 2003: <?php echo make_link("20030904-pph.php", "Handling Package Proposals"); ?></li>
  <li>20th August 2003: <?php echo make_link("20030820-vm.php", "Handling Votings and Membership"); ?></li>
</ul>

<?php
response_footer();
?>

