<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('introduction.php', 'Introduction'),
  'next' => array('standards.control.php', 'Control Structures'),
  'up'   => array('getting-started.php', 'Getting Started'),
  'toc'  => array(
    array('getting-started.php#getting-started', ''),
    array('getting-started.php#AEN0', ''),
    array('introduction.php', 'Introduction'),
    array('getting-started.php#AEN0', ''),
    array('standards.php', 'Coding Standards'),
    array('getting-started.php#AEN0', ''),
    array('reference.php', 'PEAR'))));
manualHeader('Coding Standards','standards.php');
?><DIV
CLASS="chapter"
><H1
><A
NAME="standards"
>Chapter 2. Coding Standards</A
></H1
><DIV
CLASS="TOC"
><DL
><DT
><B
>Table of Contents</B
></DT
><DT
><A
HREF="standards.php#standards.indenting"
>Indenting</A
></DT
><DT
><A
HREF="standards.control.php"
>Control Structures</A
></DT
><DT
><A
HREF="standards.funcalls.php"
>Function Calls</A
></DT
><DT
><A
HREF="standards.funcdef.php"
>Function Definitions</A
></DT
><DT
><A
HREF="standards.comments.php"
>Comments</A
></DT
><DT
><A
HREF="standards.including.php"
>Including Code</A
></DT
><DT
><A
HREF="standards.tags.php"
>PHP Code Tags</A
></DT
><DT
><A
HREF="standards.header.php"
>Header Comment Blocks</A
></DT
><DT
><A
HREF="standards.cvs.php"
>Using CVS</A
></DT
><DT
><A
HREF="standards.exampleurls.php"
>Example URLs</A
></DT
><DT
><A
HREF="standards.naming.php"
>Naming Conventions</A
></DT
></DL
></DIV
><DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>Note: </B
>
    The PEAR Coding Standards applies to code that is to become a part
    of PEAR, either distributed with PHP or available for download via
    PEAR's install tool.
   </P
></BLOCKQUOTE
></DIV
><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.indenting"
>Indenting</A
></H1
><P
>&#13;    Use an indent of 4 spaces, with no tabs. If you use Emacs to edit PEAR
    code, you should set indent-tabs-mode to nil. Here is an example mode
    hook that will set up Emacs according to these guidelines (you will
    need to ensure that it is called when you are editing PHP files):
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;(defun php-mode-hook ()
  (setq tab-width 4
        c-basic-offset 4
        c-hanging-comment-ender-p nil
  	indent-tabs-mode
	(not
	 (and (string-match "/\\(PEAR\\|pear\\)/" (buffer-file-name))
	      (string-match "\.php$" (buffer-file-name))))))
</PRE
></TD
></TR
></TABLE
>
   </P
><P
>Here are vim rules for the same thing:
    <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;  set expandtab 
  set shiftwidth=4 
  set tabstop=4 
</PRE
></TD
></TR
></TABLE
>
   </P
></DIV
></DIV
><?php manualFooter('Coding Standards','standards.php');
?>