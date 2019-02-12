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
  | Authors: Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

/**
 * TODO: Present options of what to do with orphaned packages when deleting
 * categories.
 */

use App\Auth;
use App\Entity\Category;
use App\TreeMenu\DynamicHtml;
use App\Utils\TreeMenuParser;

require_once __DIR__.'/../../include/pear-prepend.php';

// Restricted to administrators only.
$container->get(Auth::class)->secure(true);

$category = $container->get(Category::class);

// Form submitted?
if (!empty($_POST) && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            if (!empty($_POST['catDesc']) && !empty($_POST['catName'])) {
                $result = $category->add([
                    'name'   => $_POST['catName'],
                    'desc'   => $_POST['catDesc'],
                    'parent' => !empty($_POST['cat_parent']) ? (int) $_POST['cat_parent'] : null
                ]);
                $_SESSION['category_manager']['error_msg'] = !$result ? 'Failed to insert category.' : 'Category added.';
            } else {
                $_SESSION['category_manager']['error_msg'] = 'Please enter a name and description!';
            }
            header('Location: /admin/category-manager.php');
            exit;
        break;

        case 'update':
            if (!empty($_POST['catDesc']) && !empty($_POST['catName'])) {
                $result = $category->update((int) $_POST['cat_parent'], $_POST['catName'], $_POST['catDesc']);
                $_SESSION['category_manager']['error_msg'] = !$result->rowCount() ? 'No changes.' : 'Category updated.';
            } else {
                $_SESSION['category_manager']['error_msg'] = 'Please enter a name and description!';
            }
            header('Location: /admin/category-manager.php');
            exit;
        break;

        case 'delete':
            if (!empty($_POST['cat_parent'])) {
                $result = $category->delete($_POST['cat_parent']);
                $_SESSION['category_manager']['error_msg'] = !$result ? 'Failed to delete category.' : 'Category deleted';
            } else {
                $_SESSION['category_manager']['error_msg'] = 'Please select a category';
            }
            header('Location: /admin/category-manager.php');
            exit;
        break;

        default:
            header('Location: /admin/category-manager.php');
            exit;
        break;
    }
}

// Get the categories with tree menu
$treeMenu = $container->get(TreeMenuParser::class)->get();

// Check for any error msg
if (!empty($_SESSION['category_manager']['error_msg'])) {
    $message = $_SESSION['category_manager']['error_msg'];
    unset($_SESSION['category_manager']['error_msg']);
}

$categories   = $database->run('SELECT id, name, description FROM categories ORDER BY id')->fetchAll();
$treeMenuPres = new DynamicHtml($treeMenu, ['images' => '/img/TreeMenu', 'defaultClass' => 'treeMenuOff']);

echo $template->render('pages/admin/category_manager.php', [
    'categories' => $categories,
    'treeMenuPres' => $treeMenuPres,
    'message' => isset($message) ? $message : '',
]);
