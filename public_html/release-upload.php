<?php

auth_require();

define('HTML_FORM_MAX_FILE_SIZE', 16 * 1024 * 1024); // 16 MB
require_once "HTML/Form.php";
require_once "HTML/Table.php";

$display_form = true;
$display_verification = false;
$jumpto = false;

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
do {
	if (isset($upload)) {
        include_once 'HTTP/Upload.php';
        $upload_obj = new HTTP_Upload('en');
        $file = $upload_obj->getFiles('distfile');
        if (PEAR::isError($file)) {
            display_error($file->getMessage()); break;
        }
        if ($file->isValid()) {
            $file->setName('uniq', 'pear-');
            $file->setValidExtensions('tgz', 'accept');
            $tmpfile = $file->moveTo(PEAR_UPLOAD_TMPDIR);
            if (PEAR::isError($tmpfile)) {
                    display_error($tmpfile->getMessage()); break;
            }
            $tmpsize = $file->getProp('size');
        } elseif ($file->isMissing()) {
            display_error("No file uploaded"); break;
        } elseif ($file->isError()) {
            display_error($file->errorMsg()); break;
        }
        $display_form = false;
        $display_verification = true;

	} elseif (isset($verify)) {
        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($distfile);

        include_once "PEAR/Common.php";
        $util =& new PEAR_Common;
        $info = $util->infoFromTgzFile($distfile);

        if (!@is_file($distfile)) {
            display_error("No verified file found"); break;
        }
		$ok = release::upload($info['package'], $info['version'], $info['release_state'],
		                      $info['release_notes'], $distfile, md5_file($distfile));
		@unlink($distfile);
		if (PEAR::isError($ok)) {
			display_error("Error while uploading package: ".$ok->getMessage());
			break;
		}
        release::promote($info, $distfile);
		response_header("Release Upload Finished");
		print "The release of package `" . $info['package'] . "' version `" . $info['version'] . "' ";
		print "was completed successfully and the marketing for it started.<br /><br />";
        $pacid = package::info($info['package'], 'id');
        print '<center>'.
              make_link("package-info.php?pacid=$pacid", 'Visit package home') .
              '</center>';
		$display_form = $display_verification = false;

	} elseif (isset($cancel)) {

        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($distfile);
        if (@is_file($distfile)) {
            @unlink($distfile);
        }
        header("Location: $PHP_SELF"); exit;
    }
} while (false);

PEAR::popErrorHandling();

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
    // XXX this will leave files in PEAR_UPLOAD_TMPDIR if users don't complete the
	// next screen.  Janitor cron job recommended!
	$info = $util->infoFromTgzFile(PEAR_UPLOAD_TMPDIR . "/$tmpfile");
	$form =& new HTML_Form($PHP_SELF, "POST");
    $form->addHidden('distfile', $tmpfile);
	$form->addSubmit('verify', 'Verify Release');
    $form->addSubmit('cancel', 'Cancel');

	// XXX ADD MASSIVE SANITY CHECKS HERE
	$bb = new BorderBox("Please verify that the following release ".
						"information is correct:");
	print "<table border=\"0\" cellpadding=\"3\" cellspacing=\"0\">\n";
	print "<tr><th align=\"right\">Package:</th><td>$info[package]</td></tr>\n";
	print "<tr><th align=\"right\">Version:</th><td>$info[version]</td></tr>\n";

    foreach (array('summary', 'description', 'release_state', 'release_date', 'releases_notes') as $key) {
        if (!isset($info[$key])) {
            $info[$key] = "n/a";
        }
    }

    print "<tr><th align=\"right\" valign=\"top\">Summary:</th><td>" . $info['summary'] . "</td></tr>\n";
    print "<tr><th align=\"right\" valign=\"top\">Description:</th><td>" . nl2br($info['description']) . "</td></tr>\n";
    print "<tr><th align=\"right\" valign=\"top\">Release State:</th><td>" . $info['release_state'] . "</td></tr>\n";
    print "<tr><th align=\"right\" valign=\"top\">Release Date:</th><td>" . $info['release_date'] . "</td></tr>\n";
    print "<tr><th align=\"right\" valign=\"top\">Release Notes:</th><td>" . nl2br($info['release_notes']) . "</td></tr>\n";

	print "</table>\n";
	$form->display();
	$bb->end();
}

response_footer();

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#cc0000\" size=\"+1\">$msg</font><br />\n";
}

?>