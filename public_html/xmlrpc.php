<?php // -*- C++ -*-

if (!isset($HTTP_RAW_POST_DATA)) {
    die('invalid XML RPC request');
}
set_error_handler("xmlrpc_error_handler");

require_once "xmlrpc-methods.php";
$xs = xmlrpc_server_create();
pear_register_xmlrpc_methods($xs);
$response = xmlrpc_server_call_method($xs, $HTTP_RAW_POST_DATA, null,
                                      array('output_type' => 'xml'));
header('Content-type: text/xml');
header('Content-length: '.strlen($response));
print $response;

function xmlrpc_error_handler($errno, $errmsg, $file, $line, $vars)
{
    if (error_reporting() == 0) {
        return;
    }
    static $errortype = array (
        1   =>  "Error",
        2   =>  "Warning",
        4   =>  "Parsing Error",
        8   =>  "Notice",
        16  =>  "Core Error",
        32  =>  "Core Warning",
        64  =>  "Compile Error",
        128 =>  "Compile Warning",
        256 =>  "User Error",
        512 =>  "User Warning",
        1024=>  "User Notice"
    );
    $prefix = $errortype[$errno];
    $file = basename($file);
    $error_message = "$file:$line: $prefix: $errmsg";
    $response = "<?xml version='1.0' encoding='iso-8859-1' ?>
<methodResponse>
<fault>
 <value>
  <struct>
   <member>
    <name>faultString</name>
    <value>
     <string>$error_message</string>
    </value>
   </member>
   <member>
    <name>faultCode</name>
    <value>
     <int>$errno</int>
    </value>
   </member>
  </struct>
 </value>
</fault>
</methodResponse>
";
    header("Content-length: " . strlen($response));
    header("Content-type: text/xml");
    print $response;
    exit;
}

?>
