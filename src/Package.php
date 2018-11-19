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

use App\Entity\Maintainer;
use App\User;
use \DB as DB;
use \PEAR as PEAR;

/**
 * Class to handle packages.
 */
class Package
{
    /**
     * Add new package
     *
     * @param array
     * @return mixed ID of new package or PEAR error object
     */
    public static function add($data)
    {
        global $database, $dbh, $rest, $auth_user;

        // name, category
        // license, summary, description
        // lead
        extract($data);

        if (empty($license)) {
            $license = "PHP License";
        }

        if (!empty($category) && (int)$category == 0) {
            $category = $dbh->getOne("SELECT id FROM categories WHERE name = ?", [$category]);
        }

        if (empty($category)) {
            return PEAR::raiseError("Package::add: invalid `category' field");
        }

        if (empty($name)) {
            return PEAR::raiseError("Package::add: invalid `name' field");
        }

        // NOTE WELL! PECL packages are always approved
        $query = "INSERT INTO packages (id,name,package_type,category,license,summary,description,homepage,cvs_link,approved) VALUES(?,?,?,?,?,?,?,?,?,1)";
        $id = $dbh->nextId("packages");
        $err = $dbh->query($query, [$id, $name, $type, $category, $license, $summary, $description, $homepage, $cvs_link]);

        if (DB::isError($err)) {
            return $err;
        }

        $sql = "UPDATE categories SET npackages = npackages + 1 WHERE id = $category";

        if (DB::isError($err = $dbh->query($sql))) {
            return $err;
        }

        $rest->savePackage($name);

        $maintainer = new Maintainer();
        $maintainer->setDatabase($database);
        $maintainer->setRest($rest);
        $maintainer->setAuthUser($auth_user);

        if (isset($lead) && !$maintainer->add($id, $lead, 'lead')) {
            return PEAR::raiseError("Error with adding lead to the project");
        }

        $rest->saveAllPackages();
        $rest->savePackagesCategory(self::info($name, 'category'));

        return $id;
    }

    /**
     * Get package information. Implemented $field values:
     * releases, notes, category, description, authors, categoryid, packageid,
     * authors.
     *
     * @param  mixed   Name of the package or it's ID
     * @param  string  Single field to fetch
     * @param  boolean Should PEAR packages also be taken into account?
     * @return mixed
     */
    public static function info($pkg, $field = null, $allow_pear = false)
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
             "p.doc_link as doc_link, ".
             "p.bug_link as bug_link, ".
             "p.unmaintained as unmaintained, ".
             "p.newpackagename as new_package, ".
             "p.newchannel as new_channel".
             " FROM packages p, categories c ".
             "WHERE " . $package_type . " c.id = p.category AND p.{$what} = ?";

        $rel_sql = "SELECT version, id, doneby, license, summary, ".
             "description, releasedate, releasenotes, state " . //, packagexmlversion ".
             "FROM releases ".
             "WHERE package = ? ".
             "ORDER BY releasedate DESC";
        $notes_sql = "SELECT id, nby, ntime, note FROM notes WHERE pid = ?";
        $deps_sql = "SELECT type, relation, version, `name`, `release`, optional
                     FROM deps
                     WHERE `package` = ? ORDER BY `optional` ASC";

        if ($field === null) {
            $info = $dbh->getRow($pkg_sql, [$pkg], DB_FETCHMODE_ASSOC);

            $info['releases'] =
                 $dbh->getAssoc($rel_sql, false, [$info['packageid']],
                 DB_FETCHMODE_ASSOC);
            $rels = count($info['releases']) ? array_keys($info['releases']) : [''];
            $info['stable'] = $rels[0];
            $info['notes'] =
                 $dbh->getAssoc($notes_sql, false, [@$info['packageid']],
                 DB_FETCHMODE_ASSOC);
            $deps =
                 $dbh->getAll($deps_sql, [@$info['packageid']],
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
                                        "WHERE " . $package_type . " p.name = ?", [$pkg]);
                } else {
                    $pid = $pkg;
                }

                if ($field == 'releases') {
                    $info = $dbh->getAssoc($rel_sql, false, [$pid],
                    DB_FETCHMODE_ASSOC);
                } elseif ($field == 'notes') {
                    $info = $dbh->getAssoc($notes_sql, false, [$pid],
                    DB_FETCHMODE_ASSOC);
                }

            } elseif ($field == 'category') {
                $sql = "SELECT c.name FROM categories c, packages p ".
                     "WHERE c.id = p.category AND " . $package_type . " p.{$what} = ?";
                $info = $dbh->getOne($sql, [$pkg]);
            } elseif ($field == 'description') {
                $sql = "SELECT description FROM packages p WHERE " . $package_type . " p.{$what} = ?";
                $info = $dbh->query($sql, [$pkg]);
            } elseif ($field == 'authors') {
                $sql = "SELECT u.handle, u.name, u.email, u.showemail, m.active, m.role
                        FROM maintains m, users u, packages p
                        WHERE " . $package_type ." m.package = p.id
                        AND p.$what = ?
                        AND m.handle = u.handle";
                $info = $dbh->getAll($sql, [$pkg], DB_FETCHMODE_ASSOC);
            } else {
                if ($field == 'categoryid') {
                    $dbfield = 'category';
                } elseif ($field == 'packageid') {
                    $dbfield = 'id';
                } else {
                    $dbfield = $field;
                }

                $sql = "SELECT $dbfield FROM packages p WHERE " . $package_type ." p.{$what} = ?";
                $info = $dbh->getOne($sql, [$pkg]);
            }
        }

        return $info;
    }

    /**
     * Updates fields of an existent package
     *
     * @param int $pkgid The package ID to update
     * @param array $data Assoc in the form 'field' => 'value'.
     * @return mixed True or PEAR_Error
     */
    public static function updateInfo($pkgid, $data)
    {
        global $dbh, $auth_user, $rest;

        $package_id = self::info($pkgid, 'id');

        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("Package not registered. Please register it first with \"New Package\"");
        }

        if ($auth_user->isAdmin() == false) {
            $role = User::maintains($auth_user->handle, $package_id);
            if ($role != 'lead' && $role != 'developer') {
                return PEAR::raiseError('Package::updateInfo: insufficient privileges');
            }
        }

        // XXX (cox) what about 'name'?
        $allowed = ['license', 'summary', 'description', 'category'];
        $fields = $prep = [];
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
        $row = self::info($pkgid, 'name');

        $rest->saveAllPackages();
        $rest->savePackage($row);
        $rest->savePackagesCategory(self::info($pkgid, 'category'));

        return $dbh->query($sql, $prep);
    }

    /**
     * Get packages that depend on the given package
     *
     * @param  string Name of the package
     * @return array  List of package that depend on $package
     */
    public static function getDependants($package) {
        global $dbh;

        $query = "SELECT p.name AS p_name, d.* FROM deps d, packages p " .
            "WHERE d.package = p.id AND d.type = 'pkg' " .
            "      AND d.name = '" . $package . "' " .
            "GROUP BY d.package";

        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    /**
     * Determines if the given package is valid
     *
     * @param  string Name of the package
     * @return  boolean
     */
    public static function isValid($package)
    {
        global $dbh;

        $query = "SELECT id FROM packages WHERE package_type = 'pecl' AND approved = 1 AND name = ?";
        $sth = $dbh->query($query, [$package]);

        return ($sth->numRows() > 0);
    }

    /**
     * Get all notes for given package
     *
     * @param  int ID of the package
     * @return array
     */
    public function getNotes($package)
    {
        global $dbh;

        $query = 'SELECT * FROM notes WHERE pid = ? ORDER BY ntime';

        return $dbh->getAll($query, [$package], DB_FETCHMODE_ASSOC);
    }
}
