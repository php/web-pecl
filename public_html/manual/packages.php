<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('class.pear-error.php', 'PEAR_Error'),
  'next' => array('packages.skeleton.php', ''),
  'up'   => array('index.php', 'PEAR Manual'),
  'toc'  => array(
    array('index.php#manual', ''),
    array('index.php#AEN0', ''),
    array('index.php#bookinfo', ''),
    array('index.php#AEN0', ''),
    array('preface.php', 'Preface'),
    array('getting-started.php', 'Getting Started'),
    array('packages.php', 'PEAR Packages'))));
manualHeader('PEAR Packages','packages.php');
?><DIV
CLASS="PART"
><A
NAME="packages"
></A
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
>II. PEAR Packages</H1
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
><DT
><A
HREF="packages.auth.php"
>Auth: creating authentication realms</A
></DT
><DT
><A
HREF="packages.net_checkip.php"
>Net_CheckIP: Validation of IPv4 adresses</A
></DT
></DL
></DIV
></DIV
></DIV
><?php manualFooter('PEAR Packages','packages.php');
?>