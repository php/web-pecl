<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('class.pear.php', 'PEAR'),
  'next' => array('packages.php', 'PEAR Packages'),
  'up'   => array('reference.php', 'PEAR'),
  'toc'  => array(
    array('reference.php#reference', ''),
    array('reference.php#AEN200', ''),
    array('reference.php#AEN201', ''),
    array('class.pear.php', 'PEAR'),
    array('class.pear-error.php', 'PEAR_Error'))));
manualHeader('PEAR_Error','class.pear-error.php');
?><H1
><A
NAME="class.pear-error"
>PEAR_Error</A
></H1
><DIV
CLASS="refnamediv"
><A
NAME="AEN338"
></A
>PEAR_Error&nbsp;--&nbsp;PEAR error mechanism base class</DIV
><DIV
CLASS="refsynopsisdiv"
><A
NAME="AEN341"
></A
><H2
>Synopsis</H2
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="synopsis"
>$err = new PEAR_Error($msg);</PRE
></TD
></TR
></TABLE
></DIV
><DIV
CLASS="refsect1"
><A
NAME="AEN344"
></A
><H2
>Error Modes</H2
><P
>&#13;     An error object has a mode of operation that can be set with one
     of the following constants:
     <P
></P
><DIV
CLASS="variablelist"
><DL
><DT
><A
NAME="constant.pear-error-return"
>PEAR_ERROR_RETURN</A
></DT
><DD
><P
>&#13;	 Just return the object, don't do anything special in
	 PEAR_Error's constructor.
	</P
></DD
><DT
><A
NAME="constant.pear-error-print"
>PEAR_ERROR_PRINT</A
></DT
><DD
><P
>&#13;	 Print the error message in the constructor.  The execution is
	 not interrupted.
	</P
></DD
><DT
><A
NAME="constant.pear-error-trigger"
>PEAR_ERROR_TRIGGER</A
></DT
><DD
><P
>&#13;	 Use PHP's <B
CLASS="function"
>trigger_error()</B
> function to
	 raise an internal error in PHP.  The execution is aborted if
	 you have defined your own PHP error handler or if you set the
	 error severity to E_USER_ERROR.
	</P
></DD
><DT
><A
NAME="constant.pear-error-die"
>PEAR_ERROR_DIE</A
></DT
><DD
><P
>&#13;	 Print the error message and exit.  Execution is of course
	 aborted.
	</P
></DD
><DT
><A
NAME="constant.pear-error-callback"
>PEAR_ERROR_CALLBACK</A
></DT
><DD
><P
>&#13;	 Use a callback function or method to handle errors.
	 Execution is aborted.
	</P
></DD
></DL
></DIV
>
    </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="AEN369"
></A
><H2
>Properties</H2
><P
></P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="AEN372"
></A
><H2
>Methods</H2
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN374"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
><B
CLASS="function"
>PEAR_Error::PEAR_Error</B
></CODE
> ([message
       code
       mode
       options
       userinfo]]]]])</CODE
></P
><P
></P
></DIV
><DIV
CLASS="refsect2"
><A
NAME="AEN389"
></A
><H3
>Description</H3
><P
>&#13;      PEAR_Error constructor.  Parameters:
      <P
></P
><DIV
CLASS="variablelist"
><DL
><DT
>message</DT
><DD
><P
>&#13;	  error message, defaults to "unknown error"
	 </P
></DD
><DT
>code</DT
><DD
><P
>&#13;	  error code (optional)
	 </P
></DD
><DT
>mode</DT
><DD
><P
>&#13;	  Mode of operation.  See the <A
HREF="class.pear-error.php#error-modes"
>error modes</A
> section for
	  details.
	 </P
></DD
><DT
>options</DT
><DD
><P
>&#13;	  If the mode of can have any options specified, use this
	  parameter.  Currently the "trigger" and "callback" modes are
	  the only using the options parameter.  For trigger mode,
	  this parameter is one of <TT
CLASS="constant"
><B
>E_USER_NOTICE</B
></TT
>,
	  <TT
CLASS="constant"
><B
>E_USER_WARNING</B
></TT
> or
	  <TT
CLASS="constant"
><B
>E_USER_ERROR</B
></TT
>.  For callback mode, this
	  parameter should contain either the callback function name
	  (string), or a two-element (object, string) array
	  representing an object and a method name.
	 </P
></DD
></DL
></DIV
>
     </P
></DIV
></DIV
><?php manualFooter('PEAR_Error','class.pear-error.php');
?>