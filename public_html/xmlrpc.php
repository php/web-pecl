<?php // -*- C++ -*-

require_once "xmlrpc-methods.php";

if (empty($debug)) {
    $request_body = $HTTP_RAW_POST_DATA;
} else {
	$request_body = "<?xml version='1.0' ?>
<methodCall>
 <methodName>test</methodName>
 <params/>
</methodCall>
";
}

$xs = xmlrpc_server_create();

pear_register_xmlrpc_methods($xs);

xmlrpc_server_register_method($xs, "test", "pear_xmlrpc_test");

$response = xmlrpc_server_call_method($xs, $request_body, null,
                                      array('output_type' => 'xml',
					    'verbosity' => 'pretty'));
if (empty($debug)) {
    header('Content-type: text/xml');
} else {
    header('Content-type: text/plain');
}
header('Content-length: '.strlen($response));
print $response;

function pear_xmlrpc_test($m, $p, $a) {
    return "test successful";
}

?>
