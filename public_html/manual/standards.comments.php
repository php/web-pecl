<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.funcdef.php', 'Function Definitions'),
  'next' => array('standards.including.php', 'Including Code'),
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
manualHeader('Comments','standards.comments.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.comments"
>Comments</A
></H1
><P
>&#13;    Inline documentation for classes should follow the PHPDoc
    convention, similar to Javadoc. More information about PHPDoc can
    be found here: <A
HREF="http://www.phpdoc.de/"
TARGET="_top"
>http://www.phpdoc.de/</A
>
   </P
><P
>&#13;    Non-documentation comments are strongly encouraged. A general rule of
    thumb is that if you look at a section of code and think "Wow, I don't
    want to try and describe that", you need to comment it before you
    forget how it works.
   </P
><P
>&#13;    C style comments (/* */) and standard C++ comments (//) are both
    fine. Use of Perl/shell style comments (#) is discouraged.
   </P
></DIV
><?php manualFooter('Comments','standards.comments.php');
?>