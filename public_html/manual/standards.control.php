<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.php', 'Coding Standards'),
  'next' => array('standards.funcalls.php', 'Function Calls'),
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
manualHeader('Control Structures','standards.control.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.control"
>Control Structures</A
></H1
><P
>&#13;    These include if, for, while, switch, etc. Here is an example if
    statement, since it is the most complicated of them:
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;if ((condition1) || (condition2)) {
    action1;
} elseif ((condition3) &#38;&#38; (condition4)) {
    action2;
} else {
    defaultaction;
}
</PRE
></TD
></TR
></TABLE
>
   </P
><P
>&#13;    Control statements should have one space between the control keyword
    and opening parenthesis, to distinguish them from function calls.
   </P
><P
>&#13;    You are strongly encouraged to always use curly braces even in
    situations where they are technically optional. Having them
    increases readability and decreases the likelihood of logic errors
    being introduced when new lines are added.
   </P
><P
>&#13;    For switch statements:
     <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;switch (condition) {
case 1:
    action1;
    break;

case 2:
    action2;
    break;

default:
    defaultaction;
    break;

}
</PRE
></TD
></TR
></TABLE
>
   </P
></DIV
><?php manualFooter('Control Structures','standards.control.php');
?>