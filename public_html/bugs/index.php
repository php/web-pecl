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
   |         Tomas V.V.Cox <cox@idecnet.com>                              |
   +----------------------------------------------------------------------+
   $Id$
*/
response_header("Bugs");
?>

<h1>PEAR Bug Tracking System</h1>
<p>The following options are avaible:</p>
<ul>
  <li><?php print_link('/bugs/search.php', 'Search for <b>existing bugs</b>'); ?></li>
  <li>Report a new bug for the:
      <?php print make_bug_link('Web Site', 'report', '<b>Web Site</b>');?>,
      <?php print make_bug_link('Documentation', 'report', '<b>Documentation</b>');?> or
      <?php print make_bug_link('Bug System', 'report', '<b>Bug System</b>');?>
  </li>
  <li>If you want to report a bug for a <b>specific package</b>, please go to the
  package home using the <?php print_link('/packages.php', 'Browse packages');?> tool
  or the package <?php print_link('/package-search.php', 'Search System'); ?>.
  </li>
</ul>
<p>If you need support or you don't really know if it is a bug or not, please
use our <? print_link('/support.php', 'support channels');?>.</p>

<p>Before submitting a bug, please make sure nobody has already reported it.
Read our tips on how to <?php print_link('http://bugs.php.net/how-to-report.php', 'report a bug that someone will want to help fix', 'top');?>.
</p>
<?php
response_footer();
?>
