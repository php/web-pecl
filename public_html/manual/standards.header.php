<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.tags.php', 'PHP Code Tags'),
  'next' => array('standards.cvs.php', 'Using CVS'),
  'up'   => array('standards.php', 'Coding Standards'),
  'toc'  => array(
    array('standards.php#standards', ''),
    array('standards.php#AEN57', ''),
    array('standards.php#standards.indenting', 'Indenting'),
    array('standards.control.php', 'Control Structures'),
    array('standards.funcalls.php', 'Function Calls'),
    array('standards.funcdef.php', 'Function Definitions'),
    array('standards.comments.php', 'Comments'),
    array('standards.including.php', 'Including Code'),
    array('standards.tags.php', 'PHP Code Tags'),
    array('standards.header.php', 'Header Comment Blocks'),
    array('standards.cvs.php', 'Using CVS'),
    array('standards.exampleurls.php', 'Example URLs'),
    array('standards.naming.php', 'Naming Conventions'))));
manualHeader('Header Comment Blocks','standards.header.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.header"
>Header Comment Blocks</A
></H1
><P
>&#13;    All source code files in the core PEAR distribution should contain
    the following comment block as the header:
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997, 1998, 1999, 2000, 2001 The PHP Group             |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Original Author &#60;author@example.com&#62;                        |
// |          Your Name &#60;you@example.com&#62;                                 |
// +----------------------------------------------------------------------+
//
// $Id$
</PRE
></TD
></TR
></TABLE
>
   </P
><P
>   
    There's no hard rule to determine when a new code contributer
    should be added to the list of authors for a given source file.
    In general, their changes should fall into the "substantial"
    category (meaning somewhere around 10% to 20% of code changes).
    Exceptions could be made for rewriting functions or contributing
    new logic.
   </P
><P
>&#13;    Simple code reorganization or bug fixes would not justify the
    addition of a new individual to the list of authors.
   </P
><P
>&#13;    Files not in the core PEAR repository should have a similar block
    stating the copyright, the license, and the authors. All files
    should include the modeline comments to encourage consistency.
   </P
></DIV
><?php manualFooter('Header Comment Blocks','standards.header.php');
?>