<?php

define('HTML_FORM_MAX_FILE_SIZE', 16 * 1024 * 1024); // 16 MB
require_once "HTML/Form.php";

$display_form = true;
$display_verification = false;
$jumpto = false;

do {
	if (isset($upload)) {
		$display_form = false;
		$display_verification = true;
		$tmpfile = $_FILES['distfile']['tmp_name'];
		$tmpsize = $_FILES['distfile']['size'];
	}
	if (isset($verify)) {
		$ok = release::upload($package, $version, $state, $relnotes,
		                      $distfile, md5_file($distfile));
		if (PEAR::isError($ok)) {
			display_error("Error while uploading package: ".$ok->getMessage());
			break;
		}
		response_header("Release Upload Finished");
		print "The release of package `$package' version `$version' ";
		print "was completed successfully.";
	}
} while (false);

if ($display_form) {
    $title = "Upload New Release";
    response_header($title);

    print "<h1>$title</h1>

Upload a new package distribution file built using `<code>pear
package</code>' here.  The information from your package.xml file will
be displayed on the next screen for verification.

<p />

Uploading new releases is restricted to each package's lead developer(s).

<p />

";

    $packages = $dbh->getAssoc("SELECT packages.id AS id, ".
							   "packages.name AS name ".
							   "FROM packages, maintains ".
							   "WHERE maintains.handle = ? ".
							   "AND maintains.role = 'lead' ".
							   "AND maintains.package = packages.id ".
							   "ORDER BY name",
							   false, array($PHP_AUTH_USER));

	if (empty($packages)) {
		display_error("You are not registered as lead developer for any packages.");
	}

    if (isset($errorMsg)) {
		print "<table>\n";
		print " <tr>\n";
		print "  <td>&nbsp;</td>\n";
		print "  <td><b>$errorMsg</b></td>\n";
		print " </tr>\n";
		print "</table>\n";
    }

    $form =& new HTML_Form($PHP_SELF, "POST");
	$form->addFile("distfile", "Distribution file");
    $form->addSubmit("upload", "Upload!");
    $form->display();

    if ($jumpto) {
		print "\n<script language=\"JavaScript\">\n<!--\n";
		print "document.forms[1].$jumpto.focus();\n";
		print "// -->\n</script>\n";
    }
}

if ($display_verification) {
	include_once "PEAR/Common.php";
	response_header("Upload New Release: Verify");
	$util =& new PEAR_Common;
	$info = $util->infoFromTgzFile($tmpfile, false);
	print "<pre>"; var_dump($info); print "</pre>";
}

response_footer();

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#cc0000\" size=\"+1\">$msg</font><br />\n";
}

?>