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
 * Repository class for retrieving user notes.
 */
class CvsAclRepository
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
     * Get access info by given username.
     */
    public function getPathByUsername($username)
    {
        $sql = "SELECT path FROM cvs_acl WHERE username = :username";

        $result = $this->database->run($sql, [':username' => $username])->fetch();

        return !$result ? [] : $result;
    }
}
