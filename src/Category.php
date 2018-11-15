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

namespace App;

use \PEAR as PEAR;
use \DB as DB;

/**
 * Class to handle categories.
 */
class Category
{
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
     * @return mixed ID of the category or PEAR error object
     */
    public static function add($data)
    {
        global $dbh, $rest;

        $name = $data['name'];

        if (empty($name)) {
            return PEAR::raiseError('no name given');
        }

        $desc   = (empty($data['desc'])) ? 'none' : $data['desc'];
        $parent = (empty($data['parent'])) ? null : $data['parent'];

        $sql = 'INSERT INTO categories (id, name, description, parent) VALUES (?, ?, ?, ?)';
        $id  = $dbh->nextId('categories');
        $err = $dbh->query($sql, [$id, $name, $desc, $parent]);

        if (DB::isError($err)) {
            return $err;
        }

        $err = self::renumberVisitations($id, $parent);

        if (PEAR::isError($err)) {
            return $err;
        }

        $rest->saveCategory($name);
        $rest->saveAllCategories();

        return $id;
    }

    /**
     * Updates a categories details
     *
     * @param  integer $id   Category ID
     * @param  string  $name Category name
     * @param  string  $desc Category Description
     * @return mixed         True on success, pear_error otherwise
     */
    public static function update($id, $name, $desc = '')
    {
        global $dbh;

        return $dbh->query(sprintf('UPDATE categories SET name = %s, description = %s WHERE id = %d',
                                              $dbh->quote($name),
                                              $dbh->quote($desc),
                                              $id));
    }

    /**
     * Deletes a category
     *
     * @param integer $id Category ID
     */
    public static function delete($id)
    {
        global $dbh, $rest;

        // Get parent ID if any
        $parentID = $dbh->getOne('SELECT parent FROM categories WHERE id = ' . $id);

        // Delete it
        $deleted_cat_left  = $dbh->getOne('SELECT cat_left FROM categories WHERE id = ' . $id);
        $deleted_cat_right = $dbh->getOne('SELECT cat_right FROM categories WHERE id = ' . $id);

        $dbh->query('DELETE FROM categories WHERE id = ' . $id);

        // Renumber
        $dbh->query('UPDATE categories SET cat_left = cat_left - 1, cat_right = cat_right - 1 WHERE cat_left > ' . $deleted_cat_left . ' AND cat_right < ' . $deleted_cat_right);
        $dbh->query('UPDATE categories SET cat_left = cat_left - 2, cat_right = cat_right - 2 WHERE cat_right > ' . $deleted_cat_right);

        // Update any child categories
        $dbh->query(sprintf('UPDATE categories SET parent = %s WHERE parent = %d', ($parentID ? $parentID : 'NULL'), $id));

        $rest->deleteCategory($name);

        return true;
    }

    /**
     * List all categories
     *
     * @return array
     */
    public static function listAll()
    {
        global $dbh;

        return $dbh->getAll('SELECT * FROM categories ORDER BY name', null, DB_FETCHMODE_ASSOC);
    }

    /**
     * Return a list of packages in this category
     *
     * @param string $category
     * @return array
     */
    public static function listPackages($category)
    {
        global $dbh;

        $query = 'SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = ?';

        $recent = $dbh->getAll($query, [$category], DB_FETCHMODE_ASSOC);

        return $recent;
    }

    /**
     * Get list of recent releases for the given category
     *
     * @param  int Number of releases to return
     * @param  string Name of the category
     * @return array
     */
    public static function getRecent($n, $category)
    {
        global $dbh;

        $recent = [];

        $query = "SELECT p.id AS id, " .
            "p.name AS name, " .
            "p.summary AS summary, " .
            "r.version AS version, " .
            "r.releasedate AS releasedate, " .
            "r.releasenotes AS releasenotes, " .
            "r.doneby AS doneby, " .
            "r.state AS state " .
            "FROM packages p, releases r, categories c " .
            "WHERE p.id = r.package " .
            "AND p.package_type = 'pecl' " .
            "AND p.category = c.id AND c.name = '" . $category . "'" .
            "ORDER BY r.releasedate DESC";

        $sth = $dbh->limitQuery($query, 0, $n);
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }

        return $recent;
    }

    /**
     * Determines if the given category is valid
     *
     * @param  string Name of the category
     * @return  boolean
     */
    public static function isValid($category)
    {
        global $dbh;

        $query = 'SELECT id FROM categories WHERE name = ?';
        $sth = $dbh->query($query, [$category]);

        return ($sth->numRows() > 0);
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
    public static function renumberVisitations($id, $parent = null)
    {
        global $dbh;

        if ($parent === null) {
            $left = $dbh->getOne("select max(cat_right) + 1 from categories where parent is null");
            $left = (!empty($left)) ? $left : 1; // first node
        } else {
            $left = $dbh->getOne("select cat_right from categories where id = $parent");
        }

        $right = $left + 1;
        // update my self
        $err = $dbh->query("update categories
                            set cat_left = $left, cat_right = $right
                            where id = $id");
        if (PEAR::isError($err)) {
            return $err;
        }

        if ($parent === null) {
            return true;
        }

        $err = $dbh->query("update categories set cat_left = cat_left+2
                            where cat_left > $left");
        if (PEAR::isError($err)) {
            return $err;
        }

        // (cat_right >= $left) == update the parent but not the node itself
        $err = $dbh->query("update categories set cat_right = cat_right+2
                            where cat_right >= $left and id <> $id");
        if (PEAR::isError($err)) {
            return $err;
        }

        return true;
    }
}
