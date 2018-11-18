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
}
