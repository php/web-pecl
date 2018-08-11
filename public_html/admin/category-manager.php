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
   | Authors: Richard Heyes                                               |
   +----------------------------------------------------------------------+
*/

auth_require(true);

/**
 * TODO
 *
 * o Present options of what to do with orphaned packages when
 *   deleting categories.
 */

$template_dir = dirname(__DIR__) . '/../templates/';
	
/**
 * Function to recurse thru the tree adding nodes to treemenu
 */
function parseTree(&$structure, $parent = null)
{
    global $dbh;

    $parent = is_null($parent) ? 'IS NULL' : '= ' . $parent;
		
    // Get categories
    $categories = $dbh->getAll(sprintf('SELECT id, parent, name, description, npackages '
                                       . 'FROM categories WHERE parent %s ORDER BY name, id', 
                                       $parent
                                       ), 
                               null, 
                               DB_FETCHMODE_ASSOC
                               );

    if (count($categories)) {
        foreach ($categories as $cat) {
            $newNode = &$structure->addItem(new HTML_TreeNode(array('text' => htmlspecialchars($cat['name']),
                                                                    'icon' => 'folder.gif'), 
                                                              array('onclick' => 'category_click(event, this, ' . $cat['id'] . ')')
                                                              )
                                            );
            parseTree($newNode, $cat['id']);
        }
    }
}

/**
 * Form submitted?
 */
if (!empty($_POST)) {
    include_once '../../include/pear-category.php';

    switch (@$_POST['action']) {
    case 'add':
        if (!empty($_POST['catDesc']) AND !empty($_POST['catName'])) {
            $result = category::add(array('name'   => $_POST['catName'],
                                          'desc'   => $_POST['catDesc'],
                                          'parent' => !empty($_POST['cat_parent']) ? (int)$_POST['cat_parent'] : null));
            $_SESSION['category_manager']['error_msg'] = PEAR::isError($result) ? 'Failed to insert category: ' . $result->message : 'Category added';
        } else {
            $_SESSION['category_manager']['error_msg'] = 'Please enter a name and description!';
        }
        localRedirect('/admin/category-manager.php');
        break;

    case 'update':
        if (!empty($_POST['catDesc']) AND !empty($_POST['catName'])) {
            $result = category::update((int)$_POST['cat_parent'], $_POST['catName'], $_POST['catDesc']);
            $_SESSION['category_manager']['error_msg'] = PEAR::isError($result) ? 'Failed to insert category: ' . $result->message : 'Category updated';
        } else {
            $_SESSION['category_manager']['error_msg'] = 'Please enter a name and description!';
        }
        localRedirect('/admin/category-manager.php');
        break;

    case 'delete':
        if (!empty($_POST['cat_parent'])) {
            $result = category::delete($_POST['cat_parent']);
            $_SESSION['category_manager']['error_msg'] = PEAR::isError($result) ? 'Failed to delete category: ' . $result->message : 'Category deleted';
        } else {
            $_SESSION['category_manager']['error_msg'] = 'Please select a category';
        }
        localRedirect('/admin/category-manager.php');
        break;

    default:
        localRedirect('/admin/category-manager.php');
    }
}
	
/**
 * Create the menu, set the db to assoc mode
 */
require_once('HTML/TreeMenu.php');
$treeMenu = new HTML_TreeMenu();
	
/**
 * Get the categories
 */
parseTree($treeMenu);

/**
 * Template
 */
// Check for any error msg
if (!empty($_SESSION['category_manager']['error_msg'])) {
    $message = $_SESSION['category_manager']['error_msg'];
    unset($_SESSION['category_manager']['error_msg']);
}

$categories   = $dbh->getAll('SELECT id, name, description FROM categories ORDER BY id', null, DB_FETCHMODE_ASSOC);
$treeMenuPres = new HTML_TreeMenu_DHTML($treeMenu, array('images' => '../gifs/TreeMenu', 'defaultClass' => 'treeMenuOff'));

include($template_dir . 'category-manager.html');
?>
