<?php // -*- C++ -*-

$pear_xmlrpc_methods = array();

// {{{ xmlrpc method: package.new

$pear_xmlrpc_methods[] = "package.new";

function pear_xmlrpc_package_new($name, $params, $appdata)
{
    $ret = add_package($params[0]);
    if (PEAR::isError($ret)) {
        return false;
    }
    return true;
}

// }}}

// {{{ xmlrpc method: package.info

//$pear_xmlrpc_methods[] = "package.info";

function pear_xmlrpc_package_info($name, $params, $appdata)
{
    return false;
}

// }}}

?>
