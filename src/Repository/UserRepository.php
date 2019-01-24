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
 * Repository class for retrieving user table data.
 */
class UserRepository
{
    /**
     * Database handle.
     */
    private $database;

    /**
     * Class constructor.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Get registered (active) user by given username.
     */
    public function findActiveByHandle($handle)
    {
        $sql = "SELECT * FROM users WHERE registered = 1 AND handle = :handle";

        return $this->database->run($sql, [':handle' => $handle])->fetch();
    }

    /**
     * Get user by given email.
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";

        return $this->database->run($sql, [':email' => $email])->fetch();
    }

    /**
     * Get any user by given username.
     */
    public function findByHandle($handle)
    {
        $sql = "SELECT * FROM users WHERE handle = :handle";

        return $this->database->run($sql, [':handle' => $handle])->fetch();
    }

    /**
     * Get all active users. Active users have registered table set to 1.
     */
    public function findAll()
    {
        $sql = "SELECT * FROM users WHERE registered = 1 ORDER BY handle";

        $statement = $this->database->run($sql);

        return $statement->fetchAll();
    }

    /**
     * Retrieve user's wishlist URL.
     */
    public function getWishlistByHandle($handle)
    {
        $sql = "SELECT wishlist FROM users WHERE handle = :handle";

        $statement = $this->database->run($sql, [':handle' => $handle]);

        $result = $statement->fetch();

        return isset($result['wishlist']) ? $result['wishlist'] : null;
    }

    /**
     * Get maintainer(s) for package
     *
     * @param  int Package id
     * @return array
     */
    public function findMaintainersByPackageId($packageId)
    {
        $sql = "SELECT u.handle, u.name, u.email, u.showemail, u.wishlist, m.role, m.active
                FROM maintains m, users u
                WHERE m.package = :package_id
                AND m.handle = u.handle
                ORDER BY m.active DESC";

        $results = $this->database->run($sql, [$packageId])->fetchAll();

        $maintainers = [];
        foreach ($results as $result) {
            $maintainers[$result['handle']] = $result;
        }

        return $maintainers;
    }

    /**
     * Get all lead maintainers by package id.
     */
    public function findLeadMaintainersByPackage($package)
    {
        $sql = "SELECT handle, role, active
                FROM maintains
                WHERE package = ? AND role = 'lead'
                ORDER BY active DESC";

        $results = $this->database->run($sql, [$package])->fetchAll();

        $maintainers = [];
        foreach ($results as $result) {
            $maintainers[$result['handle']] = $result;
        }

        return $maintainers;
    }

    /**
     * Get all first letters of user handles.
     */
    public function getFirstLetters()
    {
        $sql = "SELECT SUBSTRING(handle, 1, 1)
                FROM users
                WHERE registered = 1
                ORDER BY handle
        ";

        return $this->database->run($sql)->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get number of all registered users.
     */
    public function getUsersCount()
    {
        $sql = "SELECT COUNT(handle) AS count FROM users WHERE registered = 1";

        return $this->database->run($sql)->fetch()['count'];
    }

    /**
     * Find all users by given offset and limit.
     */
    public function findAllUsersByOffset($limit, $offset)
    {
        $sql = "SELECT handle, name, email, homepage, showemail
                FROM users
                WHERE registered = 1
                ORDER BY handle
                LIMIT :limit OFFSET :offset
        ";

        $arguments = [
            ':limit' => $limit,
            ':offset' => $offset
        ];

        return $this->database->run($sql, $arguments)->fetchAll();
    }
}
