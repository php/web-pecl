<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.control.php', 'Control Structures'),
  'next' => array('standards.funcdef.php', 'Function Definitions'),
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
manualHeader('Function Calls','standards.funcalls.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.funcalls"
>Function Calls</A
></H1
><P
>&#13;    Functions should be called with no spaces between the function
    name, the opening parenthesis, and the first parameter; spaces
    between commas and each parameter, and no space between the last
    parameter, the closing parenthesis, and the semicolon. Here's an
    example:
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;$var = foo($bar, $baz, $quux);
</PRE
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    As displayed above, there should be one space on either side of an
    equals sign used to assign the return value of a function to a
    variable. In the case of a block of related assignments, more space
    may be inserted to promote readability:
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;$short         = foo($bar);
$long_variable = foo($baz);
</PRE
></TD
></TR
></TABLE
>
   </P
></DIV
><?php manualFooter('Function Calls','standards.funcalls.php');
?>