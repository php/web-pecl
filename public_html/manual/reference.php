<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.naming.php', 'Naming Conventions'),
  'next' => array('class.pear.php', 'PEAR'),
  'up'   => array('getting-started.php', 'Getting Started'),
  'toc'  => array(
    array('getting-started.php#getting-started', ''),
    array('getting-started.php#AEN0', ''),
    array('introduction.php', 'Introduction'),
    array('getting-started.php#AEN0', ''),
    array('standards.php', 'Coding Standards'),
    array('getting-started.php#AEN0', ''),
    array('reference.php', 'PEAR'))));
manualHeader('PEAR Reference Manual','reference.php');
?><DIV
CLASS="reference"
><A
NAME="reference"
></A
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
>I. PEAR Reference Manual</H1
><DIV
CLASS="PARTINTRO"
><A
NAME="AEN201"
></A
><P
>&#13;    This chapter contains reference documentation for PEAR components
    that are distributed with PHP.  It is assumed that you are
    already familiar with objects and
    classes.
   </P
></DIV
><DIV
CLASS="TOC"
><DL
><DT
><B
>Table of Contents</B
></DT
><DT
><A
HREF="class.pear.php"
>PEAR</A
> &#8212; PEAR base class</DT
><DT
><A
HREF="class.pear-error.php"
>PEAR_Error</A
> &#8212; PEAR error mechanism base class</DT
></DL
></DIV
></DIV
></DIV
><?php manualFooter('PEAR Reference Manual','reference.php');
?>