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
response_header("The PEAR Group");
?>

<h1>The PEAR Group</h1>

<p>The PEAR Group is the governing body of PEAR. It currently consists
of the following individuals (in no particular order):</p>

<ul>
  <li>Lukas Smith</li>
  <li>Pierre-Alain Joye</li>
  <li>Martin Jansen</li>
  <li>Jesus M. Castagnetto</li>
  <li>Alan Knowles</li>
  <li>Tomas V.V.Cox</li>
  <li>Jon Parise</li>
  <li>Stig S. Bakken</li>
</ul>

<p>The Group was 
<?php echo make_link("http://marc.theaimsgroup.com/?l=pear-dev&m=106073080219083&w=2", "first announced"); ?> 
on 12th August 2003 by Stig S. Bakken. If you would like to get in
contact with the members, you can write to 
<?php echo make_mailto_link("pear-group@php.net"); ?>.

<h2>Administrative Documents</h2>

<ul>
  <li>04th September 2003: <?php echo make_link("docs/20030904-pph.php", "Handling Package Proposals"); ?></li>
  <li>20th August 2003: <?php echo make_link("docs/20030820-vm.php", "Handling Votings and Membership"); ?></li>
</ul>

<?php
response_footer();
?>

