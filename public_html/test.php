<?php

require_once "XML/RPC.php";

response_header("XML-RPC test");

ob_start();
readfile("/home/ssb/cvs/php/pear/HTTP/HTTP-1.0.tgz");
$data = ob_get_contents();
ob_end_clean();

$f = new XML_RPC_Message('release.upload',
                         array(new XML_RPC_Value("HTTP",
                                                 $XML_RPC_String),
                               new XML_RPC_Value("1.0",
                                                 $XML_RPC_String),
                               new XML_RPC_Value("Initial release",
                                                 $XML_RPC_String),
                               new XML_RPC_Value($data,
                                                 $XML_RPC_Base64),
                               new XML_RPC_Value(md5($data),
                                                 $XML_RPC_String)));
$c = new XML_RPC_Client("/xmlrpc.php", "pear.localdomain", 80);
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
