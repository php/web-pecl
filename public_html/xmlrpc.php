<?php // -*- C++ -*-

require_once "xmlrpc-methods.php";

if (empty($debug)) {
    $request_body = $HTTP_RAW_POST_DATA;
} else {
	$request_body = "<?xml version='1.0' ?>
<methodCall>
 <methodName>package.new</methodName>
 <params>
  <param>
   <value><string>{$query}</string></value>
  </param>
 </params>
</methodCall>
";
}

$xs = xmlrpc_server_create();

foreach ($pear_xmlrpc_methods as $method) {
    $handler = "pear_xmlrpc_".preg_replace('/[^a-z0-9]/i', '.', $method);
    xmlrpc_server_register_method($xs, $method, $handler);
}

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

?>
