<?php

require_once "xmlrpc.inc";

response_header("XML-RPC test");

ob_start();
readfile("/home/ssb/cvs/php/pear/HTTP/HTTP-1.0.tgz");
$data = ob_get_contents();
ob_end_clean();

$f = new xmlrpcmsg('release.upload',
		   array(new xmlrpcval("HTTP", $xmlrpcString),
			 new xmlrpcval("1.0", $xmlrpcString),
			 new xmlrpcval("Initial release", $xmlrpcString),
			 new xmlrpcval($data, $xmlrpcBase64),
			 new xmlrpcval(md5($data), $xmlrpcString)));
$c = new xmlrpc_client("/xmlrpc.php", "pear.localdomain", 80);
$c->setCredentials("ssb", "bing");
$c->setDebug(1);
$r=$c->send($f);
if (!$r) { die("send failed"); }
$v=$r->value();
if (!$r->faultCode()) {
    print "return value is " . $v->scalarval() . "<BR>";
} else {
    print "Fault: ";
    print "Code: " . $r->faultCode() . 
        " Reason '" .$r->faultString()."'<BR>";
}

response_footer();

?>
