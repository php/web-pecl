<?php
# make_submit()
#  - make a submit button image
#
function make_submit($file, $alt=false, $align=false, $extras=false, $dir=false, $border=0) {
	if (!$dir) {
		$dir = '/gifs';
	}
	$return = make_image($file, $alt, $align, $extras, $dir, $border);
	if ($return != "<img>") {
		$return = '<input type="image"'.substr($return,4);
	} else {
		$return = '<input type="submit">';
	}
	return $return;
}

function make_email($email, $linktext=false) {
	return sprintf("<a href=\"mailto:%s\">%s</a>",
		$email,
		($linktext ? $linktext : $email)
	);
}

function print_email($email, $linktext=false) {
	echo make_email($email, $linktext);
}

function clean_note($text) {
	$text = htmlspecialchars($text);
	$fixes = array('<br>','<p>','</p>');
	foreach ($fixes as $f) {
		$text=str_replace(htmlspecialchars($f), $f, $text);
		$text=str_replace(htmlspecialchars(strtoupper($f)), $f, $text);
	}
	$text = "<tt>".nl2br($text)."</tt>";
	return $text;
}


function sect_to_file($string) {
        $string = strtolower($string);
        $string = str_replace(' ','-',$string);
        $string = str_replace('_','-',$string);
        $func = "function.$string.php";
        $chap = "ref.$string.php";
        $feat = "features.$string.php";
        $struct = "control-structures.$string.php";
        if(is_file($func)) return $func;
        else if(is_file($chap)) return $chap;
        else if(is_file($feat)) return $feat;
        else if(is_file($struct)) return $struct;
        else return "$string.php";
}
