<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('contributing-howto.php', 'How to contribute to PEAR'),
  'next' => array('contributing-introducing.php', 'Introducing your code'),
  'up'   => array('contributing-howto.php', 'How to contribute to PEAR'),
  'toc'  => array(
    array('contributing-howto.php#contributing-howto', ''),
    array('contributing-howto.php#AEN559', ''),
    array('contributing-howto.php#contributing-introduction', 'Introduction'),
    array('contributing-requirements.php', 'Requirements'),
    array('contributing-introducing.php', 'Introducing your code'))));
manualHeader('Requirements','contributing-requirements.php');
?><DIV
CLASS="sect1"
><H1
CLASS="sect1"
><A
NAME="contributing-requirements"
>Requirements</A
></H1
><P
>If you have written something, that you think should
   belong to PEAR, you have to pay attention to the following
   points:</P
><P
>&#13;    <P
></P
><UL
><LI
><P
>Your code has to be compliant to the <A
HREF="       standards.php"
TARGET="_top"
>PEAR coding standards</A
>. This is
      necessary because PEAR has the aim to provide well suited
      and standards compliant source code.</P
></LI
><LI
><P
>You are willing and able to maintain your code and to fix
      upcoming bugs in your code once you have commited it to the PEAR
      project.
      </P
></LI
><LI
><P
>You are willing document your code in an appropriate
      way. The prefered way of documentation is Docbook XML.</P
></LI
></UL
>
    
   </P
></DIV
><?php manualFooter('Requirements','contributing-requirements.php');
?>