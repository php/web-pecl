<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.including.php', 'Including Code'),
  'next' => array('standards.header.php', 'Header Comment Blocks'),
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
manualHeader('PHP Code Tags','standards.tags.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.tags"
>PHP Code Tags</A
></H1
><P
>&#13;    <I
CLASS="emphasis"
>Always</I
> use <TT
CLASS="literal"
>&#60;?php ?&#62;</TT
> to
    delimit PHP code, not the <TT
CLASS="literal"
>&#60;? ?&#62;</TT
> shorthand.
    This is required for PEAR compliance and is also the most portable
    way to include PHP code on differing operating systems and setups.
   </P
></DIV
><?php manualFooter('PHP Code Tags','standards.tags.php');
?>