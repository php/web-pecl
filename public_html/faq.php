<!-- $Id$ -->
<?php
response_header("FAQ");

echo "<h1>PEAR Frequently Asked Questions</h1>";

$ID = 0;
$TOC = array();
$content = "";

function makeTOC($toc)
{
    $content = "<h3>Table of contents</h3>";
    if (is_array($toc)) {
        foreach ($toc as $key => $value) {
            $content .= "<a href=\"faq.php#faq-".$key."\">".$value."</a><br/>";
        }   
    }
    
    return $content;
        
}

function startElement($parser, $elementName, $elementAttributes)
{

    $content = "";

    switch (strtolower($elementName)) {

        /**
         * Tag defining the table structure
         */
        case "qandaset" :
            $content .= "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
            break;

        case "question" :
            $content .= "<tr><td bgcolor=\"#cccccc\"><a name=\"faq-".$GLOBALS['ID']."\"><h3>";
            $content .= $elementAttributes['TITLE'];
            $GLOBALS['TOC'][$GLOBALS['ID']] = $elementAttributes['TITLE'];
            $GLOBALS['ID']++;
            break;

        case "answer" :
            $content .= "<tr><td bgcolor=\"#eeeeee\">";
            break;

        /**
         * Tags defining the appearance of lists
         */            
        case "simplelist" :
            $content .= "<ul>";
            break;

        case "member" :
            $content .= "<li>";
            break;

        /**
         * Appearance
         */
        case "para" :
            $content .= "<p>";
            break;

        case "artheader" :
        case "command" :
            $content .= "<pre>";
            break;
        
        case "emphasis" :
            $content .= "<em>";
            break;

        case "ulink" :
            $content .= "<a href=\"".$elementAttributes['URL']."\">";
            
            if (isset($elementAttributes['NAME'])) {
                $content .=$elementAttributes['NAME'];
            }            
            break;

        case "faqlink" :
            $content .= "<a href=\"faq.php#faq-".$elementAttributes['FAQ']."\">";
            break;

        case "author" :
            if ($elementAttributes['NAME'] != "") {
                $content .= "<b>Answer written by ".$elementAttributes['NAME'].".";
            } else {
                $content .= "<b>";
            }
            break;            

        case "break" :
            $content .= "<br/>";
            break;
        
        case "filename" :
            $content .= "<code>";
            break;
    }
    
    $GLOBALS['content'] .= $content;

}

function endElement($parser, $elementName)
{

    $content = "";

    switch (strtolower($elementName)) {

        /**
         * Tags defining the table structure
         */        
        case "qandaset" :
            $content .= "</table>";
            break;

        case "question" :
            $content .= "</h3></td></tr>\n";
            break;
            
        case "answer" :
            $content .= "</td></tr>\n";
            break;

        /**
         * Tags defining the appearance of lists
         */
        case "simplelist" :
            $content .= "</ul>";
            break;

        case "member" :
            $content .= "</li>";
            break;

        case "para" :
            $content .= "</p>";
            break;
        
        case "artheader" :
        case "command" :
            $content .= "</pre>";
            break;

        case "emphasis" :
            $content .= "</em>";
            break;
        
        case "ulink" :
        case "faqlink" :
            $content .= "</a>";
            break;
        
        case "author" :
            $content .= "</b>";
            break;

        case "filename" :
            $content .= "</code>";
            break;

    }
    
    $GLOBALS['content'] .= $content;

}

function characterData($parser, $data)
{
    $GLOBALS['content'] .= htmlspecialchars($data);
}

$filename = "../doc/pearfaq.xml";

$fp = fopen($filename, "r") or die("error opening pearfaq.xml");

$parser = xml_parser_create();

xml_set_element_handler($parser, "startElement", "endElement");
xml_set_character_data_handler($parser, "characterData");

while ($data = fread($fp, 4096)) {
    xml_parse($parser, $data, feof($fp));    
}

xml_parser_free($parser);
fclose($fp);

echo makeTOC($TOC);

echo $content;

response_footer();
?>
