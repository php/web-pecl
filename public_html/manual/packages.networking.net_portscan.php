<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.networking.net_checkip.php', 'Net_CheckIP'),
  'next' => array('contributing.php', 'Contributing to PEAR'),
  'up'   => array('packages.networking.php', 'Networking'),
  'toc'  => array(
    array('packages.networking.php#packages.Networking', ''),
    array('packages.networking.php#AEN484', ''),
    array('packages.networking.net_checkip.php', 'Net_CheckIP'),
    array('packages.networking.net_portscan.php', 'Net_Portscan'))));
manualHeader('Net_Portscan','packages.networking.net_portscan.php');
?><H1
><A
NAME="packages.Networking.Net_Portscan"
><B
CLASS="classname"
>Net_Portscan</B
></A
></H1
><DIV
CLASS="refnamediv"
><A
NAME="AEN504"
></A
><B
CLASS="classname"
>Net_Portscan</B
>&nbsp;--&nbsp;
    Portscanner utilities.
   </DIV
><DIV
CLASS="refsect1"
><A
NAME="packages.Networking.Net_Portscan.checkPort"
></A
><H2
>Net_Portscan::checkPort</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN510"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>boolean <B
CLASS="function"
>Net_Portscan::checkPort</B
></CODE
> (string host, integer port [, integer timeout])</CODE
></P
><P
></P
></DIV
><P
>&#13;    This function checks if there is a service available at the
    specified port on the specified machine. It there is a service,
    the function returns true. Otherwise it returns false.
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
NAME="AEN519"
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
>&#13;require_once "Net_Portscan/Portscan.php";

if (Net_Portscan::checkPort("localhost", 80) == NET_PORTSCAN_SERVICE_FOUND) {
    echo "There is a service on localhost on port 80 (" . Net_Portscan::getService(80) . ").\n";
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
><DIV
CLASS="refsect1"
><A
NAME="packages.Networking.Net_Portscan.checkPortRange"
></A
><H2
>Net_Portscan::checkPortRange</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN524"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>array <B
CLASS="function"
>Net_Portscan::checkPortRange</B
></CODE
> (string host, integer minPort, integer maxPort [, integer timeout])</CODE
></P
><P
></P
></DIV
><P
>&#13;    This function is pretty identical to checkPort, except it takes
    a range of ports, that are scanned.
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
NAME="AEN534"
></A
><P
><B
>Example 2. Using checkPortRange</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;require_once "Net_Portscan/Portscan.php";

echo "Scanning localhost ports 70-90\n";
$result = Net_Portscan::checkPortRange("localhost", 70, 90);

foreach ($result as $port =&#62; $element) {
    if ($element == NET_PORTSCAN_SERVICE_FOUND) {
        echo "On port " . $port . " there is running a service.\n";
    } else {
        echo "On port " . $port . " there is no service running.\n";
    }
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
><DIV
CLASS="refsect1"
><A
NAME="packages.Networking.Net_Portscan.getService"
></A
><H2
>Net_Portscan::getService</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN539"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>string <B
CLASS="function"
>Net_Portscan::checkPortRange</B
></CODE
> (integer port, string protocol)</CODE
></P
><P
></P
></DIV
><P
>&#13;    This function returns the service associated with port for the
    specified protocol as per /etc/services. protocol is either
    tcp or udp. (Default is tcp.)
   </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="packages.Networking.Net_Portscan.getPort"
></A
><H2
>Net_Portscan::getPort</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN548"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>integer <B
CLASS="function"
>Net_Portscan::getPort</B
></CODE
> (string service, string protocol)</CODE
></P
><P
></P
></DIV
><P
>&#13;    This function returns the port which corresponds to service for the
    specified protocol as per /etc/services. protocol is either tcp or
    udp. (Default is tcp.)
   </P
></DIV
><?php manualFooter('Net_Portscan','packages.networking.net_portscan.php');
?>