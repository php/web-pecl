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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require(true);
require_once "HTML/Form.php";
require_once "ppp/pear-ppp.php";

if (!defined('PEAR_COMMON_PACKAGE_NAME_PREG')) {
    define('PEAR_COMMON_PACKAGE_NAME_PREG', '/^([A-Z][a-zA-Z0-9_]+|[a-z][a-z0-9_]+)$/');
}

$display_form = true;
$width = 60;
$errorMsg = "";
$jumpto = "name";

do {
    if (isset($submit)) {
        $required = array("name" => "enter the package name",
                          "summary" => "enter the one-liner description",
                          "desc" => "enter the full description",
                          "category" => "choose a category");
        if (empty($auth_user) || !user::exists($auth_user->handle)) {
            $additionals = array("user_firstname" => "enter your firstname",
                                 "user_lastname" => "enter your lastname",
                                 "user_email" => "enter your email address",
                                 "user_password" => "enter your password"
                                 );
            $required = array_merge($required, $additionals);
        } else {
            $_POST['handle'] = $auth_user->handle;
        }

        foreach ($required as $field => $_desc) {
            if (!empty($_POST[$field])) {
                continue;
            }

            display_error("Please $_desc!");
            $jumpto = $field;
            break 2;
        }
        if (!empty($_POST['user_password']) && $_POST['user_password'] != $_POST['user_password2']) {
            display_error("The passwords do not match!");
            $jumpto = "user_password";
            break 2;
        }

        if (!preg_match(PEAR_COMMON_PACKAGE_NAME_PREG, $_POST['name'])) {
            display_error("Invalid package name.  PEAR package names must start ".
                          "with a capital letter and contain only letters, ".
                          "digits and underscores.  PECL package names must be ".
                          "all-lowercase, starting with a letter.");
            break 2;
        }

        $dbh->expectError(DB_ERROR_ALREADY_EXISTS);

        $pkg = proposal::add($_POST);

        $dbh->popExpect();
        if (DB::isError($pkg)) {
            if ($pkg->getCode() == DB_ERROR_ALREADY_EXISTS) {
                error_handler("The `" . htmlspecialchars($_POST['name'], ENT_QUOTES) . "' package already exists!",
                              "Package already exists");
            } else {
                error_handler("Registering the package failed.");
            }
            exit;
        }

        $display_form = false;
        response_header("Package Proposed");
        print "The package `" . htmlspecialchars($_POST['name'], ENT_QUOTES) . "' has been proposed in PEAR.<br />\n";
        print "<a href=\"index.php\">Back</a>";
    }
} while (false);

if ($display_form) {
    $title = "Propose new Package";
    response_header($title);

    print "<h1>$title</h1>";

    if (!empty($errorMsg)) {
        print "<table>\n";
        print " <tr>\n";
        print "  <td>&nbsp;</td>\n";
        print "  <td><b>$errorMsg</b></td>\n";
        print " </tr>\n";
        print "</table>\n";
    } else {
        print "<p>Use this form to propose a new package. " .
              "If you already have an account for pear.php.net and " .
              " aren't logged in yet, please " . 
              make_link("/login.php", "login now") . 
              ".</p>";

        print "<p>If you are unsure about the category and the name for your ";
        print "proposal, please ask on the " . 
              make_mailto_link("pear-dev@lists.php.net", "mailinglist") .
              "</p>";
    }

    $categories = $dbh->getAssoc("SELECT id,name FROM categories ORDER BY name");
    $form =& new HTML_Form(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES), "POST");

    $bb = new BorderBox("Propose package", "100%");
    $form->addText("name", "Package Name", null, 20);
    $form->addSelect("category", "Category", $categories, '', 1,
                     '--Select Category--');
    $form->addText("summary", "One-liner description", null, $width);
    $form->addTextarea("desc", "Full description", null, $width, 3);
    $form->addTextarea("source_links", "Links to .phps files<br /><small>(Please enter each URL in it's own line.)</small>",
                       null, $width, 3);

    // Only ask for user information if the user is not logged in
    if (empty($auth_user) || !user::exists($auth_user->handle)) {
       $form->addText("user_firstname", "Your firstname", null, 20);
       $form->addText("user_lastname", "Your lastname", null, 20);
       $form->addPassword("user_password", "Your password", null, 20);
       $form->addText("user_email", "Your email address", null, 20);
    }

    $form->addText("homepage", "Additional project homepage", null, 20);

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
