<?php // -*- C++ -*-

$pear_xmlrpc_methods = array();

// {{{ xmlrpc method: package.new

$pear_xmlrpc_methods[] = "package.new";

function pear_xmlrpc_package_add($name, $params, $appdata)
{
    $ret = package::add($params[0]);
    if (PEAR::isError($ret)) {
        return false;
    }
    return true;
}

// }}}

// {{{ xmlrpc method: package.info

$pear_xmlrpc_methods[] = "package.info";

function pear_xmlrpc_package_info($name, $params, $appdata)
{
    global $dbh;
    return pacakge::info($params[0]);
}

// }}}

// {{{ xmlrpc method: package.list

$pear_xmlrpc_methods[] = "package.list";

function pear_xmlrpc_package_list($name, $params, $appdata)
{
    global $dbh;
    return $dbh->getAssoc("SELECT p.id AS packageid, p.name AS name, ".
                          "c.id AS categoryid, c.name AS category, ".
                          "p.stablerelease AS stable, p.license AS license, ".
                          "p.summary AS summary, p.description AS description".
                          " FROM packages p, categories c ".
                          "WHERE c.id = p.category ".
                          "ORDER BY p.name");
}

// }}}

?>
