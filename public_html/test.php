<?php

require_once "signatures.php";

$signatures = parse_signatures_from_file("../include/pear-database.php", "index");
print "<pre>";
//var_dump($signatures);
print "</pre>\n";

class foo {
	function bar($str) {
		return "bing: $str";
	}
}

$params = array("gazonk");
$func = "foo";
var_dump(@call_user_method_array("bar", $func, $params));

?>
