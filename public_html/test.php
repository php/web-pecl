<?php

require_once "signatures.php";

parse_signatures_from_file("../include/pear-database.php", &$signatures,
						   "index");

pre_dump($signatures);

exit;
print "<pre>";
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
print htmlspecialchars($ret);
print "</pre>\n";

?>
