<?php
/*
    The PEAR Team debug functions
    (this is too personal for having only one for all ;-)
*/
    /* Stig */
	function pre_dump($var) {
	    print "<pre>";
	    var_dump($var);
	    print "</pre>";
	}

    /* Richard */
	// Print Dump & Die
	function pdd($var){
		echo '<pre>';
		print_r($var);
		echo '</pre>';
		exit;
	}

    /* Richard */
	// Var Dump & Die
	function vdd($var){
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
		exit;
	}

    /* Tomas */
    function printr($var, $exit = 0)
    {
        if ($var === false) {
            $str = 'FALSE';
        } elseif ($var === true) {
            $str = 'TRUE';
        } elseif ($var === null) {
            $str = 'NULL';
        } else {
            ob_start();
                print_r($var);
                $str = ob_get_contents();
            ob_end_clean();
            $str = htmlentities($str);
        }
        echo '<pre>'.$str.'</pre>';
        if ($exit) {
            exit;
        }
    }
?>