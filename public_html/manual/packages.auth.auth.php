<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('packages.auth.php', 'Authentication'),
  'next' => array('packages.auth.auth_http.php', 'Auth_HTTP'),
  'up'   => array('packages.auth.php', 'Authentication'),
  'toc'  => array(
    array('packages.auth.php#packages.Auth', ''),
    array('packages.auth.php#AEN417', ''),
    array('packages.auth.auth.php', 'Auth'),
    array('packages.auth.auth_http.php', 'Auth_HTTP'))));
manualHeader('Auth','packages.auth.auth.php');
?><H1
><A
NAME="packages.Auth.Auth"
><B
CLASS="classname"
>Auth</B
></A
></H1
><DIV
CLASS="refnamediv"
><A
NAME="AEN420"
></A
><B
CLASS="classname"
>Auth</B
>&nbsp;--&nbsp;
    PHP based authentication systems.
   </DIV
><DIV
CLASS="refsect1"
><A
NAME="packages.Auth.Auth.Auth"
></A
><H2
>Auth::Auth</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN426"
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
> (string storageDriver, mixed options, string loginFunction)</CODE
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
>&#13;    The third parameter is the name of user-defined function, that
    prints the login screen.
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
NAME="AEN436"
></A
><P
><B
>Example 1. Using PEAR::Auth with PEAR::DB container and a user-defined
    login function</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;require_once "Auth/Auth.php";

function myOutput($username, $status)
{
    if ($status == AUTH_WRONG_LOGIN) {
        echo "You entered wrong login data!&#60;br / &#62;";
    }
    
    echo "&#60;form method=\"post\" action=\"" . $HTTP_SERVER_VARS['PHP_SELF'] . "\"&#62;\n";
    echo "Username: &#60;input type=\"text\" name=\"username\" value=\"" . $username . "\"&#62;&#60;br /&#62;\n";
    echo "Password: &#60;input type=\"password\" name=\"password\"&#62;&#60;br /&#62;\n";
    echo "&#60;input type="submit"&#62;&#60;/form&#62;\n";
}

$a = new Auth("DB", "mysql://martin:test@localhost/auth", "myOutput");

$a-&#62;start();

if ($a-&#62;getAuth()) {
    echo "You have been authenticated successfully.";
}
    </PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
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
NAME="AEN439"
></A
><P
><B
>Example 2. Using different DB parameters</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;require_once "Auth/Auth.php";

function myOutput($username, $status)
{
    ...  /** See example 1 for the full listing */
}

$params = array(
            "dsn" =&#62; "mysql://martin:test@localhost/auth",
            "table" =&#62; "myAuth",
            "usernamecol" =&#62; "myUserColumn",
            "passwordcol" =&#62; "myPasswordColumn"
            );

$a = new Auth("DB", $params, "myOutput");

$a-&#62;start();

if ($a-&#62;getAuth()) {
    echo "You have been authenticated successfully.";
}
    </PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
><P
>&#13;    This example shows you, how you can specifiy alternative
    names for the database table and the column names. In our
    example, we use the table myAuth, select the username from
    the field myUserColumn and the password from the field
    myPasswordColumn. The Default values for this fields are
    auth, username and password.
   </P
><P
>&#13;    This feature is necessary if you want to use PEAR::Auth
    with a database layout that is different from the one,
    PEAR::Auth uses by default.
   </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="packages.Auth.Auth.start"
></A
><H2
>Auth::start</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN446"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
><B
CLASS="function"
>Auth::start</B
></CODE
> ()</CODE
></P
><P
></P
></DIV
><P
>&#13;    Start the authentication process.
   </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="packages.Auth.Auth.getAuth"
></A
><H2
>Auth::getAuth</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN454"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>boolean <B
CLASS="function"
>Auth::getAuth</B
></CODE
> ()</CODE
></P
><P
></P
></DIV
><P
>&#13;    Check if the user has been authenticated.
   </P
><P
>&#13;    If the user has already been authenticated, the function returns
    true. Otherwise it returns false.
   </P
><P
>&#13;    For usage example, see <B
CLASS="function"
>Auth::Auth()</B
>.
   </P
></DIV
><?php manualFooter('Auth','packages.auth.auth.php');
?>