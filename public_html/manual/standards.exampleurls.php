<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.cvs.php', 'Using CVS'),
  'next' => array('standards.naming.php', 'Naming Conventions'),
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
manualHeader('Example URLs','standards.exampleurls.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.exampleurls"
>Example URLs</A
></H1
><P
>&#13;    Use "example.com" for all example URLs, per RFC 2606.
   </P
></DIV
><?php manualFooter('Example URLs','standards.exampleurls.php');
?>