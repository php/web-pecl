<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

use App\Repository\CategoryRepository;
use App\Repository\PackageRepository;

if (!defined('PEAR_COMMON_PACKAGE_NAME_PREG')) {
    define('PEAR_COMMON_PACKAGE_NAME_PREG', '/^([A-Z][a-zA-Z0-9_]+|[a-z][a-z0-9_]+)$/');
}

$auth->require();

$display_form = true;
$errorMsg = "";
$jumpto = "name";

$valid_args = ['submit', 'name','category','license','summary','desc','homepage','cvs_link'];
foreach($valid_args as $arg) {
        if(isset($_POST[$arg])) $_POST[$arg] = htmlspecialchars($_POST[$arg], ENT_QUOTES);
}

$submit = isset($_POST['submit']) ? true : false;

do {
    if (isset($submit)) {
        $required = ["name" => "enter the package name",
                          "summary" => "enter the one-liner description",
                          "desc" => "enter the full description",
                          "license" => "choose a license type",
                          "category" => "choose a category"];
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

        $packageRepository = new PackageRepository($database);
        $existing = $packageRepository->findOneByName($_POST['name']);
        if ($existing) {
            error_handler(
                'The '.htmlspecialchars($_POST['name'], ENT_QUOTES).' package already exists!',
                "Package already exists"
            );
        } else {
            try {
                $pkg = $packageEntity->add([
                    'name'        => $_POST['name'],
                    'type'        => 'pecl',
                    'category'    => $_POST['category'],
                    'license'     => $_POST['license'],
                    'summary'     => $_POST['summary'],
                    'description' => $_POST['desc'],
                    'homepage'    => $_POST['homepage'],
                    'cvs_link'    => $_POST['cvs_link'],
                    'lead'        => $auth_user->handle
                ]);
            } catch (\Exception $e) {
                error_handler(
                    'Error occurred',
                    "Error"
                );
            }
        }

        $display_form = false;
        response_header("Package Registered");
        print "The package `" . htmlspecialchars($_POST['name'], ENT_QUOTES) . "' has been registered in PECL.<br />\n";
        print "You have been assigned as lead developer.<br />\n";
    }
} while (false);

if ($display_form) {
    response_header('New Package');

    $categoryRepository = new CategoryRepository($database);
    $categories = $categoryRepository->findAll();

    include __DIR__.'/../templates/forms/new_package.php';

    if ($jumpto) {
        print "\n<script>\n";
        print "document.forms[1].$jumpto.focus();\n";
        print "</script>\n";
    }
}

response_footer();

function display_error($msg)
{
    global $errorMsg;

    $errorMsg .= "<font color=\"#cc0000\" size=\"+1\">$msg</font><br />\n";
}
