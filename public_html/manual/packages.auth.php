<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.php', 'PEAR Packages'),
  'next' => array('packages.net_checkip.php', ''),
  'up'   => array('packages.php', 'PEAR Packages'),
  'toc'  => array(
    array('packages.php#packages', ''),
    array('packages.php#AEN0', ''),
    array('packages.auth.php', ''),
    array('packages.php#AEN0', ''),
    array('packages.net_checkip.php', ''))));
manualHeader('','packages.auth.php');
?><DIV
CLASS="ARTICLE"
><DIV
CLASS="TITLEPAGE"
><H1
CLASS="title"
><A
NAME="AEN416"
>Auth: creating authentication realms</A
></H1
><HR></DIV
><DIV
CLASS="section"
><H1
CLASS="section"
><A
NAME="packages.auth.auth"
>Auth</A
></H1
><P
>The PEAR Auth package helps you to create PHP based
  authentication systems.</P
><P
>The biggest advantage of PEAR Auth is the fact, that it
  uses storage containers to read/write the login data. This
  containers can be easily docked into the auth system and can
  theoretically access to databases, files, LDAP servers etc.
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
>&#13;require_once "Auth/Auth.php";

$auth = new Auth("DB", "mysql://martin:test@localhost/test");
    
$auth-&#62;start();

if ($auth-&#62;getAuth()) {
    echo "You are successfully logged in. Welcome to the system.\n";
    echo "This text is only visible for you because you are logged in.\n";
}
  </PRE
></TD
></TR
></TABLE
><P
>&#13;   The function <B
CLASS="function"
>Auth()</B
> is the constructor of the
   Auth class. You can pass two parameters towards it: The first
   parameter <TT
CLASS="parameter"
><I
>$storageDriver</I
></TT
> represents the
   type of storage container, the class shall use. In our example,
   the container is a database. The second parameter <TT
CLASS="parameter"
><I
>&#13;   $storageOptions</I
></TT
> defines some parameters that are passed
   to the storage container. When using the PEAR DB layer, this
   2nd parameter has to be the DSN string that tells the DB layer how
   to connect.
  </P
><P
>&#13;   The function <B
CLASS="function"
>start()</B
> checks, if the user is
   already logged in. If this check leads into a positive result,
   the function does nothing more. If <B
CLASS="function"
>start()</B
>
   detects, that the user isn't already logged in, it starts the
   authentication mechanism: An HTML form is printed and the user
   can enter his login data.
  </P
><P
>&#13;   <B
CLASS="function"
>getAuth()</B
> is a helper function that returns
   true if the user is logged and that returns false if he hasn't
   already logged in.
  </P
></DIV
></DIV
><?php manualFooter('','packages.auth.php');
?>