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
   | Authors: Stig Sæther Bakken <ssb@fast.no>                            |
   |                                                                      |
   +----------------------------------------------------------------------+
 */

// {{{ parse_signatures_from_file()

/*
Legend for needed perms to execute methods:

+         => valid user
*         => admin
<nothing> => all

*/
function parse_signatures_from_file($file, &$signatures, $out_format = "signatures")
{
    $cache_file = PEAR_TMPDIR . '/' . basename($file) . '.' . $out_format;
    if (@filemtime($cache_file) > @filemtime($file)) {
        $signatures = unserialize(file_get_contents($cache_file));
        return true;
    }
    $fp = fopen($file, "r");
    if (!is_resource($fp)) {
        return false;
    }
    $contents = fread($fp, filesize($file));
    fclose($fp);
    // format: array( array(returntype, methodname, paramlist), ... )
    settype($signatures, "array");
    $matches = array();
    if (preg_match_all('/([ \*\+])?proto\s+([a-z|]+)\s+([a-zA-Z0-9_:]+)\s*\(([^\)]*)\)\s*?\n(\s*?\/\/\s+?(.*?)\s*?\n)?/s',
                       $contents, $matches)) {
        for ($i = 0; $i < sizeof($matches[0]); $i++) {
            $auth_type   =  $matches[1][$i];
            $return_type =  $matches[2][$i];
            $method_name =  $matches[3][$i];
            $parameters  =  $matches[4][$i];
            //$purpose     = @$matches[6][$i]; // XXX unfinished
            $return_type_permutations = explode("|", $return_type);
            $xmlrpc_method = str_replace("::", ".", $method_name);
            $param_list = preg_split('/\s*,\s*/', $parameters);
            $first_optional_param = -1;
//            error_log("found method $xmlrpc_method");
            for ($j = 0; $j < sizeof($param_list); $j++) {
                if ($first_optional_param == -1 && strstr($param_list[$j], "[")) {
                    $first_optional_param = $j;
                }
                $param_list[$j] = preg_replace('/[\[\]]/', '', $param_list[$j]);
            }
            if ($first_optional_param == -1) {
                $numparams = array(sizeof($param_list));
            } else {
                $numparams = array();
                for ($j = $first_optional_param; $j <= sizeof($param_list); $j++) {
                    $numparams[] = $j;
                }
            }
            $param_variations = array();
            for ($j = 0; $j < sizeof($param_list); $j++) {
                $param_variations[$j] = explode("|", $param_list[$j]);
            }
            $paramlist_permutations = array();
            foreach ($numparams as $nump) {
                $v = 1;
                $params = array();
                $varyat = array();
                for ($j = 0; $j < $nump; $j++) {
                    $v *= sizeof($param_variations[$j]);
                    if (sizeof($param_variations[$j]) > 1) {
                        $varyat[] = $j;
                    }
                    $params[$j] = $param_variations[$j][0];
                }
                if (sizeof($varyat) > 0) {
                    _signature_worker($paramlist_permutations, $params, $varyat, $param_variations);
                } else {
                    $paramlist_permutations[implode(",", $params)] = 1;
                }
            }
            if ($out_format == "full_description") {
                // XXX this format is still buggy
                foreach ($return_type_permutations as $ret_type) {
                    foreach ($paramlist_permutations as $paramlist => $dummy) {
                        foreach (explode(",", $paramlist) as $ind => $pp) {
                            $plist[$ind] = array("type" => $pp);
                        }
                        $signatures[] = array(
                            "xmlrpc_method" => $xmlrpc_method,
                            "implemented_in" => $method_name,
                            "return_type" => $ret_type,
                            "parameters" => $plist);
                    }
                }
            } elseif ($out_format == "index") {
                foreach ($paramlist_permutations as $paramlist => $dummy) {
                    $signatures["index"][$xmlrpc_method][$paramlist] = $method_name;
                    if ($auth_type == "*") {
                        $signatures["auth"][$xmlrpc_method] = "admin";
                    } elseif ($auth_type == "+") {
                        $signatures["auth"][$xmlrpc_method] = "user";
                    } else {
                        $signatures["auth"][$xmlrpc_method] = "all";
                    }
                }









            } else {
                foreach ($return_type_permutations as $ret_type) {
                    $signatures[] = array(
                        "method_name" => $xmlrpc_method,
                        "return_type" => $ret_type,
                        "param_types" => array_keys($paramlist_permutations),
                    );
                }
            }
        }
    }
    $mode = LOCK_EX;
    $lock_fp = false;
    if (!eregi('Windows 9', php_uname())) {
        $lock_fp = @fopen(PEAR_TMPDIR . '/.siglock', 'w');

        if (!is_resource($lock_fp)) {
            return true;
        }
        if (!(int)flock($lock_fp, $mode)) {
            fclose($lock_fp);
            return true;
        }
    }
    if ($wp = @fopen($cache_file, "w")) {
        fwrite($wp, serialize($signatures));
        fclose($wp);
    }
    $mode = LOCK_UN;
    if (!eregi('Windows 9', php_uname())) {

        if (!is_resource($lock_fp)) {
            return true;
        }
        if (!(int)flock($lock_fp, $mode)) {
            fclose($lock_fp);
            return true;
        }
        fclose($lock_fp);
    }
    return true;
}

// }}}
// {{{ _signature_worker()

function _signature_worker(&$storage, $params, &$varyat, &$param_variations, $j = 0)
{
    $n = $varyat[$j];
    $variations = $param_variations[$n];
    for ($k = 0; $k < sizeof($variations); $k++) {
        $params[$n] = $variations[$k];
        $storage[implode(",", $params)] = 1;
        if (($j+1) < sizeof($varyat)) {
            _signature_worker($storage, $params, $varyat, $param_variations, $j+1);
        }
    }
}

// }}}

?>
