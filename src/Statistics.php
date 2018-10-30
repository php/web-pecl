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

/**
 * Statistics service class.
 */
class Statistics
{
    /**
     * Get general package statistics.
     *
     * @param  integer ID of the package
     * @return array
     */
    public static function package($id)
    {
        global $dbh;

        $query = 'SELECT SUM(dl_number) FROM package_stats WHERE pid = '.(int)$id;

        return $dbh->getOne($query);
    }

    /**
     * Get statistics for active release.
     */
    public static function activeRelease($id, $rid = "")
    {
        global $dbh;

        $query = 'SELECT s.release, SUM(s.dl_number) AS dl_number, MAX(s.last_dl) AS last_dl, MIN(r.releasedate) AS releasedate '
            . 'FROM package_stats AS s '
            . 'LEFT JOIN releases AS r ON (s.rid = r.id) '
            . "WHERE pid = " . (int)$id;

        if (!empty($rid)) {
            $query .= " AND rid = " . (int)$rid;
        }

        $query .= ' GROUP BY s.release HAVING COUNT(r.id) > 0 ORDER BY r.releasedate DESC';

        return $dbh->getAll($query, DB_FETCHMODE_ASSOC);
    }
}
