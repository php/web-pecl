<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.auth.auth.php', 'Auth'),
  'next' => array('packages.networking.php', 'Networking'),
  'up'   => array('packages.auth.php', 'Authentication'),
  'toc'  => array(
    array('packages.auth.php#packages.Auth', ''),
    array('packages.auth.php#AEN417', ''),
    array('packages.auth.auth.php', 'Auth'),
    array('packages.auth.auth_http.php', 'Auth_HTTP'))));
manualHeader('Auth_HTTP','packages.auth.auth_http.php');
?><H1
><A
NAME="packages.Auth.Auth_HTTP"
><B
CLASS="classname"
>Auth_HTTP</B
></A
></H1
><DIV
CLASS="refnamediv"
><A
NAME="AEN464"
></A
><B
CLASS="classname"
>Auth_HTTP</B
>&nbsp;--&nbsp;
    HTTP authentication with PHP.
   </DIV
><DIV
CLASS="refsect1"
><A
NAME="packages.Auth.Auth_HTTP.Auth_HTTP"
></A
><H2
>Auth_HTTP::Auth_HTTP</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN470"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
><B
CLASS="function"
>Auth::Auth</B
></CODE
> (string storageDriver, mixed options)</CODE
></P
><P
></P
></DIV
><P
>&#13;    Constructor for the authencation system. The first parameter is the
    name of the storage driver, that should be used. The second parameter
    can either be a string containing some login information or an
    array containing a bunch of options for the storage driver.
   </P
><P
>&#13;    For more information on usage of the constructor parameters, please
    refer to the documentation of PEAR::Auth.
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
NAME="AEN479"
></A
><P
><B
>Example 1. Simple usage example</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;require_once "Auth_HTTP/Auth_HTTP.php";

$a = new Auth_HTTP("DB", "mysql://test:test@localhost/test");

$a-&#62;start();
    </PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
></DIV
><?php manualFooter('Auth_HTTP','packages.auth.auth_http.php');
?>