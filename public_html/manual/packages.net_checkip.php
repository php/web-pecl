<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.auth.php', ''),
  'next' => array('contributing.php', 'Contributing to PEAR'),
  'up'   => array('packages.php', 'PEAR Packages'),
  'toc'  => array(
    array('packages.php#packages', ''),
    array('packages.php#AEN0', ''),
    array('packages.auth.php', ''),
    array('packages.php#AEN0', ''),
    array('packages.net_checkip.php', ''))));
manualHeader('','packages.net_checkip.php');
?><DIV
CLASS="ARTICLE"
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
><A
NAME="AEN435"
>Net_CheckIP: Validation of IPv4 adresses</A
></H1
><HR></DIV
><DIV
CLASS="section"
><H1
CLASS="section"
><A
NAME="packages.net_checkip.net_checkip"
>Net_Checkip</A
></H1
><P
>&#13;   This class can validate if a given string has a valid IPv4 syntax.
  </P
><P
>Usage example:</P
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
><P
>&#13;   The function <B
CLASS="function"
>check_ip()</B
> returns true, if
   the passed string has a valid IPv4 syntax. That means that it has
   to contain exactly 4 numbers between 0 and 255, which are all
   separated by a dot.
  </P
></DIV
></DIV
><?php manualFooter('','packages.net_checkip.php');
?>