<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('contributing-requirements.php', 'Requirements'),
  'next' => array('packages.skeleton.php', ''),
  'up'   => array('contributing-howto.php', 'How to contribute to PEAR'),
  'toc'  => array(
    array('contributing-howto.php#contributing-howto', ''),
    array('contributing-howto.php#AEN473', ''),
    array('contributing-howto.php#contributing-introduction', 'Introduction'),
    array('contributing-requirements.php', 'Requirements'),
    array('contributing-introducing.php', 'Introducing your code'))));
manualHeader('Introducing your code','contributing-introducing.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="contributing-introducing"
>Introducing your code</A
></H1
><P
>Once you can verify that all the requirements listed
   above are ok for you, you can tell us about your plan.</P
><P
>The best way to do so is to tell the people on the <A
HREF="mailto:pear-dev@lists.php.net"
TARGET="_top"
>PEAR developers mailinglist</A
>
   about your code. You should present us the following information:</P
><P
>&#13;    <P
></P
><UL
><LI
><P
>What does your code do? (should be as verbose as
      possible)</P
></LI
><LI
><P
>Where can we have a look at the code (best thing
      is to tell us an URL where we can view the source)?</P
></LI
><LI
><P
>What requirements/dependencies does your code have?</P
></LI
></UL
>
   </P
><P
>We will then (hopefully) start discussing about your code and
   we'll decide, if we accept your contribution or not. (Don't be
   afraid, that your code will not be taken, although it is good: If it
   is well developed and we think it can be useful for some people out
   there, we will take it for sure.)</P
></DIV
><?php manualFooter('Introducing your code','contributing-introducing.php');
?>