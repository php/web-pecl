<?php

require_once "HTML/Form.php";

response_header("PEAR: Add a package");

$domains = $dbh->getAssoc("SELECT domain,domain FROM domains");
$domains[""] = "[root]";

$form = new HTML_Form($PHP_SELF, "POST");
$form->addText("name", "Name", "");
$form->addHidden("hidden1", "foo");
$form->addHidden("hidden2", "bar");
$form->addSelect("domain", "Domain", &$domains, '', 1, 'Select Domain');
$form->addSubmit();
$form->display();

response_footer();

?>
