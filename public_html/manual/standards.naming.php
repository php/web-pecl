<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.exampleurls.php', 'Example URLs'),
  'next' => array('reference.php', 'PEAR'),
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
manualHeader('Naming Conventions','standards.naming.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.naming"
>Naming Conventions</A
></H1
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="AEN164"
>Functions and Methods</A
></H2
><P
>&#13;     Functions and methods should be named using the "studly caps"
     style (also referred to as "bumpy case" or "camel caps").
     Functions should in addition have the package name as a prefix,
     to avoid name collisions between packages.  The initial letter of
     the name (after the prefix) is lowercase, and each letter that
     starts a new "word" is capitalized.  Some examples:
     <DIV
CLASS="informaltable"
><A
NAME="AEN167"
></A
><P
></P
><TABLE
BORDER="1"
CLASS="CALSTABLE"
><TBODY
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><P
>connect()</P
></TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><P
>getData()</P
></TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><P
>buildSomeWidget()</P
></TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><P
>XML_RPC_serializeData()</P
></TD
></TR
></TBODY
></TABLE
><P
></P
></DIV
>
    </P
><P
>&#13;     Private class members (meaning class members that are intented
     to be used only from within the same class in which they are
     declared; PHP does not yet support truly-enforceable private
     namespaces) are preceeded by a single underscore.  For example:
     <DIV
CLASS="informaltable"
><A
NAME="AEN180"
></A
><P
></P
><TABLE
BORDER="1"
CLASS="CALSTABLE"
><TBODY
><TR
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><P
>_sort()</P
></TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><P
>_initTree()</P
></TD
><TD
ALIGN="LEFT"
VALIGN="MIDDLE"
><P
>$this-&#62;_status</P
></TD
></TR
></TBODY
></TABLE
><P
></P
></DIV
>
    </P
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="AEN190"
>Constants</A
></H2
><P
>&#13;     Constants should always be all-uppercase, with underscores to
     seperate words.  Prefix constant names with the uppercased name
     of the class/package they are used in. For example, the constants
     used by the <TT
CLASS="literal"
>DB::</TT
> package all begin with
     "<TT
CLASS="literal"
>DB_</TT
>".
    </P
></DIV
><DIV
CLASS="sect2"
><H2
CLASS="sect2"
><A
NAME="AEN195"
>Global Variables</A
></H2
><P
>&#13;     If your package needs to define global variables, their name
     should start with a single underscore followed by the package
     name and another underscore.  For example, the PEAR package uses
     a global variable called $_PEAR_destructor_object_list.
    </P
></DIV
></DIV
><?php manualFooter('Naming Conventions','standards.naming.php');
?>