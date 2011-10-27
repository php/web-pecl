<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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
   $Id: package-new.php -1   $
*/

require_once "HTML/Form.php";

if (!defined('PEAR_COMMON_PACKAGE_NAME_PREG')) {
    define('PEAR_COMMON_PACKAGE_NAME_PREG', '/^([A-Z][a-zA-Z0-9_]+|[a-z][a-z0-9_]+)$/');
}

auth_require();

$display_form = true;
$width = 60;
$errorMsg = "";
$jumpto = "name";

/* May seem like overkill, but the prepended get() function checks both GET and POST */
$valid_args = array('submit', 'name','category','license','summary','desc','homepage','cvs_link');
foreach($valid_args as $arg) {
        if(isset($_POST[$arg])) $_POST[$arg] = htmlspecialchars($_POST[$arg], ENT_QUOTES);
        if(isset($_GET[$arg])) $_GET[$arg] = htmlspecialchars($GET[$arg], ENT_QUOTES);
}

$submit = isset($_POST['submit']) ? true : false;

do {
    if (isset($submit)) {
        $required = array("name" => "enter the package name",
                          "summary" => "enter the one-liner description",
                          "desc" => "enter the full description",
                          "license" => "choose a license type",
                          "category" => "choose a category");
        foreach ($required as $field => $_desc) {
            if (empty($_POST[$field])) {
                display_error("Please $_desc!");
                $jumpto = $field;
                break 2;
            }
        }

		  $_POST['license'] = trim($_POST['license']);

		  if (!strcasecmp($_POST['license'], "GPL") ||
			  	!strcasecmp($_POST['license'], "LGPL")) {
			  display_error("Illegal license type.  PECL packages CANNOT be GPL/LGPL licensed and thus MUST NOT be linked to GPL code.  Talk to pecl-dev@lists.php.net for more information.");
			  $jumpto = 'license';
			  break;
		  }

        if (!preg_match(PEAR_COMMON_PACKAGE_NAME_PREG, $_POST['name'])) {
            display_error("Invalid package name.  PECL package names must be ".
                          "all-lowercase, starting with a letter.");
            break;
        }

        $dbh->expectError(DB_ERROR_ALREADY_EXISTS);
        $pkg = package::add(array(
                                  'name'        => $_POST['name'],
                                  'type'        => 'pecl',
                                  'category'    => $_POST['category'],
                                  'license'     => $_POST['license'],
                                  'summary'     => $_POST['summary'],
                                  'description' => $_POST['desc'],
                                  'homepage'    => $_POST['homepage'],
                                  'cvs_link'    => $_POST['cvs_link'],
                                  'lead'        => $auth_user->handle
                                  ));
        $dbh->popExpect();
        if (DB::isError($pkg) && $pkg->getCode() == DB_ERROR_ALREADY_EXISTS) {
            error_handler("The `" . htmlspecialchars($_POST['name'],ENT_QUOTES) . "' package already exists!",
                          "Package already exists");
            exit;
        }
        $display_form = false;
        response_header("Package Registered");
        print "The package `" . htmlspecialchars($_POST['name'], ENT_QUOTES) . "' has been registered in PECL.<br />\n";
        print "You have been assigned as lead developer.<br />\n";
    }
} while (false);

if ($display_form) {
    $title = "New Package";
    response_header($title);

    print "<h1>$title</h1>

<p>Use this form to register a new package.</p>


<p>
<b>Before proceeding</b>, make sure you pick the right name for your
package.  This is usually done through \"community consensus\", which
means posting a suggestion to the pecl-dev mailing list and have
people agree with you.
</p>


";

    if (isset($errorMsg)) {
        print "<table>\n";
        print " <tr>\n";
        print "  <td>&nbsp;</td>\n";
        print "  <td><b>$errorMsg</b></td>\n";
        print " </tr>\n";
        print "</table>\n";
    }

    $categories = $dbh->getAssoc("SELECT id,name FROM categories ORDER BY name");
    $form =& new HTML_Form(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES), "POST");

    print "<form method=\"post\" action=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "\">\n";

    $bb = new BorderBox("Register package", "100%", "", 2, true);

    $bb->horizHeadRow("Package Name", $form->returnText("name", get("name"), 20, 80));
    $bb->horizHeadRow("License", $form->returnText("license", get("license"), 20, 50));
    $cats = $form->returnSelect("category", $categories, get("category"), 1,
                                "--Select Category--");
    $bb->horizHeadRow("Category", $cats);
    $bb->horizHeadRow("Summary", $form->returnText("summary", get("summary"), $width));
    $bb->horizHeadRow("Full description", $form->returnTextarea("desc", get("desc"), $width, 3));
    $bb->horizHeadRow("Additional project homepage", $form->returnText("homepage", get("homepage"), 40, 255));
    $bb->horizHeadRow("Browse Source URL", $form->returnText("cvs_link", get("cvs_link"), 40, 255) .
                                     '<br /><small>For example: http://cvs.php.net/cvs.php/pecl/PDO</small>');
    $bb->fullRow($form->returnSubmit("Submit Request", "submit"));
    $bb->end();

    if ($jumpto) {
        print "\n<script language=\"JavaScript\">\n<!--\n";
        print "document.forms[1].$jumpto.focus();\n";
        print "// -->\n</script>\n";
    }

    print "</form>\n";
}

response_footer();

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#cc0000\" size=\"+1\">$msg</font><br />\n";
}

?>
