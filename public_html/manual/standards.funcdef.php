<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.funcalls.php', 'Function Calls'),
  'next' => array('standards.comments.php', 'Comments'),
  'up'   => array('standards.php', 'Coding Standards'),
  'toc'  => array(
    array('standards.php#standards', ''),
    array('standards.php#AEN57', ''),
    array('standards.php#standards.indenting', 'Indenting'),
    array('standards.control.php', 'Control Structures'),
    array('standards.funcalls.php', 'Function Calls'),
    array('standards.funcdef.php', 'Function Definitions'),
    array('standards.comments.php', 'Comments'),
    array('standards.including.php', 'Including Code'),
    array('standards.tags.php', 'PHP Code Tags'),
    array('standards.header.php', 'Header Comment Blocks'),
    array('standards.cvs.php', 'Using CVS'),
    array('standards.exampleurls.php', 'Example URLs'),
    array('standards.naming.php', 'Naming Conventions'))));
manualHeader('Function Definitions','standards.funcdef.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.funcdef"
>Function Definitions</A
></H1
><P
>&#13;    Function declaractions follow the "one true brace" convention:
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;function fooFunction($arg1, $arg2 = '')
{
    if (condition) {
        statement;
    }
    return $val;
}
</PRE
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    Arguments with default values go at the end of the argument list.
    Always attempt to return a meaningful value from a function if one
    is appropriate. Here is a slightly longer example:
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;function connect(&#38;$dsn, $persistent = false)
{
    if (is_array($dsn)) {
        $dsninfo = &#38;$dsn;
    } else {
        $dsninfo = DB::parseDSN($dsn);
    }
    
    if (!$dsninfo || !$dsninfo['phptype']) {
        return $this-&#62;raiseError();
    }
    
    return true;
}
    </PRE
></TD
></TR
></TABLE
>
   </P
></DIV
><?php manualFooter('Function Definitions','standards.funcdef.php');
?>