<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.net_checkip.php', ''),
  'next' => array('packages.skeleton.php', ''),
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
manualHeader('Contributing to PEAR','contributing.php');
?><DIV
CLASS="PART"
><A
NAME="contributing"
></A
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
>III. Contributing to PEAR</H1
><DIV
CLASS="TOC"
><DL
><DT
><B
>Table of Contents</B
></DT
><DT
><A
HREF="packages.skeleton.php"
>Skeleton for creating PEAR docbook documentation</A
></DT
></DL
></DIV
></DIV
></DIV
><?php manualFooter('Contributing to PEAR','contributing.php');
?>