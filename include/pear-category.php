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
*/

require 'HTML/Menu.php';
/*
Transform the tree of categories in an assoc array
valid for the Ulf's Menu Class
*/
function &tree_to_menu ($tnode, $mpid) {
    global $tree;
    $mnode['title'] = $tree[$tnode]['name'];
    if (isset($tree[$tnode]['children'])) {
        $i = 1;  //menu node 'sub' id
        foreach ($tree[$tnode]['children'] as $node) {
            $pid  = $mpid . $i++;
            $msub = tree_to_menu($node, $pid);
            $msub['url'] = $GLOBALS['PHP_SELF'] . "?catid=$node";
            $mnode['sub'][$pid] = $msub;
        }
    }
    return $mnode;
}

/*
Returns the html needed to print in the category selection page
*/
function initialize_categories_menu () {
    global $dbh, $tree, $menu;

    $sth = $dbh->query('SELECT id, name, parent, cat_left, cat_right
                        FROM categories ORDER BY name');

    while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
        extract($row);
        settype($parent, 'integer');
        $tree[$parent]['children'][] = $id;
        $tree[$id]['parent'] = $parent;
        $subcats = ($cat_right - $cat_left - 1) / 2;
        if ($subcats < 1 && $subcats > 0) {
            $subcats = 1;
        }
        if ($subcats > 0) {
            $name = "$name ($subcats)";
        }
        $tree[$id]['name']   = $name;
    }
    $tree[0]['name'] = 'Top Level';

    $menu[1] = tree_to_menu(0,'1');
    $menu[1]['url'] = $GLOBALS['PHP_SELF'];
}

function &get_categories_menu($type = 'tree') {
    global $menu;
    if (empty($menu)) {
        initialize_categories_menu();
    }
    $m = new HTML_Menu ($menu, $type, 'REQUEST_URI');
    return $m->get();
}

?>