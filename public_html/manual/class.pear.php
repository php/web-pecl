<?php 
sendManualHeaders('ISO-8859-1','en');
setupNavigation(array(
  'home' => array('index.php', 'PEAR Manual'),
  'prev' => array('reference.php', 'PEAR'),
  'next' => array('class.pear-error.php', 'PEAR_Error'),
  'up'   => array('reference.php', 'PEAR'),
  'toc'  => array(
    array('reference.php#reference', ''),
    array('reference.php#AEN200', ''),
    array('reference.php#AEN201', ''),
    array('class.pear.php', 'PEAR'),
    array('class.pear-error.php', 'PEAR_Error'))));
manualHeader('PEAR','class.pear.php');
?><H1
><A
NAME="class.pear"
>PEAR</A
></H1
><DIV
CLASS="refnamediv"
><A
NAME="AEN205"
></A
>PEAR&nbsp;--&nbsp;PEAR base class</DIV
><DIV
CLASS="refsynopsisdiv"
><A
NAME="AEN208"
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
>require_once "PEAR.php";</PRE
></TD
></TR
></TABLE
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="synopsis"
>class <TT
CLASS="replaceable"
><I
>classname</I
></TT
> extends PEAR { ... }</PRE
></TD
></TR
></TABLE
></DIV
><DIV
CLASS="refsect1"
><A
NAME="AEN213"
></A
><H2
>Description</H2
><P
>&#13;     The PEAR base class provides standard functionality that is used
     by most PEAR classes.  Normally you never make an instance of the
     PEAR class directly, you use it by subclassing it.
    </P
><P
>&#13;     Its key features are:
     <P
></P
><UL
><LI
><P
>request-shutdown object "destructors"</P
></LI
><LI
><P
>error handling</P
></LI
></UL
>
    </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="destructors"
></A
><H2
>PEAR "destructors"</H2
><P
>&#13;     If you inherit <A
HREF="class.pear.php"
><B
CLASS="classname"
>PEAR</B
></A
> in a class called
     <TT
CLASS="replaceable"
><I
>ClassName</I
></TT
>, you can define a method in
     it called called _<TT
CLASS="replaceable"
><I
>ClassName</I
></TT
> (the
     class name with an underscore prepended) that will be invoked
     when the request is over.  This is not a destructor in the sense
     that you can "delete" an object and have the destructor called,
     but in the sense that PHP gives you a callback in the object
     when PHP is done executing.  See <A
HREF="class.pear.php#example.destructors"
>the example</A
> below.
    </P
><P
>&#13;     <DIV
CLASS="warning"
><P
></P
><TABLE
CLASS="warning"
BORDER="1"
WIDTH="100%"
><TR
><TD
ALIGN="CENTER"
><B
><A
NAME="destructors.warning"
></A
>Important!</B
></TD
></TR
><TR
><TD
ALIGN="LEFT"
><P
>&#13;       In order for destructors to work properly, you must
       instantiate your class with the "=&#38; new" operator like
       this:
       <TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;$obj =&#38; new MyClass();
</PRE
></TD
></TR
></TABLE
>
      </P
><P
>&#13;       If you only use "= new", the object registered in PEAR's
       shutdown list will be a copy of the object at the time the
       constructor is called, and it will this copy's "destructor"
       that will be called upon request shutdown.
      </P
></TD
></TR
></TABLE
></DIV
>
    </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="error-handling"
></A
><H2
>PEAR Error Handling</H2
><P
>&#13;     PEAR's base class also provides a way of passing around more
     complex errors than a true/false value or a numeric code.  A
     PEAR error is an object that is either an instance of the class
     <A
HREF="class.pear-error.php"
><B
CLASS="classname"
>PEAR_Error</B
></A
>, or some class inheriting
     <A
HREF="class.pear-error.php"
><B
CLASS="classname"
>PEAR_Error</B
></A
>.
    </P
><P
>&#13;     One of the design criteria of PEAR's errors is that it should not
     force a particular type of output on the user, it should be
     possible to handle errors without any output at all if that is
     desirable.  This makes it possible to handle errors gracefully,
     also when your output format is different from HTML (for example
     WML or some other XML format).
    </P
><P
>&#13;     The error object can be configured to do a number of things when
     it is created, such as printing an error message, printing the
     message and exiting, raising an error with PHP's
     <B
CLASS="function"
>trigger_error()</B
> function, invoke a callback,
     or none of the above.  This is typically specified in
     <A
HREF="class.pear-error.php"
><B
CLASS="classname"
>PEAR_Error</B
></A
>'s constructor, but all of the
     parameters are optional, and you can set up defaults for errors
     generated from each object based on the
     <A
HREF="class.pear.php"
><B
CLASS="classname"
>PEAR</B
></A
> class.  See the <A
HREF="class.pear.php#example.error1"
>PEAR error examples</A
> for how
     to use it and the <A
HREF="class.pear-error.php"
><B
CLASS="classname"
>PEAR_Error</B
></A
> reference
     for the full details.
    </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="AEN247"
></A
><H2
>Examples</H2
><P
>&#13;     The example below shows how to use the PEAR's "poor man's kinda
     emulated destructors" to implement a simple class that holds the
     contents of a file, lets you append data to the object and
     flushes the data back to the file at the end of the request:
     <TABLE
WIDTH="100%"
BORDER="0"
CELLPADDING="0"
CELLSPACING="0"
CLASS="EXAMPLE"
><TR
><TD
><DIV
CLASS="example"
><A
NAME="example.destructors"
></A
><P
><B
>Example 1. PEAR: emulated destructors</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;require_once "PEAR.php";

class FileContainer extends PEAR
{
    var $file = '';
    var $contents = '';
    var $modified = 0;
    
    function FileContainer($file)
    {
        $this-&#62;PEAR(); // this calls the parent class constructor
        $fp = fopen($file, "r");
        if (!is_resource($fp)) {
            return;
        }
        while (!empty($data = fread($fp, 2048))) {
            $this-&#62;contents .= $data;
    	}
        fclose($fp);
    }

    function append($str)
    {
        $this-&#62;contents .= $str;
        $this-&#62;modified++;
    }

    // The "destructor" is named like the constructor
    // but with an underscore in front.
    function _FileContainer()
    {
        if ($this-&#62;modified) {
            $fp = fopen($this-&#62;file, "w");
            if (!is_resource($fp)) {
                return;
            }
            fwrite($fp, $this-&#62;contents);
            fclose($fp);
        }
    }
}

$fileobj =&#38; new FileContainer("testfile");
$fileobj-&#62;append("this ends up at the end of the file\n");

// When the request is done and PHP shuts down, $fileobj's
// "destructor" is called and updates the file on disk.

</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
>
     <DIV
CLASS="note"
><BLOCKQUOTE
CLASS="note"
><P
><B
>Note: </B
>
       PEAR "destructors" use PHP's shutdown callbacks
       (<B
CLASS="function"
>register_shutdown_function()</B
>), and you
       can't output anything from these when PHP is running in a web
       server.  So anything printed in a "destructor" gets lost except
       when PHP is used in command-line mode.  Bummer.
      </P
><P
>&#13;       Also, see the <A
HREF="class.pear.php#destructors.warning"
>warning</A
> about how to
       instantiate objects if you want to use the destructor.
      </P
></BLOCKQUOTE
></DIV
>
    </P
><P
>&#13;     The next examples illustrate different ways of using PEAR's error
     handling mechanism.
    </P
><P
>&#13;     <TABLE
WIDTH="100%"
BORDER="0"
CELLPADDING="0"
CELLSPACING="0"
CLASS="EXAMPLE"
><TR
><TD
><DIV
CLASS="example"
><A
NAME="example.error1"
></A
><P
><B
>Example 2. PEAR error example (1)</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;function mysockopen($host = "localhost", $port = 8090)
{
    $fp = fsockopen($host, $port, $errno, $errstr);
    if (!is_resource($fp)) {
        return new PEAR_Error($errstr, $errno);
    }
    return $fp;
}

$sock = mysockopen();
if (PEAR::isError($sock)) {
    print "mysockopen error: ".$sock-&#62;getMessage()."&#60;BR&#62;\n"
}
</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
>
    </P
><P
>&#13;     This example shows a wrapper to <B
CLASS="function"
>fsockopen()</B
>
     that delivers the error code and message (if any) returned by
     fsockopen in a PEAR error object.  Notice that
     <B
CLASS="function"
>PEAR::isError()</B
> is used to detect whether a
     value is a PEAR error.
    </P
><P
>&#13;     PEAR_Error's mode of operation in this example is simply
     returning the error object and leaving the rest to the user
     (programmer).  This is the default error mode.
    </P
><P
>&#13;     In the next example we're showing how to use default error modes:
    </P
><P
>&#13;     <TABLE
WIDTH="100%"
BORDER="0"
CELLPADDING="0"
CELLSPACING="0"
CLASS="EXAMPLE"
><TR
><TD
><DIV
CLASS="example"
><A
NAME="example.error2"
></A
><P
><B
>Example 3. PEAR error example (2)</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;class TCP_Socket extends PEAR
{
    var $sock;

    function TCP_Socket()
    {
        $this-&#62;PEAR();
    }

    function connect($host, $port)
    {
        $sock = fsockopen($host, $port, $errno, $errstr);
        if (!is_resource($sock)) {
            return $this-&#62;raiseError($errstr, $errno);
        }
    }
}

$sock = new TCP_Socket;
$sock-&#62;setErrorHandling(PEAR_ERROR_DIE);
$sock-&#62;connect("localhost", 8090);
print "still alive&#60;BR&#62;\n";
</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
>
    </P
><P
>&#13;     Here, we set the default error mode to
     <A
HREF="class.pear-error.php#constant.pear-error-die"
><TT
CLASS="constant"
><B
>PEAR_ERROR_DIE</B
></TT
></A
>, and since we don't specify
     any error mode in the raiseError call (that'd be the third
     parameter), raiseError uses the default error mode and exits if
     fsockopen fails.
    </P
></DIV
><DIV
CLASS="refsect1"
><A
NAME="AEN274"
></A
><H2
>Global Variables Used</H2
><P
>&#13;     The PEAR class uses some global variables to register global
     defaults, and an object list used by the "destructors".  All of
     the global variables associated with the PEAR class have a
     <TT
CLASS="literal"
>_PEAR_</TT
> name prefix.
    </P
><P
>&#13;     <P
></P
><DIV
CLASS="variablelist"
><DL
><DT
>$_PEAR_default_error_mode</DT
><DD
><P
>&#13;	 If no default error mode is set in an object, this mode will
	 be used.  Must be one of
	 <A
HREF="class.pear-error.php#constant.pear-error-return"
><TT
CLASS="constant"
><B
>PEAR_ERROR_RETURN</B
></TT
></A
>,
	 <A
HREF="class.pear-error.php#constant.pear-error-print"
><TT
CLASS="constant"
><B
>PEAR_ERROR_PRINT</B
></TT
></A
>,
	 <A
HREF="class.pear-error.php#constant.pear-error-trigger"
><TT
CLASS="constant"
><B
>PEAR_ERROR_TRIGGER</B
></TT
></A
>,
	 <A
HREF="class.pear-error.php#constant.pear-error-die"
><TT
CLASS="constant"
><B
>PEAR_ERROR_DIE</B
></TT
></A
> or
	 <A
HREF="class.pear-error.php#constant.pear-error-callback"
><TT
CLASS="constant"
><B
>PEAR_ERROR_CALLBACK</B
></TT
></A
>.
	</P
><P
>&#13;	 Don't set this variable directly, call
	 <B
CLASS="function"
>PEAR::setErrorHandling()</B
> as a static
	 method like this:
	 <DIV
CLASS="informalexample"
><A
NAME="AEN291"
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
CLASS="programlisting"
>&#13;PEAR::setErrorHandling(PEAR_ERROR_DIE);
</PRE
></TD
></TR
></TABLE
><P
></P
></DIV
>
	</P
></DD
><DT
>$_PEAR_default_error_options</DT
><DD
><P
>&#13;	 If the error mode is <A
HREF="class.pear-error.php#constant.pear-error-trigger"
><TT
CLASS="constant"
><B
>PEAR_ERROR_TRIGGER</B
></TT
></A
>,
	 this is the error level (one of
	 <TT
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
>).
	</P
><P
>&#13;	 Don't set this variable directly, call
	 <B
CLASS="function"
>PEAR::setErrorHandling()</B
> as a static
	 method like this:
	 <DIV
CLASS="informalexample"
><A
NAME="AEN303"
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
CLASS="programlisting"
>&#13;PEAR::setErrorHandling(PEAR_ERROR_TRIGGER, E_USER_ERROR);
</PRE
></TD
></TR
></TABLE
><P
></P
></DIV
>
	</P
></DD
><DT
>$_PEAR_default_error_callback</DT
><DD
><P
>&#13;	 If no <TT
CLASS="replaceable"
><I
>options</I
></TT
> parameter is used
	 when an error is raised and the error mode is
	 <A
HREF="class.pear-error.php#constant.pear-error-callback"
><TT
CLASS="constant"
><B
>PEAR_ERROR_CALLBACK</B
></TT
></A
>, the value of this
	 variable is used as the callback.  This means that you can
	 switch the error mode temporarily and return to callback mode
	 without specifying the callback function again.  A string
	 value represents a function, a two-element array with an
	 object at index 0 and a string at index 1 represents a
	 method.
	</P
><P
>&#13;	 Again, don't set this variable directly, call
	 <B
CLASS="function"
>PEAR::setErrorHandling()</B
> as a static
	 method like this:
	 <DIV
CLASS="informalexample"
><A
NAME="AEN313"
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
CLASS="programlisting"
>&#13;PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "my_error_handler");
</PRE
></TD
></TR
></TABLE
><P
></P
></DIV
>
	</P
><P
>&#13;	 Here is an example of how you can switch back and forth
	 without specifying the callback function again:
	 <DIV
CLASS="informalexample"
><A
NAME="AEN316"
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
CLASS="programlisting"
>&#13;PEAR::setErrorMode(PEAR_ERROR_CALLBACK, "my_function_handler");
do_some_stuff();
PEAR::setErrorMode(PEAR_ERROR_DIE);
do_some_critical_stuff();
PEAR::setErrorMode(PEAR_ERROR_CALLBACK);
// now we're back to using my_function_handler again
</PRE
></TD
></TR
></TABLE
><P
></P
></DIV
>
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
NAME="AEN318"
></A
><H2
>Methods</H2
><DIV
CLASS="refsect2"
><A
NAME="function.pear"
></A
><H3
>PEAR::PEAR</H3
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN322"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>PEAR()</CODE
> (void)</CODE
></P
><P
></P
></DIV
><P
>&#13;      This is the PEAR class constructor.  Call it from the
      constructor of every class inheriting the PEAR class.
      <TABLE
WIDTH="100%"
BORDER="0"
CELLPADDING="0"
CELLSPACING="0"
CLASS="EXAMPLE"
><TR
><TD
><DIV
CLASS="example"
><A
NAME="AEN327"
></A
><P
><B
>Example 4. PEAR Class Constructor Example</B
></P
><TABLE
BORDER="0"
BGCOLOR="#E0E0E0"
WIDTH="100%"
><TR
><TD
><PRE
CLASS="programlisting"
>&#13;class MyClass extends PEAR
{
    var $foo, $bar;
    function MyClass($foo, $bar)
    {
        $this-&#62;PEAR();
        $this-&#62;foo = $foo;
        $this-&#62;bar = $bar;
    }
}
</PRE
></TD
></TR
></TABLE
></DIV
></TD
></TR
></TABLE
>
     </P
></DIV
><DIV
CLASS="refsect2"
><A
NAME="function.-pear"
></A
><H3
>PEAR::_PEAR</H3
><DIV
CLASS="funcsynopsis"
><A
NAME="AEN332"
></A
><P
></P
><P
><CODE
><CODE
CLASS="FUNCDEF"
>_PEAR()</CODE
> (void)</CODE
></P
><P
></P
></DIV
><P
>&#13;      This is the PEAR class destructor.  It is called during request
      shutdown.
     </P
></DIV
></DIV
><?php manualFooter('PEAR','class.pear.php');
?>