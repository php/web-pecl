<?php

// $Id$ 

response_header("Frequently Asked Questions");

echo "<h1>PEAR Frequently Asked Questions</h1>";

$tagmap = array(
    "variablelist"           => "dl",
    "varlistentry,term"      => "dt",
    "varlistentry,listitem"  => "dd",
    "simplelist"             => "ul",
    "simplelist,member"      => "li",
    "para"                   => "p",
    "artheader"              => "pre",
    "command"                => "pre",
    "emphasis"               => "i",
    "ulink"                  => "a",
    "faqlink"                => "a",
    "author"                 => "b",
    "break"                  => "br",
    "filename"               => "tt"
);

$ID = 1;
$TOC = array();
$content = "";
$elemstack = array();

function makeTOC(&$toc)
{
    $ret = "<h3>Table of contents</h3>";
    if (is_array($toc)) {
        foreach ($toc as $key => $value) {
            $ret .= "\n{$key}. <a href=\"faq.php#faq-{$key}\">{$value}</a><br />";
        }   
    }
    
    return $ret;
        
}

function startElement($parser, $elementName, $attribs)
{

    global $ID, $TOC, $content, $elemstack, $tagmap;

    $key1 = strtolower($elementName);
    array_push($elemstack, $key1);
    $parent = @$elemstack[sizeof($elemstack) - 2];
    $key2 = "{$parent},{$key1}";

    switch ($key1) {
        /**
         * Tag defining the table structure
         */
        case "qandaset" : {
            $content .= "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	        return;
	    }

        case "question" : {
            $content .= "<tr><td bgcolor=\"#cccccc\"><a name=\"faq-{$ID}\" /><font size=\"+1\">";
            $content .= $attribs['TITLE'];
	        $TOC[$ID++] = $attribs['TITLE'];
	        return;
	    }

        case "answer" : {
            $content .= "<tr><td bgcolor=\"#eeeeee\">";
	        return;
	    }

        case "ulink" : {
            $content .= "<a href=\"".$attribs['URL']."\">";
            if (!empty($attribs['NAME'])) {
                $content .= $attribs['NAME'];
            }            
	        return;
	    }

        case "faqlink" : {
            $content .= "<a href=\"faq.php#faq-".$attribs['FAQ']."\">";
            return;
	    }

        case "author" : {
	        $content .= "<{$tagmap[$key1]}>";
            if (!empty($attribs['NAME'])) {
                $content .= "Answer written by ". $attribs['NAME'];
            }
            return;
	    }
	    
	    case "quote" : {
	        $content .= "\"";
	        return;
	    }	    
    }
    
    if (isset($tagmap[$key2])) {
	    $content .= "<{$tagmap[$key2]}>";
    } elseif (isset($tagmap[$key1])) {
	    $content .= "<{$tagmap[$key1]}>";
    }
}

function endElement($parser, $elementName)
{
    global $content, $elemstack, $tagmap;

    $parent = @$elemstack[sizeof($elemstack) - 2];
    array_pop($elemstack);

    $key1 = strtolower($elementName);
    $key2 = "{$parent},{$key1}";

    switch ($key1) {
        /**
         * Tags defining the table structure
         */        
        case "qandaset" : {
            $content .= "</table>";
	        return;
	    }

        case "question" : {
            $content .= "</font></td></tr>\n";
            return;
	    }

        case "answer" : {
            $content .= "<a href=\"#top\">" . make_image("arrow_top.gif") . " top</a><br /><br /></td></tr>\n";
            return;
	    }

	    case "quote" : {
	        $content .= "\"";
	        return;
	    }
    }

    if (isset($tagmap[$key2])) {
	    $content .= "</{$tagmap[$key2]}>";
    } elseif (isset($tagmap[$key1])) {
	    $content .= "</{$tagmap[$key1]}>";
    }
}

function characterData($parser, $data)
{
    $GLOBALS['content'] .= htmlspecialchars($data);
}

$filename = "../doc/pearfaq.xml";

$fp = @fopen($filename, "r");

if (!$fp) {
    PEAR::raiseError("error opening pearfaq.xml");
}

$parser = xml_parser_create();

xml_set_element_handler($parser, "startElement", "endElement");
xml_set_character_data_handler($parser, "characterData");

while ($data = fread($fp, 4096)) {
    if (!xml_parse($parser, $data, feof($fp))) {
	    $code = xml_get_error_code($parser);
	    $msg = "While parsing pearfaq.xml on line " .
	        xml_get_current_line_number($parser) . ": " .
	        xml_error_string($code);
	    PEAR::raiseError($msg, $code);
    }
}

xml_parser_free($parser);
fclose($fp);

echo makeTOC($TOC);

echo $content;

response_footer();

?>
