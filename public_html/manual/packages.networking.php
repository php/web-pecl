<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.auth.auth_http.php', 'Auth_HTTP'),
  'next' => array('packages.networking.net_checkip.php', 'Net_CheckIP'),
  'up'   => array('packages.php', 'PEAR Packages'),
  'toc'  => array(
    array('packages.php#packages', ''),
    array('packages.auth.php', 'Authentication'),
    array('packages.networking.php', 'Networking'))));
manualHeader('Networking','packages.networking.php');
?><DIV
CLASS="reference"
><A
NAME="packages.Networking"
></A
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
>III. Networking</H1
><DIV
CLASS="PARTINTRO"
><A
NAME="AEN484"
></A
><P
>&#13;    Networking utilities.
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
HREF="packages.networking.net_checkip.php"
><B
CLASS="classname"
>Net_CheckIP</B
></A
> &#8212; 
    Validation of IPv4 adresses
   </DT
><DT
><A
HREF="packages.networking.net_portscan.php"
><B
CLASS="classname"
>Net_Portscan</B
></A
> &#8212; 
    Portscanner utilities.
   </DT
></DL
></DIV
></DIV
></DIV
><?php manualFooter('Networking','packages.networking.php');
?>