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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header("Document Type Definitions");
?>

<h2>Document Type Definitions</h2>

<p>The following Document Type Definitions are used in PEAR:</p>

<?php $bb = new BorderBox("Available DTDs"); ?>

<table border="0" cellpadding="2" cellspacing="2">
<tr>
  <td valign="top"><a href="/dtd/package-1.0">package-1.0</a></td>
  <td>This is the <acronym title="Document Type Definition">DTD</acronym>
  that defines the legal building blocks of the <tt>package.xml</tt>
  file that comes with each package. More information about
  <tt>package.xml</tt> can be found 
  <a href="/manual/en/developers.packagedef.php">in the manual</a>.
  <br /><br />A <a href="/dtd/package-1.0.xsd">
  <acronym title="XML Schema Definition">XSD</acronym> file</a> is
  available as well.
  </td>
</tr>
</table>

<?php
$bb->end();
response_footer();
?>