<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.php', 'PEAR Packages'),
  'next' => array('packages.auth.auth.php', 'Auth'),
  'up'   => array('packages.php', 'PEAR Packages'),
  'toc'  => array(
    array('packages.php#packages', ''),
    array('packages.auth.php', 'Auth'),
    array('packages.php#AEN0', ''),
    array('packages.net_checkip.php', ''))));
manualHeader('Auth','packages.auth.php');
?><DIV
CLASS="reference"
><A
NAME="packages.Auth"
></A
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
>II. Auth</H1
><DIV
CLASS="PARTINTRO"
><A
NAME="AEN417"
></A
><P
>&#13;   The PEAR::Auth package helps you to create PHP based
   authentication systems.
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
HREF="packages.auth.auth.php"
><B
CLASS="classname"
>Auth</B
></A
> &#8212; 
    PHP based authentication systems.
   </DT
></DL
></DIV
></DIV
></DIV
><?php manualFooter('Auth','packages.auth.php');
?>