<?php

	function pre_dump($var) {
	    print "<pre>";
	    var_dump($var);
	    print "</pre>";
	}

	// Print Dump & Die
	function pdd($var){
		echo '<pre>';
		print_r($var);
		echo '</pre>';
		exit;
	}

	// Var Dump & Die
	function vdd($var){
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
		exit;
	}
?>