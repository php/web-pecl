<?php // -*- C++ -*-

require_once "signatures.php";

$xmlrpc_method_index = parse_signatures_from_file("../include/pear-database.php", "index");

function pear_register_xmlrpc_methods($xs)
{
    global $xmlrpc_method_index;
    foreach ($xmlrpc_method_index as $method => $foo) {
        error_log("registering $method");
        xmlrpc_server_register_method($xs, $method, "pear_xmlrpc_dispatcher");
    }
}

function pear_xmlrpc_dispatcher($method_name, $params, $appdata)
{
    global $xmlrpc_method_index;
    error_log("pear_xmlrpc_dispatcher: $method_name called");
    if (empty($xmlrpc_method_index[$method_name])) {
        error_log("unknown method: $method_name");
        return false; // XXX FAULT
    }
    $type_key = "";
    for ($i = 0; $i < sizeof($params); $i++) {
        if ($i > 0) {
            $type_key .= ",";
        }
        $type_key .= xmlrpc_get_type($params[$i]);
    }
    if (!isset($xmlrpc_method_index[$method_name][$type_key])) {
        error_log("no signature found for $method_name($type_key)");
        return false; // XXX FAULT
    }
    $function = $xmlrpc_method_index[$method_name][$type_key];
    if (strstr($function, "::")) {
        list($class, $method) = explode("::", $function);
        $ret = call_user_method_array($method, $class, $params);
    } else {
        $ret = call_user_func_array($function, $params);
    }
    ob_start();
    var_dump($ret);
    error_log("$method_name returned ".ob_get_contents());
    ob_end_clean();
    return $ret;
}

?>
