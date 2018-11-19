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
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Repository;

use App\Database;

/**
 * Repository class for packages table.
 */
class PackageRepository
{
    /**
     * Class constructor.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Find all PECL packages. If category ID is provided it gets packages by
     * category.
     */
    public function findAllByCategory($categoryId = null)
    {
        $sql = "SELECT id, name FROM packages WHERE packages.package_type = 'pecl'";

        $arguments = [];

        if (!empty($categoryId)) {
            $sql .= " AND category = :category_id";
            $arguments[':category_id'] = $categoryId;
        }

        $sql .= " ORDER BY name";

        $statement = $this->database->run($sql, $arguments);

        return $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * Return a list of packages by category name
     */
    public function findAllByCategoryName($categoryName)
    {
        $sql = 'SELECT p.id, p.name
                FROM packages p, categories c
                WHERE p.category = c.id AND c.name = ?';

        return $this->database->run($sql, [$categoryName])->fetchAll();
    }

    /**
     * Get all packages maintained by given username.
     */
    public function findPackagesMaintainedByHandle($handle)
    {
        $sql = "SELECT p.id, p.name, m.role
                FROM packages p, maintains m
                WHERE m.handle = :handle
                AND p.id = m.package
                AND p.package_type = 'pecl'
                ORDER BY p.name";

        return $this->database->run($sql, [':handle' => $handle])->fetchAll();
    }

    /**
     * Get all maintainers by given package id.
     */
    public function getMaintainersByPackageId($id)
    {
        $sql = "SELECT u.handle
                FROM maintains m, users u
                WHERE m.package = :package_id
                AND m.handle = u.handle";

        $statement = $this->database->run($sql, [':package_id' => $id]);

        return $statement->fetchAll();
    }

    /**
     * Lists the IDs and names of all approved PECL packages
     *
     * Returns an associative array where the key of each element is
     * a package ID, while the value is the name of the corresponding
     * package.
     *
     * @return array
     */
    public function findAllPeclPackages()
    {
        $sql = "SELECT id, name FROM packages WHERE package_type = 'pecl' ORDER BY name";

        return $this->database->run($sql)->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * List all packages for exporting to XML files.
     *
     * @param boolean Only list released packages?
     * @param boolean If listing released packages only, only list stable releases?
     * @param boolean List also PEAR packages
     * @return array
     */
    public function listAll()
    {
        $sql = "SELECT p.name, p.id AS packageid,
                    c.id AS categoryid,
                    c.name AS category,
                    p.license AS license,
                    p.summary AS summary,
                    p.description AS description,
                    m.handle AS lead
                FROM packages p, categories c, maintains m
                WHERE p.package_type = 'pecl'
                    AND p.approved = 1
                    AND c.id = p.category
                    AND p.id = m.package
                    AND m.role = 'lead'
                ORDER BY p.name";

        $packageinfo = $this->database->run($sql)->fetchAll();

        $results = [];
        foreach($packageinfo as $item) {
            // Reset package stability state
            $item['stable'] = false;

            $results[$item['name']] = $item;
        }
        $packageinfo = $results;

        $sql = "SELECT p.name, r.id AS rid,
                    r.version AS stable,
                    r.state AS state
                FROM packages p, releases r
                WHERE p.package_type = 'pecl'
                    AND p.approved = 1
                    AND p.id = r.package
                ORDER BY r.releasedate ASC ";

        $allreleases = $this->database->run($sql)->fetchAll();
        $results = [];
        foreach($allreleases as $item) {
            $results[$item['name']] = $item;
        }
        $allreleases = $results;

        $sql = "SELECT p.name, r.id AS rid,
                    r.version AS stable,
                    r.state AS state
                FROM packages p, releases r
                WHERE p.package_type = 'pecl'
                    AND p.approved = 1
                    AND p.id = r.package
                ORDER BY r.releasedate ASC";
        $stablereleases = $this->database->run($sql)->fetchAll();

        $results = [];
        foreach($stablereleases as $item) {
            $results[$item['name']] = $item;
        }
        $stablereleases = $results;

        $sql = "SELECT package, `release` , type, relation, version, name FROM deps";
        $deps = $this->database->run($sql)->fetchAll();

        foreach ($stablereleases as $pkg => $stable) {
            if (isset($packageinfo[$pkg])) {
                $packageinfo[$pkg]['stable'] = $stable['stable'];
                $packageinfo[$pkg]['unstable'] = false;
                $packageinfo[$pkg]['state']  = $stable['state'];
            }
        }

        foreach ($allreleases as $pkg => $stable) {
            if ($stable['state'] == 'stable') {
                if (version_compare($packageinfo[$pkg]['stable'], $stable['stable'], '<')) {
                    // Only change it if the version number is newer
                    $packageinfo[$pkg]['stable'] = $stable['stable'];
                }
            } else {
                if (!isset($packageinfo[$pkg]['unstable'])
                    || version_compare($packageinfo[$pkg]['unstable'], $stable['stable'], '<')
                ) {
                    // Only change it if the version number is newer
                    $packageinfo[$pkg]['unstable'] = $stable['stable'];
                }
            }

            $packageinfo[$pkg]['state']  = $stable['state'];

            if (isset($packageinfo[$pkg]['unstable']) && !$packageinfo[$pkg]['stable']) {
                $packageinfo[$pkg]['stable'] = $packageinfo[$pkg]['unstable'];
            }
        }

        foreach(array_keys($packageinfo) as $pkg) {
            $_deps = [];

            foreach($deps as $dep) {
                if ($dep['package'] == $packageinfo[$pkg]['packageid']
                    && isset($allreleases[$pkg])
                    && $dep['release'] == $allreleases[$pkg]['rid'])
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

        return $packageinfo;
    }

    /**
     * Find package by name.
     */
    public function findOneByName($packageName)
    {
        $sql = "SELECT * FROM packages WHERE name = ?";

        return $this->database->run($sql, [$packageName])->fetch();
    }
}
