<?php
//require 'HTML/Menu.php';
require '/home/cox/web-include/Menu.php'; // XXX Put this class in the right place
/*
Transform the tree of categories in an assoc array
valid for the Menu Class
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
function &cat_selector () {
    global $dbh, $tree;
    if (empty($dbh)) {
        include_once 'DB.php';
        PEAR::setErrorHandling(PEAR_ERROR_DIE);
        $dbh = DB::connect('mysql://pear:pear@localhost/pear');
    }

    $sth = $dbh->query('SELECT id, name, parent FROM categories ORDER BY id');

    while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
        extract($row);
        settype($parent, 'integer');
        $tree[$parent]['children'][] = $id;
        $tree[$id]['parent'] = $parent;
        $tree[$id]['name']   = $name;
    }
    $tree[0]['name'] = 'Categories';

    $menu[1] = tree_to_menu(0,'1');
    $menu[1]['url'] = $GLOBALS['PHP_SELF'];
    $m = new HTML_Menu ($menu, 'tree', 'REQUEST_URI');
    return $m->get();
}
?>