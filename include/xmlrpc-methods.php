<?php // -*- PHP -*-
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Stig Sæther Bakken <ssb@fast.no>                            |
   |                                                                      |
   +----------------------------------------------------------------------+
 */

require_once "signatures.php";

parse_signatures_from_file("../include/pear-database.php",
                           &$xmlrpc_method_index, "index");

// {{{ pear_register_xmlrpc_methods()

function pear_register_xmlrpc_methods($xs)
{
    global $xmlrpc_method_index;
    foreach ($xmlrpc_method_index["index"] as $method => $foo) {
//        error_log("registering $method");
        xmlrpc_server_register_method($xs, $method, "pear_xmlrpc_dispatcher");
    }
    xmlrpc_server_register_introspection_callback($xs, "pear_xmlrpc_introspection_callback");
}

// }}}
// {{{ pear_xmlrpc_dispatcher()

function pear_xmlrpc_dispatcher($method_name, $params, $appdata)
{
    global $xmlrpc_method_index;
    if (empty($xmlrpc_method_index["index"][$method_name])) {
        error_log("pear xmlrpc unknown method: $method_name");
        return false; // XXX FAULT
    }
    $type_key = "";
    for ($i = 0; $i < sizeof($params); $i++) {
        if ($i > 0) {
            $type_key .= ",";
        }
        $type_key .= xmlrpc_get_type($params[$i]);
    }
    if (!isset($xmlrpc_method_index["index"][$method_name][$type_key])) {
        error_log("pear xmlrpc no signature found for $method_name($type_key)");
        return false; // XXX FAULT
    }
	$auth = $xmlrpc_method_index["auth"][$method_name];
	if ($auth != "all") {
		auth_require($auth == "admin");
	}
    $function = $xmlrpc_method_index["index"][$method_name][$type_key];
    if (strstr($function, "::")) {
        list($class, $method) = explode("::", $function);
        // XXX deprecated syntax
        $ret = @call_user_method_array($method, $class, $params);
    } else {
        $ret = call_user_func_array($function, $params);
    }
    if (PEAR::isError($ret)) {
        $arr = (array)$ret;
        $arr['__PEAR_TYPE__'] = 'error';
        $arr['__PEAR_ERROR_CLASS__'] = get_class($ret);
        return $arr;
    }
/*
    ob_start();
    var_dump($ret);
    error_log("$method_name returned ".ob_get_contents());
    ob_end_clean();
*/
    return $ret;
}

// }}}
// {{{ pear_xmlrpc_introspection_callback()

function pear_xmlrpc_introspection_callback($userdata)
{
    parse_signatures_from_file("../include/pear-database.php", &$signatures,
                               "signatures");
    $ret = "<introspection version='1.0'>\n";
    $ret .= " <methodList>\n";
    foreach ($signatures as $sig) {
        $ret .= "  <methodDescription name='$sig[method_name]'>\n";
        $ret .= "   <author/>\n";
        $ret .= "   <purpose/>\n";
        $ret .= "   <signatures>\n";
	foreach ($sig["param_types"] as $params) {
            $ret .= "    <signature>\n";
            $ret .= "     <params>\n";
            $paramlist = explode(",", $params);
            foreach ($paramlist as $param) {
                $ret .= "      <value type='$param'/>\n";
            }
            $ret .= "     </params>\n";
            $ret .= "     <returns>\n";
            $ret .= "      <value type='$sig[return_type]'/>\n";
            $ret .= "     </returns>\n";
            $ret .= "    </signature>\n";
	}
        $ret .= "   </signatures>\n";
        $ret .= "   <see/>\n";
        $ret .= "   <examples/>\n";
        $ret .= "   <errors/>\n";
        $ret .= "   <notes/>\n";
        $ret .= "   <bugs/>\n";
        $ret .= "   <todo/>\n";
        $ret .= "  </methodDescription>\n";
    }
    $ret .= " </methodList>\n";
    $ret .= "</introspection>\n";
    return $ret;
}

// }}}
?>
