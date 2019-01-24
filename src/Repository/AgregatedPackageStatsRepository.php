<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
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
 * Statistics repository class for retrieving agregated_package_stats results.
 */
class AgregatedPackageStatsRepository
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
     * Get total number of package downloads.
     *
     * @param  integer ID of the package
     * @return array
     */
    public function find($packageId, $releaseId)
    {
        $sql = "SELECT
                    YEAR(yearmonth) AS dyear,
                    MONTH(yearmonth) AS dmonth,
                    SUM(downloads) AS downloads
                FROM aggregated_package_stats a, releases r
                WHERE a.package_id = :package_id
                    AND r.id = a.release_id
                    AND r.package = a.package_id
                    AND yearmonth > (now() - INTERVAL 1 YEAR)
        ";

        $arguments = ['package_id' => $packageId];

        if ($releaseId > 0) {
            $sql .= " AND a.release_id = :release_id";
            $arguments[':release_id'] = $releaseId;
        }

        $sql .= " GROUP BY dyear, dmonth ORDER BY dyear DESC, dmonth DESC";

        return $this->database->run($sql, $arguments)->fetchAll();
    }
}
