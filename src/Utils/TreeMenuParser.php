<?php

namespace App\Utils;

use App\Database;
use App\TreeMenu\TreeMenu;
use App\TreeMenu\TreeNode;

/**
 * Retrieves categories from database and converts them to tree menu.
 */
class TreeMenuParser
{
    /**
     * Class constructor.
     */
    public function __construct(Database $database, TreeMenu $treeMenu)
    {
        $this->database = $database;
        $this->treeMenu = $treeMenu;
    }

    /**
     * Generates tree menu from categories in the database with recursion
     * through the tree adding nodes to treemenu.
     */
    public function parse($structure, $parent = null)
    {
        $arguments = [];

        $sql = 'SELECT id, parent, name, description, npackages FROM categories WHERE parent';

        if ($parent === null) {
            $sql .= ' IS NULL';
        } else {
            $sql .= ' = :parent';
            $arguments[':parent'] = $parent;
        }

        $sql .= ' ORDER BY name, id';

        $categories = $this->database->run($sql, $arguments)->fetchAll();

        if (count($categories)) {
            foreach ($categories as $category) {
                $treeNode = new TreeNode(
                    [
                        'text' => htmlspecialchars($category['name'], ENT_QUOTES),
                        'icon' => 'folder.gif'
                    ],
                    ['onclick' => 'category_click(event, this, '.$category['id'].')']
                );

                $newNode = $structure->addItem($treeNode);
                $newNode = $this->parse($newNode, $category['id']);
            }
        }

        return $structure;
    }

    /**
     * Get parsed tree menu.
     */
    public function get()
    {
        $this->treeMenu = $this->parse($this->treeMenu);

        return $this->treeMenu;
    }
}
