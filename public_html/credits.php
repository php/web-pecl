<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header('Credits');
?>

<h2>Credits</h2>

<h3>PEAR website team</h3>

<ul>
  <li><?php echo user_link("ssb"); ?></li>
  <li><?php echo user_link("cox"); ?></li>
  <li><?php echo user_link("mj"); ?></li>
  <li><?php echo user_link("cmv"); ?></li>
  <li><?php echo user_link("richard");?></li>
</ul>

<h3>PEAR documentation team</h3>

<ul>
  <li><?php echo user_link("cox"); ?></li>
  <li><?php echo user_link("mj"); ?></li>
  <li><?php echo user_link("alexmerz"); ?></li>
</ul>

<small>(All in alphabetic order)</small>

<?php
response_footer();
?>
