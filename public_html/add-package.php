<?php

$t1 = microtime();

include_once "html_form.php";

$t2 = microtime();

pageHeader("PEAR: Add a package");

$domains = $dbh->getAssoc("SELECT domain,domain FROM domains");
$domains[""] = "[root]";

$form = new HTML_Form($PHP_SELF, "POST");
$form->addText("name", "Name", "");
$form->addHidden("hidden1", "foo");
$form->addHidden("hidden2", "bar");
$form->addSelect("domain", "Domain", &$domains, '', 1, 'Select Domain');
$form->addSubmit();
$form->display();

pageFooter();

$t3 = microtime();

print "t1=$t1<br>\n";
print "t2=$t2 (".((double)$t2-(double)$t1).")<br>\n";
print "t3=$t3 (".((double)$t3-(double)$t2).")<br>\n";

?>
