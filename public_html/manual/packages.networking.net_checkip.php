<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.networking.php', 'Networking'),
  'next' => array('packages.networking.net_portscan.php', 'Net_Portscan'),
  'up'   => array('packages.networking.php', 'Networking'),
  'toc'  => array(
    array('packages.networking.php#packages.Networking', ''),
    array('packages.networking.php#AEN484', ''),
    array('packages.networking.net_checkip.php', 'Net_CheckIP'),
    array('packages.networking.net_portscan.php', 'Net_Portscan'))));
manualHeader('Net_CheckIP','packages.networking.net_checkip.php');
?><H1
><A
NAME="packages.Networking.Net_CheckIP"
><B
CLASS="classname"
>Net_CheckIP</B
></A
></H1
><DIV
CLASS="refnamediv"
><A
NAME="AEN487"
></A
><B
CLASS="classname"
>Net_CheckIP</B
>&nbsp;--&nbsp;
    Validation of IPv4 adresses
   </DIV
><DIV
CLASS="refsect1"
><A
NAME="packages.Networking.Net_CheckIP.check_ip"
></A
><H2
>Net_Portscan::checkPort</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN493"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>boolean <B
CLASS="function"
>Net_CheckIP::check_ip</B
></CODE
> (string ip)</CODE
></P
><P
></P
></DIV
><P
>&#13;    This function can validate if a given string has a valid IPv4 syntax.
   </P
><TABLE
WIDTH="100%"
BORDER="0"
CELLPADDING="0"
CELLSPACING="0"
CLASS="EXAMPLE"
><TR
><TD
><DIV
CLASS="example"
><A
NAME="AEN500"
></A
><P
><B
>Example 1. Using checkPort</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;require_once "Net_CheckIP/CheckIP.php";

if (Net_CheckIP::check_ip("your_ip_goes_here")) {
    // Syntax of the IP is ok
}
    </PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
></DIV
><?php manualFooter('Net_CheckIP','packages.networking.net_checkip.php');
?>