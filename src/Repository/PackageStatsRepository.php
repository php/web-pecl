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
  | Authors: Stig S. Bakken <ssb@fast.no>                                |
  |          Tomas V.V.Cox <cox@php.net>                                 |
  |          Martin Jansen <mj@php.net>                                  |
  |          Gregory Beaver <cellog@php.net>                             |
  |          Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

namespace App\Repository;

use App\Database;

/**
 * Statistics repository class for retrieving package_stats results.
 */
class PackageStatsRepository
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
    public function getDownloadsByPackageId($id)
    {
        $sql = "SELECT
                    SUM(dl_number) AS downloads
                FROM package_stats
                WHERE pid = :pid
        ";

        $statement = $this->database->run($sql, [':pid' => $id]);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return $result['downloads'];
    }

    /**
     * Get statistics for releases.
     */
    public function getReleasesStats($packageId, $releaseId = null)
    {
        $sql = "SELECT
                    s.release,
                    SUM(s.dl_number) AS dl_number,
                    MAX(s.last_dl) AS last_dl,
                    MIN(r.releasedate) AS releasedate
                FROM package_stats AS s
                LEFT JOIN releases AS r ON (s.rid = r.id)
                WHERE pid = :pid
        ";

        $arguments = [':pid' => $packageId];

        if (!empty($releaseId)) {
            $sql .= " AND rid = :rid";
            $arguments[':rid'] = $releaseId;
        }

        $sql .= " GROUP BY s.release HAVING COUNT(r.id) > 0 ORDER BY r.releasedate DESC";

        $statement = $this->database->run($sql, $arguments);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
