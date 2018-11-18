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
  | Authors: Stig S. Bakken <ssb@fast.no>                                |
  |          Tomas V.V.Cox <cox@php.net>                                 |
  |          Martin Jansen <mj@php.net>                                  |
  |          Gregory Beaver <cellog@php.net>                             |
  |          Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

namespace App\Entity;

use App\Database;
use App\Rest;

/**
 * Entity representing the database table categories row.
 */
class Category
{
    private $database;
    private $rest;

    /**
     * Set database handler.
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Set REST generator service.
     */
    public function setRest(Rest $rest)
    {
        $this->rest = $rest;
    }

    /**
     * Add new category
     *
     *    $data = [
     *        'name'   => 'category name',
     *        'desc'   => 'category description',
     *        'parent' => 'category parent id'
     *    ];
     *
     * @param array
     */
    public function add($data)
    {
        // Get ID for the category. The current database schema doesn't have the
        // auto increment set yet for the id column.
        $sql = "SELECT id FROM categories ORDER by id DESC";
        $id = $this->database->run($sql)->fetch()['id'];
        $id = !$id ? 1 : $id;
        $id++;

        $name = $data['name'];
        $desc   = (empty($data['desc'])) ? 'none' : $data['desc'];
        $parent = (empty($data['parent'])) ? null : $data['parent'];

        $sql = 'INSERT INTO categories (id, name, description, parent) VALUES (?, ?, ?, ?)';

        $err = $this->database->run($sql, [$id, $name, $desc, $parent]);

        $this->renumberVisitations($id, $parent);

        $this->rest->saveCategory($name);
        $this->rest->saveAllCategories();

        return $id;
    }

    /**
     * Updates a categories details
     *
     * @param  integer $id   Category ID
     * @param  string  $name Category name
     * @param  string  $desc Category Description
     */
    public function update($id, $name, $desc = '')
    {
        $sql = 'UPDATE categories SET name = ?, description = ? WHERE id = ?';

        return $this->database->run($sql, [$name, $desc, $id]);
    }

    /**
     * Deletes a category
     *
     * @param integer $id Category ID
     */
    public function delete($id)
    {
        // Get category data
        $sql = 'SELECT name, parent FROM categories WHERE id = ?';
        $category = $this->database->run($sql, [$id])->fetch();
        $parentId = $category['parent'];

        // Delete it
        $sql = 'SELECT cat_left, cat_right FROM categories WHERE id = ?';
        $result = $this->database->run($sql, [$id])->fetch();
        $deleted_cat_left  = $result['cat_left'];
        $deleted_cat_right = $result['cat_right'];
        $this->database->run('DELETE FROM categories WHERE id = ?',[$id]);

        // Renumber
        $this->database->run('UPDATE categories SET cat_left = cat_left - 1, cat_right = cat_right - 1 WHERE cat_left > ' . $deleted_cat_left . ' AND cat_right < ' . $deleted_cat_right);
        $this->database->run('UPDATE categories SET cat_left = cat_left - 2, cat_right = cat_right - 2 WHERE cat_right > ' . $deleted_cat_right);

        // Update any child categories
        $this->database->run(sprintf('UPDATE categories SET parent = %s WHERE parent = %d', ($parentId ? $parentId : 'NULL'), $id));

        $this->rest->deleteCategory($category['name']);

        return true;
    }

    /**
     * Some useful "visitation model" tricks:
     *
     * To find the number of child elements:
     *  (right - left - 1) / 2
     *
     * To find the number of child elements (including self):
     *  (right - left + 1) / 2
     *
     * To get all child nodes:
     *
     *  SELECT * FROM table WHERE left > <self.left> AND left < <self.right>
     *
     * To get all child nodes, including self:
     *
     *  SELECT * FROM table WHERE left BETWEEN <self.left> AND <self.right>
     *  "ORDER BY left" gives tree view
     *
     * To get all leaf nodes:
     *
     *  SELECT * FROM table WHERE right-1 = left;
     */
    public function renumberVisitations($id, $parent = null)
    {
        if ($parent === null) {
            $left = $this->database->run("SELECT MAX(cat_right) + 1 AS `left` FROM categories WHERE parent IS NULL")->fetch()['left'];
            $left = (!empty($left)) ? $left : 1; // first node
        } else {
            $left = $this->database->run("SELECT cat_right FROM categories WHERE id = ?", [$parent])->fetch()['cat_right'];
        }

        $right = $left + 1;
        // update my self
        $this->database->run("UPDATE categories SET cat_left = ?, cat_right = ? WHERE id = ?", [$left, $right, $id]);

        if ($parent === null) {
            return true;
        }

        $this->database->run("UPDATE categories SET cat_left = cat_left + 2 WHERE cat_left > ?", [$left]);

        // (cat_right >= $left) == update the parent but not the node itself
        $this->database->run("UPDATE categories SET cat_right = cat_right + 2 WHERE cat_right >= ? and id <> ?", [$left, $id]);

        return true;
    }

    /**
     * Determines if the given category is valid
     *
     * @param  string Name of the category
     * @return  boolean
     */
    public function isValid($categoryName)
    {
        $sql = 'SELECT id FROM categories WHERE name = ?';
        $results = $this->database->run($sql, [$categoryName])->fetchAll();

        return (count($results) > 0);
    }
}
