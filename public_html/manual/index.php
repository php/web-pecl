<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('index.php#AEN0', ''),
  'next' => array('preface.php', 'Preface'),
  'up'   => array('index.php#AEN0', ''),
  'toc'  => array(
    array('index.php#AEN0', ''))));
manualHeader('PEAR Manual','index.php');
?><DIV
CLASS="BOOK"
><A
NAME="manual"
></A
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
><A
NAME="manual"
>PEAR Manual</A
></H1
><DIV
CLASS="author"
>Martin Jansen</DIV
><H4
CLASS="EDITEDBY"
>Edited by</H4
><H3
CLASS="editor"
>Martin Jansen</H3
><P
CLASS="copyright"
><A
HREF="copyright.php"
>Copyright</A
> &copy; 2001 by the PHP PEAR Group</P
><HR></DIV
><DIV
CLASS="TOC"
><DL
><DT
><B
>Table of Contents</B
></DT
><DT
><A
HREF="preface.php"
>Preface</A
></DT
><DD
><DL
><DT
><A
HREF="preface.php#about"
>About this Manual</A
></DT
></DL
></DD
><DT
>I. <A
HREF="getting-started.php"
>Getting Started</A
></DT
><DD
><DL
><DT
>1. <A
HREF="introduction.php"
>Introduction</A
></DT
><DT
>2. <A
HREF="standards.php"
>Coding Standards</A
></DT
><DT
>I. <A
HREF="reference.php"
>PEAR Reference Manual</A
></DT
></DL
></DD
><DT
>II. <A
HREF="packages.php"
>PEAR Packages</A
></DT
><DD
><DL
><DT
>II. <A
HREF="packages.auth.php"
>Auth</A
></DT
><DT
><A
HREF="packages.net_checkip.php"
>Net_CheckIP: Validation of IPv4 adresses</A
></DT
><DT
>III. <A
HREF="packages.net_portscan.php"
>Net_Portscan</A
></DT
></DL
></DD
><DT
>III. <A
HREF="contributing.php"
>Contributing to PEAR</A
></DT
><DD
><DL
><DT
>3. <A
HREF="contributing-howto.php"
>How to contribute to PEAR</A
></DT
><DT
><A
HREF="packages.skeleton.php"
>Skeleton for creating PEAR docbook documentation</A
></DT
></DL
></DD
></DL
></DIV
></DIV
><?php manualFooter('PEAR Manual','index.php');
?>