<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Stig Bakken <ssb@fast.no>                                   |
   |          Tomas V.V.Cox <cox@php.net>                                 |
   |          Martin Jansen <mj@php.net>                                  |
   |          Pierre Joye <pierre@php.net>                                |
   |          Gregory Beaver <cellog@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
*/

// These classes correspond to tables and methods define operations on
// each.

/**
 *
 *
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
function renumber_visitations($id, $parent = null)
{
    global $dbh;
    if ($parent === null) {
        $left = $dbh->getOne("SELECT max(cat_right) + 1 FROM categories
                              WHERE parent IS NULL");
        $left = ($left !== null) ? $left : 1; // first node
    } else {
        $left = $dbh->getOne("SELECT cat_right FROM categories WHERE id = ?", array($parent));
    }
    $right = $left + 1;
    // update my self
    $err = $dbh->query("UPDATE categories
                        SET cat_left = ?, cat_right = ?
                        WHERE id = ?", array($left, $right, $id));
    if (PEAR::isError($err)) {
        return $err;
    }
    if ($parent === null) {
        return true;
    }
    $err = $dbh->query("UPDATE categories SET cat_left = cat_left+2
                        WHERE cat_left > ?", array($left));
    if (PEAR::isError($err)) {
        return $err;
    }
    // (cat_right >= $left) == update the parent but not the node itself
    $err = $dbh->query("UPDATE categories SET cat_right = cat_right+2
                        WHERE cat_right >= ? and id <> ?", array($left, $id));
    if (PEAR::isError($err)) {
        return $err;
    }
    return true;
}


/**
 * Class to handle categories
 *
 * @class   category
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 * @author  Richard Heyes <richard@php.net>
 */
class category
{
    /**
     * Add new category
     *
     *    $data = array(
     *        'name'   => 'category name',
     *        'desc'   => 'category description',
     *        'parent' => 'category parent id'
     *        );
     *
     * @param array
     * @return mixed ID of the category or PEAR error object
     */
    static function add($data)
    {
        global $dbh;
        if (empty($data['name'])) {
            return PEAR::raiseError('no name given');
        }
        $name   = $data['name'];
        $desc   = empty($data['desc'])   ? 'none' : $data['desc'];
        $parent = empty($data['parent']) ? null   : $data['parent'];

        $sql = 'INSERT INTO categories (id, name, description, parent) VALUES (?, ?, ?, ?)';
        $id  = $dbh->nextId('categories');
        $err = $dbh->query($sql, array($id, $name, $desc, $parent));
        if (DB::isError($err)) {
            return $err;
        }

        $err = renumber_visitations($id, $parent);
        if (PEAR::isError($err)) {
            return $err;
        }

        include_once 'pear-rest.php';
        $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
        $pear_rest->saveCategoryREST($name);
        $pear_rest->saveAllCategoriesREST();
        $pear_rest->savePackagesCategoryREST($name);
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
    static function update($id, $name, $desc = '')
    {
        global $dbh;
        $sql  = 'SELECT name FROM categories WHERE id = ?';
        $data = $GLOBALS['dbh']->getOne($sql, array($id));

        include_once 'pear-rest.php';
        $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $dbh);
        $pear_rest->deleteCategoryREST($data);

        $sql = 'UPDATE categories SET name = ?, description = ? WHERE id = ?';
        $ret = $GLOBALS['dbh']->query($sql, array($name, $desc, $id));
        $pear_rest->saveCategoryREST($name);
        $pear_rest->saveAllCategoriesREST();
        $pear_rest->savePackagesCategoryREST($name);
        return $ret;
    }

    /**
     * Deletes a category
     *
     * @param integer $id Cateogry ID
     */
    static function delete($id)
    {

        // if ($GLOBALS['dbh']->query('SELECT COUNT(*) FROM categories WHERE parent = ' . (int)$id) > 0) {
        //     return PEAR::raiseError('Cannot delete a category which has subcategories');
        // }
        //
        // // Get parent ID if any
        // $parentID = $GLOBALS['dbh']->getOne('SELECT parent FROM categories WHERE id = ' . $id);
        // if (!$parentID) {
        //     $nextID = $GLOBALS['dbh']->getOne('SELECT id FROM categories WHERE cat_left = ' . $GLOBALS['dbh']->getOne('SELECT cat_right + 1 FROM categories WHERE id = ' . $id));
        // } else {
        //     $nextID = $parentID;
        // }

        $name = $GLOBALS['dbh']->getOne('SELECT name FROM categories WHERE id = ?', array($id));
        // Get parent ID if any
        $parentID = $GLOBALS['dbh']->getOne('SELECT parent FROM categories WHERE id = ?', array($id));

        // Delete it
        $deleted_cat_left  = $GLOBALS['dbh']->getOne('SELECT cat_left FROM categories WHERE id = ?', array($id));
        $deleted_cat_right = $GLOBALS['dbh']->getOne('SELECT cat_right FROM categories WHERE id = ?', array($id));

        $GLOBALS['dbh']->query('DELETE FROM categories WHERE id = ' . $id);

        // Renumber
        $GLOBALS['dbh']->query('UPDATE categories SET cat_left = cat_left - 1, cat_right = cat_right - 1 WHERE cat_left > ? AND cat_right < ?', array($deleted_cat_left, $deleted_cat_right));
        $GLOBALS['dbh']->query('UPDATE categories SET cat_left = cat_left - 2, cat_right = cat_right - 2 WHERE cat_right > ?', array($deleted_cat_right));

        // Update any child categories
        $GLOBALS['dbh']->query('UPDATE categories SET parent = ? WHERE parent = ?', array(($parentID ? $parentID : 'NULL'), $id));

        include_once 'pear-rest.php';
        $pear_rest = new pearweb_Channel_REST_Generator(PEAR_REST_PATH, $GLOBALS['dbh']);
        $pear_rest->deleteCategoryREST($name);
        $pear_rest->saveAllCategoriesREST();
        return true;
    }

    /**
     * List all categories
     *
     * @return array
     */
    static function listAll()
    {
        global $dbh;
        $sql = 'SELECT * FROM categories ORDER BY name';
        return $dbh->getAll($sql, null, DB_FETCHMODE_ASSOC);
    }

    /**
     * Return a list of packages in this category
     *
     * @param string $category
     * @return array
     */
    static function listPackages($category)
    {
        global $dbh;
        $query = '
            SELECT
                p.id, p.name
            FROM
                packages p, categories c
            WHERE
                p.category = c.id AND
                c.name = ?';
        $recent = $dbh->getAll($query, array($category), DB_FETCHMODE_ASSOC);
        return $recent;
    }

    /**
     * Get list of recent releases for the given category
     *
     * @param  int Number of releases to return
     * @param  string Name of the category
     * @return array
     */
    static function getRecent($n, $category)
    {
        global $dbh;
        $recent = array();

        $query = '
            SELECT
                p.id AS id,
                p.name AS name,
                p.summary AS summary,
                r.version AS version,
                r.releasedate AS releasedate,
                r.releasenotes AS releasenotes,
                r.doneby AS doneby,
                r.state AS state
            FROM packages p, releases r, categories c
            WHERE
                p.id = r.package
                AND p.category = c.id
                AND c.name = ?
            ORDER BY r.releasedate DESC';

        $sth = $dbh->limitQuery($query, 0, $n, array($category));
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    /**
     * Determines if the given category is valid
     *
     * @access public
     * @param  string Name of the category
     * @return  boolean
     */
    static function isValid($category)
    {
        global $dbh;
        $sql = 'SELECT id FROM categories WHERE name = ?';
        $sth = $dbh->query($sql, array($category));
        return ($sth->numRows() > 0);
    }
}
