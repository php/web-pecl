<?php // -*- C++ -*-
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
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

// {{{ constants

if (!defined("HTML_FORM_TEXT_SIZE")) {
    define("HTML_FORM_TEXT_SIZE", 20);
}
if (!defined("HTML_FORM_PASSWD_SIZE")) {
    define("HTML_FORM_PASSWD_SIZE", 10);
}
if (!defined("HTML_FORM_TEXTAREA_HT")) {
    define("HTML_FORM_TEXTAREA_HT", 5);
}
if (!defined("HTML_FORM_TEXTAREA_WT")) {
    define("HTML_FORM_TEXTAREA_WT", HTML_FORM_TEXT_SIZE);
}

// }}}

class HTML_Form {
    // {{{ properties

    /** ACTION attribute of <FORM> tag */
    var $action;

    /** METHOD attribute of <FORM> tag */
    var $method;

    /** NAME attribute of <FORM> tag */
    var $name;

    /** an array of entries for this form */
    var $fields;

    /** DB_storage object, if tied to one */
    var $storageObject;

    // }}}

    // {{{ constructor

    function HTML_Form($action, $method = 'GET', $name = '') {
	$this->action = $action;
	$this->method = $method;
	$this->name = $name;
	$this->fields = array();
    }

    // }}}

    // {{{ addText()

    function addText($name, $title, $default, $size = HTML_FORM_TEXT_SIZE) {
	$this->fields[] = array("text", $name, $title, $default, $size);
    }

    // }}}
    // {{{ addPassword()

    function addPassword($name, $title, $default, $size = HTML_FORM_PASSWD_SIZE) {
	$this->fields[] = array("password", $name, $title, $default, $size);
    }

    // }}}
    // {{{ addCheckbox()

    function addCheckbox($name, $title, $default) {
	$this->fields[] = array("checkbox", $name, $title, $default);
    }

    // }}}
    // {{{ addTextarea()

    function addTextarea($name, $title, $default,
			 $width = HTML_FORM_TEXTAREA_WT,
			 $height = HTML_FORM_TEXTAREA_HT) {
	$this->fields[] = array("textarea", $name, $title, &$default, $width, $height);
    }

    // }}}
    // {{{ addSubmit

    function addSubmit($name = "submit", $title = "Submit Changes") {
	$this->fields[] = array("submit", $name, $title);
    }

    // }}}
    // {{{ addReset()

    function addReset($title = "Discard Changes") {
	$this->fields[] = array("reset", $title);
    }

    // }}}
    // {{{ addSelect()

    function addSelect($name, $title, $entries, $default = '', $size = 1,
		       $blank = '', $multiple = false) {
	$this->fields[] = array("select", $name, $title, &$entries, $default, $size,
				$blank, $multiple);
    }

    // }}}
    // {{{ addRadio()

    function addRadio($name, $title, $value, $default) {
	$this->fields[] = array("radio", $name, $title, $value, $default);
    }

    // }}}
    // {{{ addImage()

    function addImage($name, $src) {
	$this->fields[] = array("image", $name, $src);
    }

    // }}}
    // {{{ addHidden()

    function addHidden($name, $value) {
	$this->fields[] = array("hidden", $name, $value);
    }

    // }}}

    // {{{ start()

    function start() {
	print "<FORM ACTION=\"$this->action\" METHOD=\"$this->method\"";
	if ($this->name) {
	    print " NAME=\"$this->name\"";
	}
	print ">";
    }

    // }}}
    // {{{ end()

    function end() {
	$fields = array();
	reset($this->fields);
	while (list($i, $data) = each($this->fields)) {
	    if ($data[0] == 'reset') {
		continue;
	    }
	    $fields[$data[1]] = true;
	}
	$this->displayHidden("_fields", implode(":", array_keys($fields)));
	print "</FORM>";
    }

    // }}}

    // {{{ displayText()

    function displayText($name, $default = '', $size = HTML_FORM_TEXT_SIZE) {
	print "<INPUT NAME=\"$name\" VALUE=\"$default\" SIZE=\"$size\">";
    }

    // }}}
    // {{{ displayTextRow()

    function displayTextRow($name, $title, $default = '', $size = HTML_FORM_TEXT_SIZE) {
	print " <TR>\n";
	print "  <TH ALIGN=\"right\">$title</TH>";
	print "  <TD>";
	$this->displayText($name, $default, $size);
	print "</TD>\n";
	print " </TR>\n";
    }

    // }}}
    // {{{ displayPassword()

    function displayPassword($name, $default = '', $size = HTML_FORM_PASSWD_SIZE) {
	print "<INPUT NAME=\"$name\" TYPE=\"password\" VALUE=\"$default\" SIZE=\"$size\">";
    }

    // }}}
    // {{{ displayPasswordRow()

    function displayPasswordRow($name, $title, $default = '', $size = HTML_FORM_PASSWD_SIZE) {
	print "<TR>\n";
	print "  <TH ALIGN=\"right\">$title</TH>\n";
	print "  <TD>";
	$this->displayPassword($name, $default, $size);
	print " repeat: ";
	$this->displayPassword($name."2", $default, $size);
	print "</TD>\n";
	print "</TR>\n";
    }

    // }}}
    // {{{ displayCheckbox()

    function displayCheckbox($name, $default = false) {
	print "<INPUT TYPE=\"checkbox\" NAME=\"$name\"";
	if ($default && $default != 'off') {
	    print " CHECKED";
	}
	print ">";
    }

    // }}}
    // {{{ displayCheckboxRow()

    function displayCheckboxRow($name, $title, $default = false) {
	print " <TR>\n";
	print "  <TH ALIGN=\"right\">$title</TH>";
	print "  <TD>";
	$this->displayCheckbox($name, $default);
	print "</TD>\n";
	print " </TR>\n";
    }

    // }}}
    // {{{ displayTextarea()

    function displayTextarea($name, $default = '', $width = 40, $height = 5) {
	print "<TEXTAREA NAME=\"$name\" COLS=\"$width\" ROWS=\"$height\">";
	print $default;
	print "</TEXTAREA>";
    }

    // }}}
    // {{{ displayTextareaRow()

    function displayTextareaRow($name, $title, $default = '', $width = 40, $height = 5) {
	print " <TR>\n";
	print "  <TH ALIGN=\"right\">$title</TH>\n";
	print "  <TD>";
	$this->displayTextarea($name, &$default, $width, $height);
	print "</TD>\n";
	print " </TR>\n";
    }

    // }}}
    // {{{ displaySubmit()

    function displaySubmit($title = 'Submit Changes', $name = "submit") {
	print "<INPUT NAME=\"$name\" TYPE=\"submit\" VALUE=\"$title\">";
    }

    // }}}
    // {{{ displaySubmitRow()

    function displaySubmitRow($name = "submit", $title = 'Submit Changes') {
	print " <TR>\n";
	print "  <TD>&nbsp</TD>\n";
	print "  <TD>";
	$this->displaySubmit($title, $name);
	print "</TD>\n";
	print " </TR>\n";
    }

    // }}}
    // {{{ displayHidden()

    function displayHidden($name, $value) {
	print "<INPUT TYPE=\"hidden\" NAME=\"$name\" VALUE=\"$value\">";
    }

    // }}}
    // {{{ displaySelect()

    function displaySelect($name, $entries, $default = '', $size = 1,
			   $blank = '', $multiple = false) {
	print "   <SELECT NAME=\"$name\"";
	if ($size) {
	    print " SIZE=\"$size\"";
	}
	if ($multiple) {
	    print " MULTIPLE";
	}
	print ">\n";
	if ($blank) {
	    print "    <OPTION VALUE=\"\">$blank\n";
	}
	while (list($val, $text) = each($entries)) {
	    print '    <OPTION ';
	if ($default && $default == $val) {
	    print 'SELECTED ';
	}
	print "VALUE=\"$val\">$text\n";
    }
    print "   </SELECT>\n";

    }

    // }}}
    // {{{ displaySelectRow()

    function displaySelectRow($name, $title, &$entries, $default = '', $size = 1,
			      $blank = '', $multiple = false)
    {
	print " <TR>\n";
	print "  <TH ALIGN=\"right\">$title:</TH>\n";
	print "  <TD>\n";
	$this->displaySelect($name, &$entries, $default, $size, $blank, $multiple);
	print "  </TD>\n";
	print " </TR>\n";
    }

    // }}}

    // XXX missing: displayRadio displayRadioRow displayReset

    // {{{ display()

    function display() {
	$this->start();
	print "<TABLE>\n";
	reset($this->fields);
	$hidden = array();
	$call_cache = array();
	while (list($i, $data) = each($this->fields)) {
	    switch ($data[0]) {
		case "hidden":
		    $hidden[] = $i;
		    continue 2;
		case "reset":
		    $params = 1;
		    break;
		case "submit":
		case "image":
		    $params = 2;
		    break;
		case "checkbox":
		    $params = 3;
		    break;
		case "text":
		case "password":
		case "radio":
		    $params = 4;
		    break;
		case "textarea":
		    $params = 5;
		    break;
		case "select":
		    $params = 7;
		    break;
		default:
		    // unknown field type
		    continue 2;
	    }
	    $str = $call_cache[$params];
	    if (!$str) {
		$str = '$this->display'.ucfirst($data[0])."Row(";
		for ($i = 1; $i <= $params; $i++) {
		    $str .= '$data['.$i.']';
		    if ($i < $params) $str .= ', ';
		}
		$str .= ');';
		$call_cache[$params] = $str;
	    }
	    eval($str);
	}
	print "</TABLE>\n";
	for ($i = 0; $i < sizeof($hidden); $i++) {
	    $this->displayHidden($this->fields[$hidden[$i]][1],
				 $this->fields[$hidden[$i]][2]);
	}
	$this->end();
    }

    // }}}
}

?>
