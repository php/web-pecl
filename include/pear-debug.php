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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

/*
    The PEAR Team debug functions
    (this is too personal for having only one for all ;-)
*/
    /* Stig, Martin */
	function pre_dump($var) {
	    print "<pre>";
	    var_dump($var);
	    print "</pre>";
	}

    /* Richard */
	function pd($var)
	{
		dd($var, 'print_r');
	}

	function vd($var)
	{
		dd($var, 'var_dump');
	}
	
	function pdd($var)
	{
		dd($var, 'print_r', true);
	}
	
	function vdd($var)
	{
		dd($var, 'var_dump', true);
	}
	
	function dd($var, $function, $exit = false)
	{
		ob_start();
		$function($var);
		$output = ob_get_contents();
		ob_end_clean();

		echo '<pre>';
		echo htmlspecialchars($output);
		echo '</pre>';

		if ($exit) {
			exit;
		}
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