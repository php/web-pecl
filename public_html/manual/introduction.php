<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('getting-started.php', 'Getting Started'),
  'next' => array('standards.php', 'Coding Standards'),
  'up'   => array('getting-started.php', 'Getting Started'),
  'toc'  => array(
    array('getting-started.php#getting-started', ''),
    array('getting-started.php#AEN0', ''),
    array('introduction.php', 'Introduction'),
    array('getting-started.php#AEN0', ''),
    array('standards.php', 'Coding Standards'),
    array('getting-started.php#AEN0', ''),
    array('reference.php', 'PEAR'))));
manualHeader('Introduction','introduction.php');
?><DIV
CLASS="chapter"
><H1
><A
NAME="introduction"
>Chapter 1. Introduction</A
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
HREF="introduction.php#pear-whatis"
>What is PEAR?</A
></DT
></DL
></DIV
><P
>&#13;   PEAR is dedicated to Malin Bakken,
   born 1999-11-21 (the first PEAR code was written just two hours
   before she was born).
  </P
><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="pear-whatis"
>What is PEAR?</A
></H1
><P
>&#13;    PEAR is a code repository for PHP extensions and PHP library code
    inspired by TeX's CTAN and Perl's CPAN.
   </P
><P
>&#13;    The purpose of PEAR is:
    <P
></P
><UL
><LI
><P
>&#13;       to provide a consistent means for library code authors to share
       their code with other developers
      </P
></LI
><LI
><P
>&#13;       to give the PHP community an infrastructure for sharing code
      </P
></LI
><LI
><P
>&#13;       to define standards that help developers write portable and
       reusable code
      </P
></LI
><LI
><P
>&#13;       to provide tools for code maintenance and distribution
      </P
></LI
></UL
>
   </P
></DIV
></DIV
><?php manualFooter('Introduction','introduction.php');
?>