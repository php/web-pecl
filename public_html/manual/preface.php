<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('index.php', 'PEAR Manual'),
  'next' => array('getting-started.php', 'Getting Started'),
  'up'   => array('index.php', 'PEAR Manual'),
  'toc'  => array(
    array('index.php#manual', ''),
    array('index.php#AEN0', ''),
    array('index.php#bookinfo', ''),
    array('index.php#AEN0', ''),
    array('preface.php', 'Preface'),
    array('getting-started.php', 'Getting Started'),
    array('packages.php', 'PEAR Packages'))));
manualHeader('Preface','preface.php');
?><DIV
CLASS="preface"
><H1
><A
NAME="preface"
>Preface</A
></H1
><DIV
CLASS="TOC"
><DL
><DT
><B
>Table of Contents</B
></DT
><DT
><A
HREF="preface.php#about"
>About this Manual</A
></DT
></DL
></DIV
><BLOCKQUOTE
CLASS="ABSTRACT"
><DIV
CLASS="abstract"
><A
NAME="AEN21"
></A
><P
></P
><P
>&#13;    <SPAN
CLASS="acronym"
>PEAR</SPAN
> is the PHP Extension and Application
    Repository.
   </P
><P
></P
></DIV
></BLOCKQUOTE
><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="about"
>About this Manual</A
></H1
><P
>&#13;    This manual is written in <SPAN
CLASS="acronym"
>XML</SPAN
> using the <A
HREF="http://www.nwalsh.com/docbook/xml/"
TARGET="_top"
>DocBook XML DTD</A
>, using <A
HREF="http://www.jclark.com/dsssl/"
TARGET="_top"
><SPAN
CLASS="acronym"
>DSSSL</SPAN
></A
> (Document
    Style and Semantics Specification Language) for formatting.  The
    tools used for formatting <SPAN
CLASS="acronym"
>HTML</SPAN
> versions are
    <A
HREF="http://www.jclark.com/jade/"
TARGET="_top"
>Jade</A
>, written by <A
HREF="http://www.jclark.com/bio.htm"
TARGET="_top"
>James Clark</A
> and <A
HREF="http://nwalsh.com/docbook/dsssl/"
TARGET="_top"
>The Modular DocBook Stylesheets</A
>
    written by <A
HREF="http://nwalsh.com/"
TARGET="_top"
>Norman Walsh</A
>.
   </P
><P
>&#13;    It is based on the great work, the PHP documentation group has
    done in the past.
   </P
></DIV
></DIV
><?php manualFooter('Preface','preface.php');
?>