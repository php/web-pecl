<?php
auth_require(true);

require_once "HTML/Form.php";

response_header("Edit author");

if (isset($HTTP_GET_VARS['handle'])) {
    $handle = $HTTP_GET_VARS['handle'];
} else {
    $handle = "";
}

if (empty($handle) && !isset($HTTP_POST_VARS['command'])) {
    PEAR::raiseError("No valid handle found!");
}

if (!isset($HTTP_POST_VARS['command'])) {
    $HTTP_POST_VARS['command'] = "display";
}
    
switch ($HTTP_POST_VARS['command']) {
    
    case "update" : {

        $query = sprintf("UPDATE users SET name = '%s', email = '%s', homepage = '%s', userinfo = '%s', showemail = '%s', admin = '%s'",
                         $HTTP_POST_VARS['name'],
                         $HTTP_POST_VARS['email'],
                         $HTTP_POST_VARS['homepage'],
                         $HTTP_POST_VARS['userinfo'],
                         isset($HTTP_POST_VARS['showemail']) ? 1 : 0,
                         isset($HTTP_POST_VARS['admin']) ? 1 : 0);

        $query .= " WHERE handle = '" . $HTTP_POST_VARS['handle'] . "'";

        $sth = $dbh->query($query);
        
        echo "The update has been executed successfully.";
        
        $handle = $HTTP_POST_VARS['handle'];
        
        response_footer();
        return;
    }
    
    default : {
        $dbh->setFetchmode(DB_FETCHMODE_ASSOC);
        $row = $dbh->getRow("SELECT * FROM users WHERE handle = '" . $handle . "'");
        if ($row === null) {
            PEAR::raiseError("No author information found!");
        }
        
        
        print "<FORM action=\"" . $HTTP_SERVER_VARS['PHP_SELF'] . "\" method=\"post\">\n";
        print "<INPUT TYPE=\"hidden\" name=\"command\" value=\"update\">\n";
        print "<INPUT TYPE=\"hidden\" name=\"handle\" value=\"" . $HTTP_GET_VARS['handle'] . "\">\n";
        
        print "<H1>Editing author \"".$handle."\"</H1>\n";
        print "<P>\n";
        
        print "<TABLE BORDER=\"0\" CELLSPACING=\"1\" CELLPADDING=\"5\">\n";
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">Handle:</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">".$handle."</TD>\n";
        print " </TR>\n";
        
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">Name:</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">";
        HTML_Form::displayText("name", $row['name']);
        print "  </TD>\n";
        print " </TR>\n";
        
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">EMail:</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">";
        HTML_Form::displayText("email", $row['email']);
        print "  </TD>\n";
        print " </TR>\n";
        
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">Homepage:</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">";
        HTML_Form::displayText("homepage", $row['homepage']);
        print "   </TD>\n";
        print " </TR>\n";
        
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">Additional user information:</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">";
        HTML_Form::displayTextarea("userinfo", $row['userinfo']);
        print "   </TD>\n";
        print " </TR>\n";
        
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">Show EMail adress:</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">";
        HTML_Form::displayCheckbox("showemail", $row['showemail']);
        print "   </TD>\n";
        print " </TR>\n";
        
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">Administrator:</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\">";
        HTML_Form::displayCheckbox("admin", $row['admin']);
        print "   </TD>\n";
        print " </TR>\n";
        
        print " <TR>\n";
        print "  <TH BGCOLOR=\"#CCCCCC\">&nbsp;</TH>\n";
        print "  <TD BGCOLOR=\"#e8e8e8\"><INPUT TYPE=\"submit\">&nbsp;<INPUT TYPE=\"reset\"></TD>\n";
        print " </TR>\n";
        
        print "</TABLE>\n";
        
        print "</FORM>\n";
        
        response_footer();

    }            
}
?>
