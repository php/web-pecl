<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
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
   $Id$
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

        if (!preg_match(PEAR_COMMON_PACKAGE_NAME_PREG, $name)) {
            display_error("Invalid package name.  PEAR package names must start ".
                          "with a capital letter and contain only letters, ".
                          "digits and underscores.  PECL package names must be ".
                          "all-lowercase, starting with a letter.");
            break;
        }

        $dbh->expectError(DB_ERROR_ALREADY_EXISTS);
        $pkg = package::add(array(
                                  'name'        => $name,
                                  'category'    => $category,
                                  'license'     => $license,
                                  'summary'     => $summary,
                                  'description' => $desc,
                                  'lead'        => $auth_user->handle,
                                  ));
        $dbh->popExpect();
        if (DB::isError($pkg) && $pkg->getCode() == DB_ERROR_ALREADY_EXISTS) {
            error_handler("The `$name' package already exists!",
                          "Package already exists");
            exit;
        }
        $display_form = false;
        response_header("Package Registered");
        print "The package `$name' has been registered in PEAR.<br />\n";
        print "You have been assigned as lead developer.<br />\n";
    }
} while (false);

if ($display_form) {
    $title = "New Package";
    response_header($title);

    print "<h1>$title</h1>

Use this form to register a new package.

<p />

<b>Before proceeding</b>, make sure you pick the right name for your
package.  This is usually done through \"community consensus\", which
means posting a suggestion to the pear-dev mailing list and have
people agree with you.

<p />

Note that if you don't follow this simple rule and break
established naming conventions, your package will be taken hostage.
So please play nice, that way we can keep the bureaucracy at a
minimum.

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
    $form =& new HTML_Form($_SERVER['PHP_SELF'], "POST");

    $bb = new BorderBox("Register package", "100%");
    $form->addText("name", "Package Name", null, 20);
    $form->addText("license", "License", null, 20);
    $form->addSelect("category", "Category", $categories, '', 1,
                     '--Select Category--');
    $form->addText("summary", "One-liner description", null, $width);
    $form->addTextarea("desc", "Full description", null, $width, 3);
    $form->addSubmit("submit", "Submit Request");

    $form->display();

    if ($jumpto) {
        print "\n<script language=\"JavaScript\">\n<!--\n";
        print "document.forms[1].$jumpto.focus();\n";
        print "// -->\n</script>\n";
    }
    $bb->end();
}

response_footer();

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#cc0000\" size=\"+1\">$msg</font><br />\n";
}

?>
