<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

if (isset($_SERVER['PHP_AUTH_USER']) && !isset($_COOKIE['PEAR_USER'])) {
    $_COOKIE['PEAR_USER'] = $_SERVER['PHP_AUTH_USER'];
}
if (isset($_SERVER['PHP_AUTH_PW']) && !isset($_COOKIE['PEAR_PW'])) {
    $_COOKIE['PEAR_PW'] = $_SERVER['PHP_AUTH_PW'];
}

PEAR::setErrorHandling(PEAR_ERROR_RETURN);

// {{{ pear_xmlrpc_error()

function pear_xmlrpc_error($error) {
    return false;
}

// }}}

function response_header($title) {}
function response_footer() {}
function report_error($error) {
    $response = "<?xml version='1.0' encoding='iso-8859-1' ?>
<methodResponse>
<fault>
 <value>
  <struct>
   <member>
    <name>faultString</name>
    <value>
     <string>$error</string>
    </value>
   </member>
   <member>
    <name>faultCode</name>
    <value>
     <int>-1</int>
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
	
}

// {{{ xu_query_http_post()

/* generic function to call an http server with post method */
function xu_query_http_post($request,
                            $host,
                            $uri,
                            $port,
                            $debug, 
                            $timeout,
                            $user,
                            $pass,
                            $secure = false)
{
    $response_buf = "";
    if ($host && $uri && $port) {
        $content_len = strlen($request);
        
        $fsockopen = $secure ? "fsockopen_ssl" : "fsockopen";

        $query_fd = $fsockopen($host, $port, $errno, $errstr, 10);
        if ($query_fd) {
            
            $auth = "";
            if ($user) {
                $auth = "Authorization: Basic " .
                    base64_encode($user . ":" . $pass) . "\r\n";
            }
            
            $http_request = 
                "POST $uri HTTP/1.0\r\n" .
                "User-Agent: xmlrpc-epi-php/0.2 (PHP)\r\n" .
                "Host: $host:$port\r\n" .
                $auth .
                "Content-Type: text/xml\r\n" .
                "Content-Length: $content_len\r\n" . 
                "\r\n" .
                $request;

            fputs($query_fd, $http_request, strlen($http_request));

            while (!feof($query_fd)) {
                $line = fgets($query_fd, 4096);
                if (!$header_parsed) {
                    if ($line === "\r\n" || $line === "\n") {
                        $header_parsed = 1;
                    }
                }
                else {
                    $response_buf .= $line;
                }
            }
            
            fclose($query_fd);
        }
    }

    return $response_buf;
}

// }}}
// {{{ xu_rpc_http()

/* call an xmlrpc method on a remote http server */
function xu_rpc_http($method_name,
                     $args,
                     $host,
                     $uri = "/",
                     $port = 80,
                     $debug = false, 
                     $timeout = 0,
                     $user = false,
                     $pass = false,
                     $secure = false)
{
    $response_buf = "";
    if ($host && $uri && $port) {
        $request_xml = xmlrpc_encode_request($method_name, $args, array(version => "xmlrpc"));
        $response_buf = xu_query_http_post($request_xml, $host, $uri, $port, $debug,
                                           $timeout, $user, $pass, $secure);
        
        $retval = xu_find_and_decode_xml($response_buf, $debug);
    }
    return $retval;
}

// }}}
// {{{ xu_find_and_decode_xml()

function xu_find_and_decode_xml($buf)
{
    if (strlen($buf)) {
        $xml_begin = substr($buf, strpos($response_buf, "<?xml"));
        if (strlen($xml_begin)) {
            $retval = xmlrpc_decode($xml_begin);
        }
    }
    return $retval;
}

// }}}

?>
