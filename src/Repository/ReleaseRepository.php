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
  |          Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Repository;

use App\Database;

/**
 * Repository class for releases.
 */
class ReleaseRepository
{
    private $database;

    /**
     * Number of recent releases returned.
     */
    const MAX_ITEMS_RETURNED = 5;

    /**
     * Class constructor.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Get recent releases
     *
     * @param  integer Number of releases to return
     * @return array
     */
    public function findRecent($max = self::MAX_ITEMS_RETURNED)
    {
        $sql = "SELECT packages.id AS id,
                    packages.name AS name,
                    packages.summary AS summary,
                    releases.version AS version,
                    releases.releasedate AS releasedate,
                    releases.releasenotes AS releasenotes,
                    releases.doneby AS doneby,
                    releases.state AS state
                FROM packages, releases
                WHERE packages.id = releases.package
                AND packages.approved = 1
                AND packages.package_type = 'pecl'
                ORDER BY releases.releasedate DESC
                LIMIT :limit
        ";

        return $this->database->run($sql, [':limit' => $max])->fetchAll();
    }
}
