<?php

pageHeader("PEAR: Add an author");

?>
<H1>Add an author</H1>
<FORM ACTION="<?php echo $PHP_SELF; ?>" METHOD="POST">
<TABLE>
<?php

formInputRow("Handle", "handle", '', 10);
formInputRow("Name", "name", '', 40);
formInputRow("Email", "email", '', 40);
formInputRow("Homepage", "homepage", '', 40);
formCheckboxRow("Show email?", "homepage", '', 40);

print " <TR>\n";
print "  <TH ALIGN=\"right\">Credentials</TH>\n";
print "  <TD>\n   ";
formCheckbox('cred_upload', true);
print " contributor<BR>\n   ";
formCheckbox('cred_admin');
print " administrator<BR>\n";
print "  </TD>\n";
print " </TR>\n";

formSubmitRow();

?></TABLE>
</FORM>
<?php

pageFooter();

?>
