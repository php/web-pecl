<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('preface.php', 'Preface'),
  'next' => array('introduction.php', 'Introduction'),
  'up'   => array('index.php', 'PEAR Manual'),
  'toc'  => array(
    array('index.php#manual', ''),
    array('index.php#AEN0', ''),
    array('index.php#bookinfo', ''),
    array('index.php#AEN0', ''),
    array('preface.php', 'Preface'),
    array('getting-started.php', 'Getting Started'),
    array('packages.php', 'PEAR Packages'),
    array('contributing.php', 'Contributing to PEAR'))));
manualHeader('Getting Started','getting-started.php');
?><DIV
CLASS="PART"
><A
NAME="getting-started"
></A
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
>I. Getting Started</H1
><DIV
CLASS="TOC"
><DL
><DT
><B
>Table of Contents</B
></DT
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
></DIV
></DIV
></DIV
><?php manualFooter('Getting Started','getting-started.php');
?>