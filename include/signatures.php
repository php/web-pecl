<?php

function parse_signatures_from_file($file, $out_format = "signatures")
{
	$fp = fopen($file, "r");
	if (!is_resource($fp)) {
		return false;
	}
	$contents = "";
	while (!feof($fp)) {
		$contents .= fread($fp, 10240);
	}
	// format: array( array(returntype, methodname, paramlist), ... )
	$signatures = array();
	if (preg_match_all('/API\s+([a-z|]+)\s+([a-zA-Z0-9_:]+)\s*\(([^\)]*)\)/',
					   $contents, $matches)) {
		for ($i = 0; $i < sizeof($matches[0]); $i++) {
			$return_type = $matches[1][$i];
			$method_name = $matches[2][$i];
			$parameters  = $matches[3][$i];
			//print "signature: $return_type <b>$method_name</b>($parameters)<br />\n";
			$return_type_permutations = explode("|", $return_type);
			$xmlrpc_method = str_replace("::", ".", $method_name);
			$param_list = preg_split('/\s*,\s*/', $parameters);
			$first_optional_param = -1;
			error_log("found method $xmlrpc_method");
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
				//print "$nump params, $v variations<br>\n";
				if (sizeof($varyat) > 0) {
					_signature_worker($paramlist_permutations, $params, $varyat, $param_variations);
				} else {
					$paramlist_permutations[implode(",", $params)] = 1;
				}
			}
			if ($out_format == "full_description") {
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
					$signatures[$xmlrpc_method][$paramlist] = $method_name;
				}
			} else {
				foreach ($return_type_permutations as $ret_type) {
					foreach ($paramlist_permutations as $paramlist => $dummy) {
						$signatures[] = array($xmlrpc_method, $ret_type, $paramlist);
					}
				}
			}
			//print "<br>\n";
		}
	}
	return $signatures;
}

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

?>