<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.comments.php', 'Comments'),
  'next' => array('standards.tags.php', 'PHP Code Tags'),
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
manualHeader('Including Code','standards.including.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.including"
>Including Code</A
></H1
><P
>&#13;    Anywhere you are unconditionally including a class file, use
    <B
CLASS="function"
>require_once()</B
>. Anywhere you are conditionally
    including a class file (for example, factory methods), use
    <B
CLASS="function"
>include_once()</B
>. Either of these will ensure
    that class files are included only once. They share the same file
    list, so you don't need to worry about mixing them - a file
    included with <B
CLASS="function"
>require_once()</B
> will not be
    included again by <B
CLASS="function"
>include_once()</B
>.
    <DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>Note: </B
>
      <B
CLASS="function"
>include_once()</B
> and
      <B
CLASS="function"
>require_once()</B
> are statements, not
      functions. You don't <I
CLASS="emphasis"
>need</I
> parentheses
      around the filename to be included.
     </P
></BLOCKQUOTE
></DIV
>
   </P
></DIV
><?php manualFooter('Including Code','standards.including.php');
?>