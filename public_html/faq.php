<?php
response_header("FAQ");

echo "<h1>PEAR Frequently Asked Questions</h1>";

$ID = 1;

function startElement($parser, $elementName, $elementAttributes)
{

    switch (strtolower($elementName)) {

        /**
         * Tag defining the table structure
         */
        case "qandaset" :
            echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
            break;

        case "question" :
            echo "<tr><td bgcolor=\"#cccccc\"><a name=\"faq-".$GLOBALS['ID']++."\"><h3>";
            break;

        case "answer" :
            echo "<tr><td bgcolor=\"#eeeeee\">";
            break;

        /**
         * Tags defining the appearance of lists
         */            
        case "simplelist" :
            echo "<ul>";
            break;

        case "member" :
            echo "<li>";
            break;

        /**
         * Appearance
         */
        case "para" :
            echo "<p>";
            break;

        case "artheader" :
        case "command" :
            echo "<pre>";
            break;
        
        case "emphasis" :
            echo "<em>";
            break;

        case "ulink" :
            echo "<a href=\"".$elementAttributes['URL']."\">";
            
            if (isset($elementAttributes['NAME'])) {
                echo $elementAttributes['NAME'];
            }            
            break;

        case "faqlink" :
            echo "<a href=\"faq.php#faq-".$elementAttributes['FAQ']."\">";
            break;

        case "author" :
            if ($elementAttributes['NAME'] != "") {
                echo "<b>Answer written by ".$elementAttributes['NAME'].".";
            } else {
                echo "<b>";
            }
            break;            

        case "break" :
            echo "<br/>";
            break;
    }

}

function endElement($parser, $elementName)
{

    switch (strtolower($elementName)) {

        /**
         * Tags defining the table structure
         */        
        case "qandaset" :
            echo "</table>";
            break;

        case "question" :
            echo "</h3></td></tr>\n";
            break;
            
        case "answer" :
            echo "</td></tr>\n";
            break;

        /**
         * Tags defining the appearance of lists
         */
        case "simplelist" :
            echo "</ul>";
            break;

        case "member" :
            echo "</li>";
            break;

        case "para" :
            echo "</p>";
            break;
        
        case "artheader" :
        case "command" :
            echo "</pre>";
            break;

        case "emphasis" :
            echo "</em>";
            break;
        
        case "ulink" :
        case "faqlink" :
            echo "</a>";
            break;
        
        case "author" :
            echo "</b>";
            break;
    }


}

function characterData($parser, $data)
{
    echo $data;
}

if (getenv("SERVER_NAME") != "pear.php.net") {
    $filename = "/var/www/pearweb/doc/pearfaq.xml";
} else {
    $filename = "/usr/local/www/pearweb/doc/pearfaq.xml";
}

$fp = fopen($filename, "r") or die("error opening pearfaq.xml");

$parser = xml_parser_create();

xml_set_element_handler($parser, "startElement", "endElement");
xml_set_character_data_handler($parser, "characterData");

while ($data = fread($fp, 4096)) {
    xml_parse($parser, $data, feof($fp));    
}

xml_parser_free($parser);
fclose($fp);

response_footer();
?>
