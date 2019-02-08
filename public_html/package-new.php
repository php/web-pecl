<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
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

use App\Auth;
use App\Repository\CategoryRepository;
use App\Repository\PackageRepository;

require_once __DIR__.'/../include/pear-prepend.php';

$container->get(Auth::class)->secure();

$errors = [];
$jumpTo = 'name';

if (isset($_POST['submit'])) {
    $required = [
        'name' => 'Please enter the package name.',
        'license' => 'Please choose a license type.',
        'category' => 'Please choose a category.',
        'summary' => 'Please enter the one-liner description.',
        'desc' => 'Please enter the full description.',
    ];

    foreach ($required as $field => $desc) {
        if (empty($_POST[$field])) {
            $errors[] = $desc;
            $jumpTo = $field;
        }
    }

    $_POST['license'] = trim($_POST['license']);

    if (
        !strcasecmp($_POST['license'], 'GPL')
        || !strcasecmp($_POST['license'], 'LGPL')
    ) {
        $errors[] = 'Illegal license type. PECL packages CANNOT be GPL/LGPL licensed and thus MUST NOT be linked to GPL code. Talk to pecl-dev@lists.php.net for more information.';
        $jumpTo = 'license';
    }

    if (!preg_match($container->get('valid_extension_name_regex'), $_POST['name'])) {
        $errors[] = 'Invalid package name. PECL package names must start with a letter and preferably include only lowercase letters. Optionally, numbers and underscores are also allowed.';
    }

    if ($container->get(PackageRepository::class)->findOneByName($_POST['name'])) {
        $errors[] = 'The '.$_POST['name'].' package already exists!';
    }

    if (0 === count($errors)) {
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
            $errors[] = 'Error occurred.';
        }
    }
}

echo $template->render('pages/package_new.php', [
    'categories' => $container->get(CategoryRepository::class)->findAll(),
    'jumpTo' => $jumpTo,
    'errors' => $errors,
]);
