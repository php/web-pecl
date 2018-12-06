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
use App\Entity\Maintainer;
use App\Rest;
use App\User;
use \PEAR as PEAR;

/**
 * Class to handle packages.
 */
class Package
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
     * Set REST generator.
     */
    public function setRest(Rest $rest)
    {
        $this->rest = $rest;
    }

    /**
     * Add new package
     *
     * @param array
     * @return mixed ID of new package or PEAR error object
     */
    public function add($data)
    {
        global $auth_user;

        $name = isset($data['name']) ? $data['name'] : null;
        $category = isset($data['category']) ? $data['category'] : null;
        $license = isset($data['license']) ? $data['license'] : null;
        $summary = isset($data['summary']) ? $data['summary'] : null;
        $description = isset($data['description']) ? $data['description'] : null;
        $lead = isset($data['lead']) ? $data['lead'] : null;
        $type = isset($data['type']) ? $data['type'] : null;
        $homepage = isset($data['homepage']) ? $data['homepage'] : null;
        $cvs_link = isset($data['cvs_link']) ? $data['cvs_link'] : null;

        if (empty($license)) {
            $license = "PHP License";
        }

        if (!empty($category) && (int)$category == 0) {
            $category = $this->database->run("SELECT id FROM categories WHERE name = ?", [$category])->fetch()['id'];
        }

        if (empty($category)) {
            return PEAR::raiseError("Package::add: invalid `category' field");
        }

        if (empty($name)) {
            return PEAR::raiseError("Package::add: invalid `name' field");
        }

        // NOTE WELL! PECL packages are always approved
        $sql = "INSERT INTO packages
                    (id, name, package_type, category, license, summary, description, homepage, cvs_link, approved)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

        $id = $this->database->run("SELECT id FROM packages ORDER by id DESC")->fetch()['id'];
        $id = !$id ? 1 : $id + 1;

        $result = $this->database->run($sql, [$id, $name, $type, $category, $license, $summary, $description, $homepage, $cvs_link]);

        if (!$result) {
            return PEAR::raiseError('Package::add: Error when adding package');
        }

        $sql = "UPDATE categories SET npackages = npackages + 1 WHERE id = ?";

        $result = $this->database->run($sql, [$category]);

        if (!$result) {
            return PEAR::raiseError('Package::add: Error when updating categories');
        }

        $this->rest->savePackage($name);

        $maintainer = new Maintainer();
        $maintainer->setDatabase($this->database);
        $maintainer->setRest($this->rest);
        $maintainer->setAuthUser($auth_user);
        $maintainer->setPackage($this);

        if (isset($lead) && !$maintainer->add($id, $lead, 'lead')) {
            return PEAR::raiseError("Error with adding lead to the project");
        }

        $this->rest->saveAllPackages();
        $this->rest->savePackagesCategory($this->info($name, 'category'));

        return $id;
    }

    /**
     * Get package information. Implemented $field values:
     * releases, notes, category, description, authors, categoryid, packageid,
     * authors.
     *
     * @param  mixed   Name of the package or it's ID
     * @param  string  Single field to fetch
     * @return mixed
     */
    public function info($pkg, $field = null)
    {
        if (is_numeric($pkg)) {
            $what = "id";
        } else {
            $what = "name";
        }

        $package_type = "p.package_type = 'pecl' AND ";

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
             "description, releasedate, releasenotes, state " .
             "FROM releases ".
             "WHERE package = ? ".
             "ORDER BY releasedate DESC";
        $notes_sql = "SELECT id, nby, ntime, note FROM notes WHERE pid = ?";
        $deps_sql = "SELECT type, relation, version, `name`, `release`, optional
                     FROM deps
                     WHERE `package` = ? ORDER BY `optional` ASC";

        if ($field === null) {
            $info = $this->database->run($pkg_sql, [$pkg])->fetch();

            $info['releases'] = $this->database->run($rel_sql, [$info['packageid']])->fetchAll();
            $results = [];
            foreach ($info['releases'] as $item) {
                $results[$item['version']] = $item;
            }
            $info['releases'] = $results;

            $rels = count($info['releases']) ? array_keys($info['releases']) : [''];
            $info['stable'] = $rels[0];
            $info['notes'] = $this->database->run($notes_sql, [@$info['packageid']])->fetchAll();
            $results = [];
            foreach ($info['notes'] as $item) {
                $results[$item['id']] = $item;
            }
            $info['notes'] = $results;

            $deps = $this->database->run($deps_sql, [@$info['packageid']])->fetchAll();
            foreach($deps as $dep) {
                $rel_version = null;
                foreach($info['releases'] as $version => $rel) {
                    if ($rel['id'] == $dep['release']) {
                        $rel_version = $version;
                        break;
                    }
                }

                if ($rel_version !== null) {
                    unset($dep['release']);
                    $info['releases'][$rel_version]['deps'][] = $dep;
                }
            }
        } elseif (in_array($field, ['releases', 'notes'])) {
            if ($what == "name") {
                $pid = $this->database->run("SELECT p.id FROM packages p ".
                                    "WHERE " . $package_type . " p.name = ?", [$pkg])->fetch()['id'];
            } else {
                $pid = $pkg;
            }

            if ($field == 'releases') {
                $info = $this->database->run($rel_sql, [$pid])->fetchAll();
                $results = [];
                foreach ($info as $item) {
                    $results[$item['version']] = $item;
                }
                $info = $results;
            } elseif ($field == 'notes') {
                $info = $this->database->run($notes_sql, [$pid])->fetchAll();
                $results = [];
                foreach ($info as $item) {
                    $results[$item['id']] = $item;
                }
                $info = $results;
            }
        } elseif ($field === 'category') {
            $sql = "SELECT c.name FROM categories c, packages p ".
                    "WHERE c.id = p.category AND " . $package_type . " p.{$what} = ?";
            $info = $this->database->run($sql, [$pkg])->fetch()['name'];
        } elseif ($field === 'description') {
            $sql = "SELECT description FROM packages p WHERE " . $package_type . " p.{$what} = ?";
            $info = $this->database->run($sql, [$pkg])->fetch()['description'];
        } elseif ($field === 'authors') {
            $sql = "SELECT u.handle, u.name, u.email, u.showemail, m.active, m.role
                    FROM maintains m, users u, packages p
                    WHERE " . $package_type ." m.package = p.id
                    AND p.$what = ?
                    AND m.handle = u.handle";
            $info = $this->database->run($sql, [$pkg])->fetchAll();
        } else {
            if ($field == 'categoryid') {
                $dbfield = 'category';
            } elseif ($field == 'packageid') {
                $dbfield = 'id';
            } else {
                $dbfield = $field;
            }

            $sql = "SELECT $dbfield FROM packages p WHERE " . $package_type ." p.{$what} = ?";
            $info = $this->database->run($sql, [$pkg])->fetch()[$dbfield];
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
    public function updateInfo($pkgid, $data)
    {
        global $auth_user;

        $package_id = $this->info($pkgid, 'id');

        if (empty($package_id)) {
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

        $sql = 'UPDATE packages SET ' . implode(', ', $fields) . " WHERE id=$package_id";
        $row = $this->info($pkgid, 'name');

        $this->rest->saveAllPackages();
        $this->rest->savePackage($row);
        $this->rest->savePackagesCategory($this->info($pkgid, 'category'));

        return $this->database->run($sql, $prep);
    }

    /**
     * Get packages that depend on the given package
     *
     * @param  string Name of the package
     * @return array  List of package that depend on $package
     */
    public function getDependants($package) {
        $sql = "SELECT p.name AS p_name, d.*
                FROM deps d, packages p
                WHERE d.package = p.id AND d.type = 'pkg' AND d.name = ?
                GROUP BY d.package";

        return $this->database->run($sql, [$package])->fetchAll();
    }

    /**
     * Determines if the given package is valid
     *
     * @param  string Name of the package
     * @return  boolean
     */
    public function isValid($package)
    {
        $sql = "SELECT id FROM packages WHERE package_type = 'pecl' AND approved = 1 AND name = ?";
        $results = $this->database->run($sql, [$package])->fetchAll();

        return (count($results) > 0);
    }

    /**
     * Get all notes for given package
     *
     * @param  int ID of the package
     * @return array
     */
    public function getNotes($package)
    {
        $sql = 'SELECT * FROM notes WHERE pid = ? ORDER BY ntime';

        return $this->database->run($sql, [$package])->fetchAll();
    }
}
