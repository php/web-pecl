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
   | Authors: Stig Sæther Bakken <ssb@fast.no>                            |
   |          Tomas V.V.Cox <cox@php.net>                                 |
   |          Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once 'DB/storage.php';
require_once 'PEAR/Common.php';
require_once 'HTTP.php';

// {{{ validate()

function validate($entity, $field, $value /* , $oldvalue, $object */) {
    switch ("$entity/$field") {
        case "users/handle":
            if (!preg_match('/^[a-z][a-z0-9]+$/i', $value)) {
                return false;
            }
            break;
        case "users/name":
            if (!$value) {
                return false;
            }
            break;
        case "users/email":
            if (!preg_match('/[a-z0-9_\.\+%]@[a-z0-9\.]+\.[a-z]+$', $email)) {
                return false;
            }
            break;
    }
    return true;
}

// }}}

// {{{ renumber_visitations()

/*

Some useful "visitation model" tricks:

To find the number of child elements:
 (right - left - 1) / 2

To find the number of child elements (including self):
 (right - left + 1) / 2

To get all child nodes:

 SELECT * FROM table WHERE left > <self.left> AND left < <self.right>


To get all child nodes, including self:

 SELECT * FROM table WHERE left BETWEEN <self.left> AND <self.right>
 "ORDER BY left" gives tree view

To get all leaf nodes:

 SELECT * FROM table WHERE right-1 = left;

 */

function renumber_visitations($id, $parent)
{
    global $dbh;
    if ($parent === null) {
        $left = $dbh->getOne("select max(cat_right) + 1 from categories
                              where parent is null");
        $left = ($left !== null) ? $left : 1; // first node
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

// }}}

// {{{ version_compare_firstelem()

function version_compare_firstelem($a, $b)
{
    reset($a);
    $elem = key($a);
    return version_compare($a[$elem], $b[$elem]);
}

// }}}

// These classes correspond to tables and methods define operations on
// each.  They are packaged into classes for easier xmlrpc
// integration.

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
    // {{{ *proto int    category::add(struct)

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
    function add($data)
    {
        global $dbh;
        $name = $data['name'];
        if (empty($name)) {
            return PEAR::raiseError('no name given');
        }
        $desc   = (empty($data['desc'])) ? 'none' : $data['desc'];
        $parent = (empty($data['parent'])) ? null : $data['parent'];

        $sql = 'INSERT INTO categories (id, name, description, parent)'.
             'VALUES (?, ?, ?, ?)';
        $id  = $dbh->nextId('categories');
        $err = $dbh->query($sql, array($id, $name, $desc, $parent));
        if (DB::isError($err)) {
            return $err;
        }
        $err = renumber_visitations($id, $parent);
        if (PEAR::isError($err)) {
            return $err;
        }
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
    function update($id, $name, $desc = '')
    {
        return $GLOBALS['dbh']->query(sprintf('UPDATE categories SET name = %s, description = %s WHERE id = %d',
                                              $GLOBALS['dbh']->quote($name),
                                              $GLOBALS['dbh']->quote($desc),
                                              $id));
    }

    /**
    * Deletes a category
    *
    * @param integer $id Cateogry ID
    */
    function delete($id)
    {
    /*
        if ($GLOBALS['dbh']->query('SELECT COUNT(*) FROM categories WHERE parent = ' . (int)$id) > 0) {
            return PEAR::raiseError('Cannot delete a category which has subcategories');
        }

        // Get parent ID if any
        $parentID = $GLOBALS['dbh']->getOne('SELECT parent FROM categories WHERE id = ' . $id);
        if (!$parentID) {
            $nextID = $GLOBALS['dbh']->getOne('SELECT id FROM categories WHERE cat_left = ' . $GLOBALS['dbh']->getOne('SELECT cat_right + 1 FROM categories WHERE id = ' . $id));
        } else {
            $nextID = $parentID;
        }
    */
        // Get parent ID if any
        $parentID = $GLOBALS['dbh']->getOne('SELECT parent FROM categories WHERE id = ' . $id);

        // Delete it
        $deleted_cat_left  = $GLOBALS['dbh']->getOne('SELECT cat_left FROM categories WHERE id = ' . $id);
        $deleted_cat_right = $GLOBALS['dbh']->getOne('SELECT cat_right FROM categories WHERE id = ' . $id);

        $GLOBALS['dbh']->query('DELETE FROM categories WHERE id = ' . $id);

        // Renumber
        $GLOBALS['dbh']->query('UPDATE categories SET cat_left = cat_left - 1, cat_right = cat_right - 1 WHERE cat_left > ' . $deleted_cat_left . ' AND cat_right < ' . $deleted_cat_right);
        $GLOBALS['dbh']->query('UPDATE categories SET cat_left = cat_left - 2, cat_right = cat_right - 2 WHERE cat_right > ' . $deleted_cat_right);

        // Update any child categories
        $GLOBALS['dbh']->query(sprintf('UPDATE categories SET parent = %s WHERE parent = %d', ($parentID ? $parentID : 'NULL'), $id));

        return true;
    }

    // }}}
    // {{{  proto array  category::listAll()

    /**
     * List all categories
     *
     * @return array
     */
    function listAll()
    {
        global $dbh;
        return $dbh->getAll("SELECT * FROM categories ORDER BY name",
                            null, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{  proto array  category::getRecent(int, string)

    /**
     * Get list of recent releases for the given category
     *
     * @param  int Number of releases to return
     * @param  string Name of the category
     * @return array
     */
    function getRecent($n, $category)
    {
        global $dbh;
        $recent = array();

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

    // }}}
    // {{{ *proto bool   category::isValid(string)

    /**
     * Determines if the given category is valid
     *
     * @access public
     * @param  string Name of the category
     * @return  boolean
     */
    function isValid($category)
    {
        global $dbh;
        $query = "SELECT id FROM categories WHERE name = ?";
        $sth = $dbh->query($query, array($category));
        return ($sth->numRows() > 0);
    }

    // }}}
}

/**
 * Class to handle packages
 *
 * @class   package
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class package
{
    // {{{ *proto int    package::add(struct)

    /**
     * Add new package
     *
     * @param array
     * @return mixed ID of new package or PEAR error object
     */
    function add($data)
    {
        global $dbh;
        // name, category
        // license, summary, description
        // lead
        extract($data);
        if (empty($license)) {
            $license = "PEAR License";
        }
        if (!empty($category) && (int)$category == 0) {
            $category = $dbh->getOne("SELECT id FROM categories WHERE name = ?",
                                     array($category));
        }
        if (empty($category)) {
            return PEAR::raiseError("package::add: invalid `category' field");
        }
        if (empty($name)) {
            return PEAR::raiseError("package::add: invalid `name' field");
        }
        $query = "INSERT INTO packages (id,name,package_type,category,license,summary,description,homepage,cvs_link) VALUES(?,?,?,?,?,?,?,?,?)";
        $id = $dbh->nextId("packages");
        $err = $dbh->query($query, array($id, $name, $type, $category, $license, $summary, $description, $homepage, $cvs_link));
        if (DB::isError($err)) {
            return $err;
        }
        $sql = "UPDATE categories SET npackages = npackages + 1
                WHERE id = $category";
        if (DB::isError($err = $dbh->query($sql))) {
            return $err;
        }
        if (isset($lead) && DB::isError($err = maintainer::add($id, $lead, 'lead'))) {
            return $err;
        }

        return $id;
    }

    // }}}

    /**
     * Implemented $field values:
     * releases, notes, category, description, authors, categoryid,
     * packageid, authors
     */

    // {{{  proto struct package::info(string|int, [string])
    /*
     * Implemented $field values:
     * releases, notes, category, description, authors, categoryid,
     * packageid, authors
     */

    /**
     * Get package information
     *
     * @static
     * @param  mixed  Name of the package or it's ID
     * @param  string Single field to fetch
     * @param  boolean Should PEARd packages also be taken into account?
     * @return mixed
     */
    function info($pkg, $field = null, $allow_pear = false)
    {
        global $dbh;

        if (is_numeric($pkg)) {
            $what = "id";
        } else {
            $what = "name";
        }

        $package_type = '';
        if ($allow_pear) {
             $package_type = "((p.package_type = 'pear' AND p.approved = 1) OR p.package_type = 'pecl') AND ";
        } else {
             $package_type = "p.package_type = 'pecl' AND ";
        }

        $pkg_sql = "SELECT p.id AS packageid, p.name AS name, ".
             "p.package_type AS type, ".
             "c.id AS categoryid, c.name AS category, ".
             "p.stablerelease AS stable, p.license AS license, ".
             "p.summary AS summary, p.homepage AS homepage, ".
             "p.description AS description, p.cvs_link AS cvs_link, ".
             "p.doc_link as doc_link".
             " FROM packages p, categories c ".
             "WHERE " . $package_type . " c.id = p.category AND p.{$what} = ?";
        $rel_sql = "SELECT version, id, doneby, license, summary, ".
             "description, releasedate, releasenotes, state " . //, packagexmlversion ".
             "FROM releases ".
             "WHERE package = ? ".
             "ORDER BY releasedate DESC";
        $notes_sql = "SELECT id, nby, ntime, note FROM notes WHERE pid = ?";
        $deps_sql = "SELECT type, relation, version, name, release, optional
                     FROM deps
                     WHERE package = ? ORDER BY optional ASC";
        if ($field === null) {
            $info =
                 $dbh->getRow($pkg_sql, array($pkg), DB_FETCHMODE_ASSOC);
            $info['releases'] =
                 $dbh->getAssoc($rel_sql, false, array($info['packageid']),
                 DB_FETCHMODE_ASSOC);
            $rels = sizeof($info['releases']) ? array_keys($info['releases']) : array('');
            $info['stable'] = $rels[0];
            $info['notes'] =
                 $dbh->getAssoc($notes_sql, false, array(@$info['packageid']),
                 DB_FETCHMODE_ASSOC);
            $deps =
                 $dbh->getAll($deps_sql, array(@$info['packageid']),
                 DB_FETCHMODE_ASSOC);
            foreach($deps as $dep) {
                $rel_version = null;
                foreach($info['releases'] as $version => $rel) {
                    if ($rel['id'] == $dep['release']) {
                        $rel_version = $version;
                        break;
                    };
                };
                if ($rel_version !== null) {
                    unset($dep['release']);
                    $info['releases'][$rel_version]['deps'][] = $dep;
                };
            };
        } else {
            // get a single field
            if ($field == 'releases' || $field == 'notes') {
                if ($what == "name") {
                    $pid = $dbh->getOne("SELECT p.id FROM packages p ".
                                        "WHERE " . $package_type . " p.name = ?", array($pkg));
                } else {
                    $pid = $pkg;
                }
                if ($field == 'releases') {
                    $info = $dbh->getAssoc($rel_sql, false, array($pid),
                    DB_FETCHMODE_ASSOC);
                } elseif ($field == 'notes') {
                    $info = $dbh->getAssoc($notes_sql, false, array($pid),
                    DB_FETCHMODE_ASSOC);
                }
            } elseif ($field == 'category') {
                $sql = "SELECT c.name FROM categories c, packages p ".
                     "WHERE c.id = p.category AND " . $package_type . " p.{$what} = ?";
                $info = $dbh->getAssoc($sql, false, array($pkg));
            } elseif ($field == 'description') {
                $sql = "SELECT description FROM packages p WHERE " . $package_type . " p.{$what} = ?";
                $info = $dbh->query($sql, array($pkg));
            } elseif ($field == 'authors') {
                $sql = "SELECT u.handle, u.name, u.email, u.showemail, m.role
                        FROM maintains m, users u, packages p
                        WHERE " . $package_type ." m.package = p.id
                        AND p.$what = ?
                        AND m.handle = u.handle";
                $info = $dbh->getAll($sql, array($pkg), DB_FETCHMODE_ASSOC);
            } else {
                if ($field == 'categoryid') {
                    $dbfield = 'category';
                } elseif ($field == 'packageid') {
                    $dbfield = 'id';
                } else {
                    $dbfield = $field;
                }
                $sql = "SELECT $dbfield FROM packages p WHERE " . $package_type ." p.{$what} = ?";
                $info = $dbh->getOne($sql, array($pkg));
            }
        }
        return $info;
    }

    // }}}
    // {{{  proto struct package::listAll([bool])

    /**
     * List all packages
     *
     * @static
     * @param boolean Only list releases packages?
     * @param boolean If listing released packages only, only list stable releases?
     * @param boolean List also PEAR packages
     * @return array
     */
    function listAll($released_only = true, $stable_only = true, $include_pear = false)
    {
        global $dbh, $HTTP_RAW_POST_DATA;

        if (isset($HTTP_RAW_POST_DATA)) {
            $include_pecl = true;
        }
        
        $package_type = '';
        if (!$include_pear) {
            $package_type = "p.package_type = 'pecl' AND p.approved = 1 AND ";
        }

        $packageinfo = $dbh->getAssoc("SELECT p.name, p.id AS packageid, ".
            "c.id AS categoryid, c.name AS category, ".
            "p.license AS license, ".
            "p.summary AS summary, ".
            "p.description AS description, ".
            "m.handle AS lead ".
            " FROM packages p, categories c, maintains m ".
            "WHERE " . $package_type .
            " c.id = p.category ".
            "  AND p.id = m.package ".
            "  AND m.role = 'lead' ".
            "ORDER BY p.name", false, null, DB_FETCHMODE_ASSOC);
        $allreleases = $dbh->getAssoc(
            "SELECT p.name, r.id AS rid, r.version AS stable, r.state AS state ".
            "FROM packages p, releases r ".
            "WHERE " . $package_type .
            "p.id = r.package ".
            "ORDER BY r.releasedate ASC ", false, null, DB_FETCHMODE_ASSOC);
        $stablereleases = $dbh->getAssoc(
            "SELECT p.name, r.id AS rid, r.version AS stable, r.state AS state ".
            "FROM packages p, releases r ".
            "WHERE " . $package_type .
            "p.id = r.package ".
            ($released_only ? "AND r.state = 'stable' " : "").
            "ORDER BY r.releasedate ASC ", false, null, DB_FETCHMODE_ASSOC);
        $deps = $dbh->getAll(
            "SELECT package, release , type, relation, version, name ".
            "FROM deps", null, DB_FETCHMODE_ASSOC);
        foreach ($packageinfo as $pkg => $info) {
            $packageinfo[$pkg]['stable'] = false;
        }
        foreach ($stablereleases as $pkg => $stable) {
            $packageinfo[$pkg]['stable'] = $stable['stable'];
            $packageinfo[$pkg]['unstable'] = false;
            $packageinfo[$pkg]['state']  = $stable['state'];
        }
        if (!$stable_only) {
            foreach ($allreleases as $pkg => $stable) {
                if ($stable['state'] == 'stable') {
                    $packageinfo[$pkg]['stable'] = $stable['stable'];
                } else {
                    $packageinfo[$pkg]['unstable'] = $stable['stable'];
                }
                $packageinfo[$pkg]['state']  = $stable['state'];
            }
        }
        $var = !$stable_only ? 'allreleases' : 'stablereleases';
        foreach(array_keys($packageinfo) as $pkg) {
            $_deps = array();
            foreach($deps as $dep) {
                if ($dep['package'] == $packageinfo[$pkg]['packageid']
                    && isset($$var[$pkg])
                    && $dep['release'] == $$var[$pkg]['rid'])
                {
                    unset($dep['rid']);
                    unset($dep['release']);
                    if ($dep['type'] == 'pkg' && isset($packageinfo[$dep['name']])) {
                        $dep['package'] = $packageinfo[$dep['name']]['packageid'];
                    } else {
                        $dep['package'] = 0;
                    }
                    $_deps[] = $dep;
                };
            };
            $packageinfo[$pkg]['deps'] = $_deps;
        };

        if ($released_only) {
            if (!$stable_only) {
                foreach ($packageinfo as $pkg => $info) {
                    if (!isset($allreleases[$pkg]) && !isset($stablereleases[$pkg])) {
                        unset($packageinfo[$pkg]);
                    }
                }
            } else {
                foreach ($packageinfo as $pkg => $info) {
                    if (!isset($stablereleases[$pkg])) {
                        unset($packageinfo[$pkg]);
                    }
                }
            }
        }
        return $packageinfo;
    }

    // }}}

    // {{{  proto struct package::listAllwithReleases()

    /**
     * Get list of packages and their releases
     *
     * @access public
     * @return array
     * @static
     */
    function listAllwithReleases()
    {
        global $dbh;

        $query = "SELECT
                      p.id AS pid, p.name, r.id AS rid, r.version, r.state
                  FROM packages p, releases r 
                  WHERE p.package_type = 'pecl' AND p.approved = 1 AND p.id = r.package
                  ORDER BY p.name, r.version DESC";
        $sth = $dbh->query($query);

        if (DB::isError($sth)) {
            return $sth;
        }

        while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
            $packages[$row['pid']]['name'] = $row['name'];
            $packages[$row['pid']]['releases'][] = array('id' => $row['rid'],
                                                         'version' => $row['version'],
                                                         'state' => $row['state']
                                                         );
        }

        return $packages;
    }

    // }}}
    // {{{  proto struct package::listLatestReleases([string])

    /**
     * List latest releases
     *
     * @static
     * @param  string Only list release with specific state (Optional)
     * @return array
     */
    function listLatestReleases($state = '')
    {
        global $dbh;
        $query =
             "SELECT ".
             "p.name AS package, ".
             "r.version AS version, ".
             "r.state AS state, ".
             "f.fullpath AS fullpath ".
             "FROM packages p, releases r, files f ".
             "WHERE p.package_type = 'pecl' AND p.approved = 1 AND p.id = r.package ".
             "AND f.package = p.id ".
             "AND f.release = r.id ".
             "AND p.package_type = 'pecl'";
        if (release::isValidState($state)) {
            $better = release::betterStates($state);
            $query .= " AND (r.state = '$state'";
            $i = 0;
            if (is_array($better)) {
                foreach ($better as $b) {
                    $query .= " OR r.state = '$b'";
                }
            }
            $query .= ")";
        }
        $query .= " ORDER BY p.name";
        $sortfunc = "version_compare_firstelem";
        $res = $dbh->getAssoc($query, false, null, DB_FETCHMODE_ASSOC, true);
        foreach ($res as $pkg => $ver) {
            if (sizeof($ver) > 1) {
                usort($ver, $sortfunc);
            }
            $res[$pkg] = array_pop($ver);
            $res[$pkg]['filesize'] = (int)@filesize($res[$pkg]['fullpath']);
            unset($res[$pkg]['fullpath']);
        }
        return $res;
    }

    // }}}
    // {{{  proto struct package::listUpgrades(struct)

    /**
     * List available upgrades
     *
     * @static
     * @param array Array containing the currently installed packages
     * @return array
     */
    function listUpgrades($currently_installed)
    {
        global $dbh;
        if (sizeof($currently_installed) == 0) {
            return array();
        }
        $query = "SELECT ".
             "p.name AS package, ".
             "r.id AS releaseid, ".
             "r.package AS packageid, ".
             "r.version AS version, ".
             "r.state AS state, ".
             "r.doneby AS doneby, ".
             "r.license AS license, ".
             "r.summary AS summary, ".
             "r.description AS description, ".
             "r.releasedate AS releasedate, ".
             "r.releasenotes AS releasenotes ".
             "FROM releases r, packages p WHERE p.package_type = 'pecl' AND p.approved = 1 AND r.package = p.id AND (";
        $conditions = array();
        foreach ($currently_installed as $package => $info) {
            extract($info); // state, version
            $conditions[] = "(package = '$package' AND state = '$state')";
        }
        $query .= implode(" OR ", $conditions) . ")";
        return $dbh->getAssoc($query, false, null, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{ +proto bool   package::updateInfo(string|int, struct)
    /**
     * Updates fields of an existant package
     *
     * @param int $pkgid The package ID to update
     * @param array $data Assoc in the form 'field' => 'value'.
     * @return mixed True or PEAR_Error
     */
    function updateInfo($pkgid, $data)
    {
        global $dbh, $auth_user;
        $package_id = package::info($pkgid, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("Package not registered. Please register it first with \"New Package\"");
        }
        if (empty($auth_user->admin)) {
            $role = user::maintains($auth_user->handle, $package_id);
            if ($role != 'lead' && $role != 'developer') {
                return PEAR::raiseError('package::updateInfo: insufficient privileges');
            }
        }
        // XXX (cox) what about 'name'?
        $allowed = array('license', 'summary', 'description', 'category');
        $fields = $prep = array();
        foreach ($allowed as $a) {
            if (isset($data[$a])) {
                $fields[] = "$a = ?";
                $prep[]   = $data[$a];
            }
        }
        if (!count($fields)) {
            return;
        }
        $sql = 'UPDATE packages SET ' . implode(', ', $fields) .
               " WHERE id=$package_id";
        return $dbh->query($sql, $prep);
    }

    // }}}
    // {{{ getDependants()

    /**
     * Get packages that depend on the given package
     *
     * @param  string Name of the package
     * @return array  List of package that depend on $package
     */
    function getDependants($package) {
        global $dbh;

        $query = "SELECT p.name AS p_name, d.* FROM deps d, packages p " .
            "WHERE d.package = p.id AND d.type = 'pkg' " .
            "      AND d.name = '" . $package . "' " .
            "GROUP BY d.package";
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{  proto array  package::getRecent(int, string)

    /**
     * Get list of recent releases for the given package
     *
     * @param  int Number of releases to return
     * @param  string Name of the package
     * @return array
     */
    function getRecent($n, $package)
    {
        global $dbh;
        $recent = array();

        $query = "SELECT p.id AS id, " .
            "p.name AS name, " .
            "p.summary AS summary, " .
            "r.version AS version, " .
            "r.releasedate AS releasedate, " .
            "r.releasenotes AS releasenotes, " .
            "r.doneby AS doneby, " .
            "r.state AS state " .
            "FROM packages p, releases r " .
            "WHERE p.id = r.package " .
            "AND p.package_type = 'pecl' AND p.approved = 1" . 
            "AND p.name = '" . $package . "'" .
            "ORDER BY r.releasedate DESC";

        $sth = $dbh->limitQuery($query, 0, $n);
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ *proto bool   package::isValid(string)

    /**
     * Determines if the given package is valid
     *
     * @access public
     * @param  string Name of the package
     * @return  boolean
     */
    function isValid($package)
    {
        global $dbh;
         $query = "SELECT id FROM packages WHERE package_type = 'pecl' AND approved = 1 AND name = ?";
        $sth = $dbh->query($query, array($package));
        return ($sth->numRows() > 0);
    }

    // }}}
}

/**
 * Class to handle maintainers
 *
 * @class   maintainer
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class maintainer
{
    // {{{ +proto int    maintainer::add(int|string, string, string)

    /**
     * Add new maintainer
     *
     * @static
     * @param  mixed  Name of the package or it's ID
     * @param  string Handle of the user
     * @param  string Role of the user
     * @return mixed True or PEAR error object
     */
    function add($package, $user, $role)
    {
        global $dbh, $auth_user;
        /*
        That breaks package-new.php, as the new package do not have any lead assigned
        if (empty($auth_user->admin) && !user::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('maintainer::add: insufficient privileges');
        }*/
        if (!user::exists($user)) {
            return PEAR::raiseError("User $user does not exist");
        }
        if (is_string($package)) {
            $package = package::info($package, 'id');
        }
        $err = $dbh->query("INSERT INTO maintains VALUES(?,?,?,1)",
                           array($user, $package, $role));
        if (DB::isError($err)) {
            return $err;
        }
        return true;
    }

    // }}}
    // {{{  proto struct maintainer::get(int|string, [bool])

    /**
     * Get maintainer(s) for package
     *
     * @static
     * @param  mixed Name of the package or it's ID
     * @param  boolean Only return lead maintainers?
     * @return array
     */
    function get($package, $lead = false)
    {
        global $dbh;
        if (is_string($package)) {
            $package = package::info($package, 'id');
        }
        $query = "SELECT handle, role FROM maintains WHERE package = '" . $package . "'";
        if ($lead) {
            $query .= " AND role = 'lead'";
        }
        return $dbh->getAssoc($query);
    }

    // }}}
    // {{{  proto struct maintainer::getByUser(string)

    /**
     * Get the roles of a specific user
     *
     * @static
     * @param  string Handle of the user
     * @return array
     */
    function getByUser($user)
    {
        global $dbh;
        $query = 'SELECT p.name, m.role FROM packages p, maintains m WHERE m.package = p.id AND m.handle = ?';
        return $dbh->getAssoc($query, false, array($user));
    }

    // }}}
    // {{{  proto bool   maintainer::isValidRole(string)

    /**
     * Check if role is valid
     *
     * @static
     * @param string Name of the role
     * @return boolean
     */
    function isValidRole($role)
    {
        static $roles;
        if (empty($roles)) {
            $roles = PEAR_Common::getUserRoles();
        }
        return in_array($role, $roles);
    }

    // }}}
    // {{{ +proto bool   maintainer::remove(int|string, string)

    /**
     * Remove user from package
     *
     * @static
     * @param  mixed Name of the package or it's ID
     * @param  string Handle of the user
     * @return True or PEAR error object
     */
    function remove($package, $user)
    {
        global $dbh, $auth_user;
        if (empty($auth_user->admin) && !user::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('maintainer::drop: insufficient privileges');
        }
        if (is_string($package)) {
            $package = package::info($package, 'id');
        }
        $sql = "DELETE FROM maintains WHERE package = ? AND handle = ?";
        return $dbh->query($sql, array($package, $user));
    }

    // }}}
    // {{{ +proto bool   maintainer::updateAll(int, array)

    /**
     * Update user and roles of a package
     *
     * @static
     * @param int $pkgid The package id to update
     * @param array $users Assoc array containing the list of users
     *                     in the form: '<user>' => '<role>'
     * @return mixed PEAR_Error or true
     */
    function updateAll($pkgid, $users)
    {
        // Only admins and leads can do this.
        global $dbh, $auth_user;
        if (empty($auth_user->admin) && !user::maintains($auth_user->handle, $pkgid, 'lead')) {
            return PEAR::raiseError('maintainer::updateAll: insufficient privileges');
        }
        $sql = "SELECT handle, role FROM maintains WHERE package = ?";
        $old = $dbh->getAssoc($sql, false, array($pkgid));
        if (DB::isError($old)) {
            return $old;
        }
        $old_users = array_keys($old);
        $new_users = array_keys($users);
        if (!$auth_user->admin && !in_array($auth_user->handle, $new_users)) {
            return PEAR::raiseError("You can not delete your own maintainer role or you will not ".
                                    "be able to complete the update process. Set your name ".
                                    "in package.xml or let the new lead developer upload ".
                                    "the new release");
        }
        foreach ($users as $user => $role) {
            if (!maintainer::isValidRole($role)) {
                return PEAR::raiseError("invalid role '$role' for user '$user'");
            }
            // The user is not present -> add him
            if (!in_array($user, $old_users)) {
                $e = maintainer::add($pkgid, $user, $role);
                if (PEAR::isError($e)) {
                    return $e;
                }
                continue;
            }
            // Users exists but role has changed -> update it
            if ($role != $old[$user]) {
                $sql = "UPDATE maintains SET role=? WHERE package=? AND handle=?";
                $res = $dbh->query($sql, array($role, $pkgid, $user));
                if (DB::isError($res)) {
                    return $res;
                }
            }
        }
        // Drop users who are no longer maintainers
        foreach ($old_users as $old_user) {
            if (!in_array($old_user, $new_users)) {
                $sql = "DELETE FROM maintains WHERE package=? AND handle=?";
                $res = $dbh->query($sql, array($pkgid, $old_user));
                if (DB::isError($res)) {
                    return $res;
                }
            }
        }
        return true;
    }

    // }}}
}

/**
 * Class to handle releases
 *
 * @class   release
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class release
{
    // {{{  proto array  release::getRecent([int])

    /**
     * Get recent releases
     *
     * @static
     * @param  integer Number of releases to return
     * @return array
     */
    function getRecent($n = 5)
    {
        global $dbh;
        $sth = $dbh->limitQuery("SELECT packages.id AS id, ".
                                "packages.name AS name, ".
                                "packages.summary AS summary, ".
                                "releases.version AS version, ".
                                "releases.releasedate AS releasedate, ".
                                "releases.releasenotes AS releasenotes, ".
                                "releases.doneby AS doneby, ".
                                "releases.state AS state ".
                                "FROM packages, releases ".
                                "WHERE packages.id = releases.package ".
            			"AND packages.package_type = 'pecl' " . 
                                "ORDER BY releases.releasedate DESC", 0, $n);
        $recent = array();
        // XXX Fixme when DB gets limited getAll()
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{  proto array  release::getDateRange(int,int)

    /**
     * Get release in a specific time range
     *
     * @static
     * @param integer Timestamp of start date
     * @param integer Timestamp of end date
     * @return array
     */
    function getDateRange($start,$end)
    {
        global $dbh;

        $recent = array();
        if (!is_numeric($start)) {
            return $recent;
        }
        if (!is_numeric($end)) {
            return $recent;
        }
        $start_f = date('Y-m-d 00:00:00',$start);
        $end_f = date('Y-m-d 00:00:00',$end);
        // limited to 50 to stop overkill on the server!
        $sth = $dbh->limitQuery("SELECT packages.id AS id, ".
                                "packages.name AS name, ".
                                "packages.summary AS summary, ".
                                "packages.description AS description, ".
                                "releases.version AS version, ".
                                "releases.releasedate AS releasedate, ".
                                "releases.releasenotes AS releasenotes, ".
                                "releases.doneby AS doneby, ".
                                "releases.state AS state ".
                                "FROM packages, releases ".
                                "WHERE packages.id = releases.package ".
                                "AND releases.releasedate > '{$start_f}' AND releases.releasedate < '{$end_f}'".
                                "ORDER BY releases.releasedate DESC",0,50);

        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ +proto string release::upload(string, string, string, string, binary, string)

    /**
     * Upload new release
     *
     * @static
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     */
    function upload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $auth_user;
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && $role != 'developer' && !$auth_user->admin) {
            return PEAR::raiseError('release::upload: insufficient privileges');
        }
        $ref = release::validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum);
        if (PEAR::isError($ref)) {
            return $ref;
        }
        return release::confirmUpload($ref);
    }

    // }}}
    // {{{ +proto string release::validateUpload(string, string, string, string, binary, string)

    /**
     * Determine if uploaded file is a valid release
     *
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     * @return mixed
     */
    function validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $dbh, $auth_user;
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && $role != 'developer' && !$auth_user->admin) {
            return PEAR::raiseError('release::validateUpload: insufficient privileges');
        }
        // (2) verify that package exists
        $package_id = package::info($package, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("package `$package' must be registered first");
        }

        // (3) verify that version does not exist
        $test = $dbh->getOne("SELECT version FROM releases ".
                             "WHERE package = ? AND version = ?",
                             array($package_id, $version));
        if (PEAR::isError($test)) {
            return $test;
        }
        if ($test) {
            return PEAR::raiseError("already exists: $package $version");
        }

        // (4) store tar ball to temp file
        $tempfile = sprintf("%s/%s%s-%s.tgz",
                            PEAR_TARBALL_DIR, ".new.", $package, $version);
        $file = sprintf("%s/%s-%s.tgz", PEAR_TARBALL_DIR, $package, $version);
        if (!@copy($tarball, $tempfile)) {
            return PEAR::raiseError("writing $tempfile failed: $php_errormsg");
        }

        if (!isset($package_id)) {
            return PEAR::raiseError("bad upload: package_id missing");
        }

        // later: do lots of integrity checks on the tarball
        if (!@rename($tempfile, $file)) {
            return PEAR::raiseError("renaming failed: $php_errormsg");
        }

        // (5) verify MD5 checksum
        $testsum = md5_file($file);
        if ($testsum != $md5sum) {
            $bytes = strlen($data);
            return PEAR::raiseError("bad md5 checksum (checksum=$testsum ($bytes bytes: $data), specified=$md5sum)");
        }

        $info = array("package_id" => $package_id,
                      "package" => $package,
                      "version" => $version,
                      "state" => $state,
                      "relnotes" => $relnotes,
                      "md5sum" => $md5sum,
                      "file" => $file);
        $infofile = sprintf("%s/%s%s-%s",
                            PEAR_TARBALL_DIR, ".info.", $package, $version);
        $fp = @fopen($infofile, "w");
        if (!is_resource($fp)) {
            return PEAR::raiseError("writing $infofile failed: $php_errormsg");
        }
        fwrite($fp, serialize($info));
        fclose($fp);
        return $infofile;
    }

    // }}}
    // {{{ +proto bool   release::confirmUpload(string)

    /**
     * Confirm release upload
     *
     * @static
     * @param string Reference to upload file (?)
     * @return boolean
     */
    function confirmUpload($upload_ref)
    {
        global $dbh, $auth_user;
        $fp = @fopen($upload_ref, "r");
        if (!is_resource($fp)) {
            return PEAR::raiseError("invalid upload reference: $upload_ref");
        }
        $info = unserialize(fread($fp, filesize($upload_ref)));
        extract($info);
        @unlink($upload_ref);

        $role = user::maintains($auth_user->handle, $package_id);
        if ($role != 'lead' && $role != 'developer' && !$auth_user->admin) {
            return PEAR::raiseError('release::confirmUpload: insufficient privileges');
        }

        // Update releases table
        $query = "INSERT INTO releases (id,package,version,state,doneby,".
             "releasedate,releasenotes) VALUES(?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $release_id = $dbh->nextId("releases");
        $dbh->execute($sth, array($release_id, $package_id, $version, $state,
                                  $_COOKIE['PEAR_USER'], gmdate('Y-m-d H:i'),
                                  $relnotes));
        // Update files table
        $query = "INSERT INTO files ".
             "(id,package,release,md5sum,basename,fullpath) ".
             "VALUES(?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $file_id = $dbh->nextId("files");
        $ok = $dbh->execute($sth, array($file_id, $package_id, $release_id,
                                        $md5sum, basename($file), $file));

        // Update dependency table
        $query = "INSERT INTO deps " .
            "(package, release, type, relation, version, name) " .
            "VALUES (?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);

        /**
         * The dependencies are only accessible via the package
         * definition. Because of this we need to instantiate
         * a PEAR_Common object here.
         */
        $common = new PEAR_Common();
        $pkg_info = $common->InfoFromTgzFile($info['file']);

        foreach ($pkg_info as $key => $value) {
            if ($key == "release_deps") {
                foreach ($value as $dep) {
                    $dbh->execute($sth, array($package_id, $release_id,
                                              @$dep['type'], @$dep['rel'],
                                              @$dep['version'], @$dep['name'])
                                  );
                }
            }
        }

        if (PEAR::isError($ok)) {
            $dbh->query("DELETE FROM releases WHERE id = $release_id");
            @unlink($file);
            return $ok;
        };

        // Update Cache
        include_once 'xmlrpc-cache.php';
        $cache = new XMLRPC_Cache;
        $cache->remove('package.listAll', array(false));
        $cache->remove('package.listAll', array(true));
        $cache->remove('package.info', array($package, null));

        return $file;
    }

    // }}}
    // {{{ +proto bool   release::dismissUpload(string)

    /**
     * Dismiss release upload
     *
     * @param string
     * @return boolean
     */
    function dismissUpload($upload_ref)
    {
        return (bool)@unlink($upload_ref);
    }

    // }}}
    // {{{ NOEXPORT      release::HTTPdownload(string, [string], [string], [bool])

    /**
     * Download release via HTTP
     *
     * Not for xmlrpc export!
     *
     * @param string Name of the package
     * @param string Version string
     * @param string Filename
     * @param boolean Uncompress file before downloading?
     * @return mixed
     */
    function HTTPdownload($package, $version = null, $file = null, $uncompress = false)
    {
        global $dbh;
        $package_id = package::info($package, 'packageid');

        if (!$package_id) {
            return PEAR::raiseError("release download:: package '".htmlspecialchars($package).
                                    "' does not exist");
        } elseif (PEAR::isError($package_id)) {
            return $package_id;
        }

        if ($file !== null) {
            if (substr($file, -4) == '.tar') {
                $file = substr($file, 0, -4) . '.tgz';
                $uncompress = true;
            }
            $row = $dbh->getRow("SELECT fullpath, release, id FROM files ".
                                "WHERE UPPER(basename) = ?", array(strtoupper($file)),
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            } elseif ($row === null) {
                return $this->raiseError("File '$file' not found");
            }
            $path = $row['fullpath'];
            $log_release = $row['release'];
            $log_file = $row['id'];
            $basename = $file;
        } elseif ($version == null) {
            // Get the most recent version
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "ORDER BY releasedate DESC", DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        } elseif (release::isValidState($version)) {
            $version = strtolower($version);
            // Get the most recent version with a given state
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "AND state = '$version' ".
                                "ORDER BY releasedate DESC",
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
            if (!isset($release_id)) {
                return PEAR::raiseError("$package does not have any releases with state \"$version\"");
            }
        } else {
            // Get a specific release
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "AND version = '$version'",
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        }
        if (!isset($path) && isset($release_id)) {
            $sql = "SELECT fullpath, basename, id FROM files WHERE release = ".
                 $release_id;
            $row = $dbh->getRow($sql, DB_FETCHMODE_ORDERED);
            if (PEAR::isError($row)) {
                return $row;
            }

            list($path, $basename, $log_file) = $row;
            if (empty($path) || !@is_file($path)) {
                return PEAR::raiseError("release download:: no version information found");
            }
        }
        if (isset($path)) {
            if (!isset($log_release)) {
                $log_release = $release_id;
            }

            release::logDownload($package_id, $log_release, $log_file);

            header('Last-modified: '.HTTP::date(filemtime($path)));
            header('Content-type: application/octet-stream');
            if ($uncompress) {
                $tarname = preg_replace('/\.tgz$/', '.tar', $basename);
                header('Content-disposition: attachment; filename="'.$tarname.'"');
                readgzfile($path);
            } else {
                header('Content-disposition: attachment; filename="'.$basename.'"');
                header('Content-length: '.filesize($path));
                readfile($path);
            }

            return true;
        }
        header('HTTP/1.0 404 Not Found');
        print 'File not found';
    }

    // }}}
    // {{{  proto bool   release::isValidState(string)

    /**
     * Determine if release state is valid
     *
     * @static
     * @param string State
     * @return boolean
     */
    function isValidState($state)
    {
        static $states = array('devel', 'snapshot', 'alpha', 'beta', 'stable');
        return in_array($state, $states);
    }

    // }}}
    // {{{  proto array  release::betterStates(string)

    /**
     * ???
     *
     * @param string Release state
     * @return boolean
     */
    function betterStates($state)
    {
        static $states = array('snapshot', 'devel', 'alpha', 'beta', 'stable');
        $i = array_search($state, $states);
        if ($i === false) {
            return false;
        }
        return array_slice($states, $i + 1);
    }

    // }}}
    // {{{ NOEXPORT      release::logDownload(integer, string, string)

    /**
     * Log release download
     *
     * @param integer ID of the package
     * @param string Version string of the release
     * @param string Filename
     * @return boolean
     */
    function logDownload($package, $release_id, $file = null)
    {
        global $dbh;

        $id = $dbh->nextId("downloads");

        $query = "INSERT INTO downloads (id, file, package, release, dl_when, dl_who, dl_host) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sth = $dbh->prepare($query);

        if (DB::isError($sth)) {
            return false;
        }

        $err = $dbh->execute($sth, array($id, $file, $package,
                                         $release_id, date("Y-m-d H:i:s"),
                                         $_SERVER['REMOTE_ADDR'],
                                         gethostbyaddr($_SERVER['REMOTE_ADDR'])
                                        ));

        if (DB::isError($err)) {
            return false;
        } else {
            return true;
        }
    }

    // }}}
    // {{{ +proto string release::promote(array, string)

    /**
     * Promote new release
     *
     * @param array Coming from PEAR_common::infoFromDescFile('package.xml')
     * @param string Filename of the new uploaded release
     * @return void
     */
    function promote($pkginfo, $upload)
    {
        $pacid   = package::info($pkginfo['package'], 'packageid');
        $authors = package::info($pkginfo['package'], 'authors');
        $txt_authors = '';
        foreach ($authors as $a) {
            $txt_authors .= $a['name'];
            if ($a['showemail']) {
                $txt_authors .= " <{$a['email']}>";
            }
            $txt_authors .= " ({$a['role']})\n";
        }
        $upload = basename($upload);
        $release = "{$pkginfo['package']}-{$pkginfo['version']} ({$pkginfo['release_state']})";
        $txtanounce =<<<END
The new PECL package $release has been released at http://pecl.php.net/.

Release notes
-------------
{$pkginfo['release_notes']}

Package Info
-------------
{$pkginfo['description']}

Related Links
-------------
Package home: http://pecl.php.net/package/$pkginfo[package]
   Changelog: http://pecl.php.net/package-changelog.php?package=$pkginfo[package]
    Download: http://pecl.php.net/get/$upload

Authors
-------------
$txt_authors
END;
        $to   = '"PECL developers list" <pecl-dev@lists.php.net>';
        $from = '"PECL Announce" <pecl-dev@lists.php.net>';
        $subject = "[ANNOUNCEMENT] $release Released.";
        mail($to, $subject, $txtanounce, "From: $from", "-f pear-sys@php.net");
    }

    // }}}
    // {{{ NOEXPORT      release::remove(int, int)

    /**
     * Remove release
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @return boolean
     */
    function remove($package, $release)
    {
        global $dbh, $auth_user;
        if (empty($auth_user->admin) &&
            !user::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('release::remove: insufficient privileges');
        }

        $success = true;

        // get files that have to be removed
        $query = sprintf("SELECT fullpath FROM files WHERE package = '%s' AND release = '%s'",
                         $package,
                         $release);

        $sth = $dbh->query($query);

        while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
            if (!@unlink($row['fullpath'])) {
                $success = false;
            }
        }

        $query = sprintf("DELETE FROM files WHERE package = '%s' AND release = '%s'",
                         $package,
                         $release
                         );
        $sth = $dbh->query($query);

        $query = sprintf("DELETE FROM releases WHERE package = '%s' AND id = '%s'",
                         $package,
                         $release
                         );
        $sth = $dbh->query($query);

        if (PEAR::isError($sth)) {
            return false;
        } else {
            return true;
        }
    }

    // }}}
}


/**
 * Class to handle notes
 *
 * @class   note
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class note
{
    // {{{ +proto bool   note::add(string, int, string)

    function add($key, $value, $note)
    {
        global $dbh;
        $nid = $dbh->nextId("notes");
        $stmt = $dbh->prepare("INSERT INTO notes (id,$key,nby,ntime,note) ".
                              "VALUES(?,?,?,?,?)");
        $res = $dbh->execute($stmt, array($nid, $value, $_COOKIE['PEAR_USER'],
                             gmdate('Y-m-d H:i'), $note));
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    // }}}
    // {{{ +proto bool   note::remove(int)

    function remove($id)
    {
        global $dbh;
        $id = (int)$id;
        $res = $dbh->query("DELETE FROM notes WHERE id = $id");
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    // }}}
    // {{{ +proto bool   note::removeAll(string, int)

    function removeAll($key, $value)
    {
        global $dbh;
        $res = $dbh->query("DELETE FROM notes WHERE $key = ". $dbh->quote($value));
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    // }}}
}

class user
{
    // {{{ *proto bool   user::remove(string)

    function remove($uid)
    {
        global $dbh;
        note::removeAll("uid", $uid);
        $dbh->query('DELETE FROM users WHERE handle = '. $dbh->quote($uid));
        return ($dbh->affectedRows() > 0);
    }

    // }}}
    // {{{ *proto bool   user::rejectRequest(string, string)

    function rejectRequest($uid, $reason)
    {
        global $dbh;
        list($email) = $dbh->getRow('SELECT email FROM users WHERE handle = ?',
                                    array($uid));
        note::add("uid", $uid, "Account rejected: $reason");
        $msg = "Your PECL account request was rejected by " . $_COOKIE['PEAR_USER'] . ":\n".
             "$reason\n";
        $xhdr = "From: " . $_COOKIE['PEAR_USER'] . "@php.net";
        mail($email, "Your PECL Account Request", $msg, $xhdr, "-f pear-sys@php.net");
        return true;
    }

    // }}}
    // {{{ *proto bool   user::activate(string)

    function activate($uid)
    {
        global $dbh;

        $user =& new PEAR_User($dbh, $uid);
        if (@$user->registered) {
            return false;
        }
        @$arr = unserialize($user->userinfo);
        note::removeAll("uid", $uid);
        $user->set('registered', 1);
        /* $user->set('ppp_only', 0); */
        if (is_array($arr)) {
            $user->set('userinfo', $arr[1]);
        }
        $user->set('created', gmdate('Y-m-d H:i'));
        $user->set('createdby', $_COOKIE['PEAR_USER']);
        $user->store();
        note::add("uid", $uid, "Account opened");
        $msg = "Your PECL/PEAR account request has been opened.\n".
             "To log in, go to http://pecl.php.net/ and click on \"login\" in\n".
             "the top-right menu.\n";
        $xhdr = "From: " . $_COOKIE['PEAR_USER'] . "@php.net";
        mail($user->email, "Your PECL Account Request", $msg, $xhdr, "-f pear-sys@php.net");
        return true;
    }

    // }}}
    // {{{ +proto bool   user::isAdmin(string)

    function isAdmin($handle)
    {
        global $dbh;

        $query = "SELECT handle FROM users WHERE handle = ? AND admin = 1";
        $sth = $dbh->query($query, array($handle));

        return ($sth->numRows() > 0);
    }

    // }}}
    // {{{  proto bool   user::listAdmins()

    function listAdmins()
    {
        global $dbh;

        $query = "SELECT email FROM users WHERE admin = 1";
        return $dbh->getCol($query);
    }

    // }}}
    // {{{ +proto bool   user::exists(string)

    function exists($handle)
    {
        global $dbh;
        $sql = "SELECT handle FROM users WHERE handle=?";
        $res = $dbh->query($sql, array($handle));
        return ($res->numRows() > 0);
    }

    // }}}
    // {{{ +proto string user::maintains(string|int, [string])

    function maintains($user, $pkgid, $role = 'any')
    {
        global $dbh;
        $package_id = package::info($pkgid, 'id');
        if ($role == 'any') {
            return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? '.
                                'AND package = ?', array($user, $package_id));
        }
        if (is_array($role)) {
            return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                                'AND role IN ("?")', array($user, $package_id, implode('","', $role)));
        }
        return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                            'AND role = ?', array($user, $package_id, $role));
    }

    // }}}
    // {{{  proto string user::info(string, [string])

    function info($user, $field = null)
    {
        global $dbh;
        if ($field === null) {
            return $dbh->getRow('SELECT * FROM users WHERE handle = ?',
                                array($user), DB_FETCHMODE_ASSOC);
        }
        if (preg_match('/[^a-z]/', $user)) {
            return null;
        }
        return $dbh->getRow('SELECT ! FROM users WHERE handle = ?',
                            array($field, $user), DB_FETCHMODE_ASSOC);

    }

    // }}}
    // {{{ listAll()

    function listAll($registered_only = true)
    {
        global $dbh;
        $query = "SELECT * FROM users";
        if ($registered_only === true) {
            $query .= " WHERE registered = 1";
        }
        $query .= " ORDER BY handle";
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    // }}}
}

class statistics
{
    // {{{ package()

    /**
     * Get general package statistics
     *
     * @param  integer ID of the package
     * @return array
     */
    function package($id)
    {
        global $dbh;
        $query = "SELECT COUNT(*) AS total FROM downloads WHERE package = '" . $id . "'";
        return $dbh->getOne($query);
    }

    // }}}
    // {{{ release()

    function release($id, $rid = '')
    {
        global $dbh;

        $query = 'SELECT s.release, s.dl_number, s.last_dl, r.releasedate '
            . 'FROM package_stats AS s '
            . 'LEFT JOIN releases AS r ON (s.rid = r.id) '
            . "WHERE pid = " . (int)$id;
        if (!empty($rid)) {
            $query .= " AND rid = " . (int)$rid;
        }
        $query .= " GROUP BY rid ORDER BY rid DESC";

        return $dbh->getAll($query, DB_FETCHMODE_ASSOC);
    }

    // }}}
}

// {{{ +proto string logintest()

function logintest()
{
    return true;
}

// }}}

// {{{ mail_pear_admins()

function mail_pear_admins($subject = "PEAR Account Request", $msg, $xhdr = '')
{
    global $dbh;
    $admins = $dbh->getAll("SELECT name,email FROM users WHERE admin = 1",
                           DB_FETCHMODE_ASSOC);
    if (count($admins) > 0) {
        foreach ($admins as $value) {
            if ($value['name'] == "") {
                $rcpt[] = "<" . $value['email'] . ">";
            } else {
                $rcpt[] = "\"" . $value['name'] . "\" <" . $value['email'] . ">";
            }
        }
        $rcpt = implode(", ", $rcpt);
        return mail($rcpt, $subject, $msg, $xhdr, "-f pear-sys@php.net");
    }
    return false;
}

// }}}

// {{{ class PEAR_User

class PEAR_User extends DB_storage
{
    function PEAR_User(&$dbh, $user)
    {
        $this->DB_storage("users", "handle", $dbh);
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->setup($user);
        $this->popErrorHandling();
    }

    function is($handle)
    {
        if (!empty($_COOKIE['PEAR_USER'])) {
            $ret = strtolower($_COOKIE['PEAR_USER']);
        } else {
            $ret = strtolower($this->handle);
        }
        return (strtolower($handle) == $ret);
    }

    function isAdmin()
    {
        return ($this->admin == 1);
    }
}

// }}}
// {{{ class PEAR_Package

class PEAR_Package extends DB_storage
{
    function PEAR_Package(&$dbh, $package, $keycol = "id")
    {
        $this->DB_storage("packages", $keycol, $dbh);
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->setup($package);
        $this->popErrorHandling();
    }
}

// }}}
// {{{ class PEAR_Release

class PEAR_Release extends DB_storage
{
    function PEAR_Release(&$dbh, $release)
    {
        $this->DB_storage("releases", "id", $dbh);
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->setup($release);
        $this->popErrorHandling();
    }
}

// }}}
// {{{ class PEAR Proposal
/*
class PEAR_Proposal extends DB_storage
{
    function PEAR_Proposal(&$dbh, $package, $keycol = "id")
    {
        $this->DB_storage("package_proposals", $keycol, $dbh);
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->setup($package);
        $this->popErrorHandling();
    }
}
*/
// }}}

if (!function_exists("md5_file")) {
    function md5_file($filename) {
        $fp = @fopen($filename, "r");
        if (is_resource($fp)) {
            return md5(fread($fp, filesize($filename)));
        }
        return null;
    }
}

/**
 * Converts a Unix timestamp to a date() formatted string in the UTC time zone
 *
 * @param int    $ts      a Unix timestamp from the local machine.  If none
 *                         is provided the current time is used.
 * @param string $format  a format string, as per http://php.net/date
 *
 * @return string  the time formatted time
 */
function make_utc_date($ts = null, $format = 'Y-m-d H:i \U\T\C') {
    if (!$ts) {
        $ts = time();
    }
    return gmdate($format, $ts);
}

?>
