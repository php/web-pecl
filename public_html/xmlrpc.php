<?php // -*- C++ -*-

require_once "xmlrpc-methods.php";
$xs = xmlrpc_server_create();
pear_register_xmlrpc_methods($xs);
$response = xmlrpc_server_call_method($xs, $HTTP_RAW_POST_DATA, null,
                                      array('output_type' => 'xml'));
header('Content-type: text/xml');
header('Content-length: '.strlen($response));
print $response;

?>
