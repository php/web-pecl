<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('standards.header.php', 'Header Comment Blocks'),
  'next' => array('standards.exampleurls.php', 'Example URLs'),
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
manualHeader('Using CVS','standards.cvs.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="standards.cvs"
>Using CVS</A
></H1
><P
>&#13;    This section applies only to packages using CVS at cvs.php.net.
   </P
><P
>&#13;    Include the $Id$ CVS keyword in each file.  As each file
    is edited, add this tag if it's not yet present (or replace
    existing forms such as "Last Modified:", etc.).

   </P
><P
>&#13;    The rest of this section assumes that you have basic knowledge
    about CVS tags and branches.
   </P
><P
>&#13;    CVS tags are used to label which revisions of the files in your
    package belong to a given release.  Below is a list of the
    required and suggested CVS tags:

    <P
></P
><DIV
CLASS="variablelist"
><DL
><DT
>RELEASE_<TT
CLASS="replaceable"
><I
>n_n</I
></TT
></DT
><DD
><P
>&#13;	(required) Used for tagging a release.  If you don't use it,
	there's no way to go back and retrieve your package from the
	CVS server in the state it was in at the time of the release.
       </P
></DD
><DT
>QA_<TT
CLASS="replaceable"
><I
>n_n</I
></TT
></DT
><DD
><P
>&#13;	(branch, optional) If you feel you need to roll out a
	release candidate before releasing, it's a good idea to make a
	branch for it so you can isolate the release and apply only
	those critical fixes before the actual release.  Meanwhile,
	normal development may continue on the main trunk.
       </P
></DD
><DT
>MAINT_<TT
CLASS="replaceable"
><I
>n_n</I
></TT
></DT
><DD
><P
>&#13;	(branch, optional) If you need to make "micro-releases" (for
	example 1.2.1 and so on after 1.2), you can use a branch for
	that too, if your main trunk is very active and you want only
	minor changes between your micro-releases.
       </P
></DD
></DL
></DIV
>
    Only the RELEASE tag is required, the rest are recommended for
    your convenience.
   </P
><P
>&#13;    Below is an example of how to tag the 1.2 release of the
    "Money_Fast" package:
    <DIV
CLASS="informalexample"
><A
NAME="AEN139"
></A
><P
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="screen"
><TT
CLASS="prompt"
>$ </TT
><B
CLASS="command"
>cd pear/Money_Fast</B
>
<TT
CLASS="prompt"
>$ </TT
><B
CLASS="command"
>cvs tag RELEASE_1_2</B
>
<TT
CLASS="computeroutput"
>T Fast.php
T README
T package.xml
</TT
>
</PRE
></TD
></TR
></TABLE
><P
></P
></DIV
>
    By doing this you make it possible for the PEAR web site to take
    you through the rest of your release process.
   </P
><P
>&#13;    Here's an example of how to create a QA branch:
    <DIV
CLASS="informalexample"
><A
NAME="AEN147"
></A
><P
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="screen"
><TT
CLASS="prompt"
>$ </TT
><B
CLASS="command"
>cvs tag QA_2_0_BP</B
>
...
<TT
CLASS="prompt"
>$ </TT
><B
CLASS="command"
>cvs rtag -b -r QA_2_0_BP QA_2_0</B
>
<TT
CLASS="prompt"
>$ </TT
><B
CLASS="command"
>cvs update -r QA_2_0</B
>
<TT
CLASS="prompt"
>$ </TT
><B
CLASS="command"
>cvs tag RELEASE_2_0RC1</B
>
...and then the actual release, from the same branch:
<TT
CLASS="prompt"
>$ </TT
><B
CLASS="command"
>cvs tag RELEASE_2_0</B
>
</PRE
></TD
></TR
></TABLE
><P
></P
></DIV
>
    The "QA_2_0_BP" tag is a "branch point" tag, which is the start
    point of the tag.  It's always a good idea to start a CVS branch
    from such branch points.  MAINT branches may use the RELEASE tag
    as their branch point.
   </P
></DIV
><?php manualFooter('Using CVS','standards.cvs.php');
?>