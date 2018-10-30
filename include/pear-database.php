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

require_once 'DB/storage.php';
require_once 'PEAR/Common.php';
require_once 'HTTP.php';
require_once __DIR__.'/../src/Category.php';
require_once __DIR__.'/../src/Maintainer.php';
require_once __DIR__.'/../src/Package.php';
require_once __DIR__.'/../src/Release.php';
require_once __DIR__.'/../src/User.php';

// {{{ renumber_visitations()

/**
 *
 *
 * Some useful "visitation model" tricks:
 *
 * To find the number of child elements:
 *  (right - left - 1) / 2
 *
 * To find the number of child elements (including self):
 *  (right - left + 1) / 2
 *
 * To get all child nodes:
 *
 *  SELECT * FROM table WHERE left > <self.left> AND left < <self.right>
 *
 *
 * To get all child nodes, including self:
 *
 *  SELECT * FROM table WHERE left BETWEEN <self.left> AND <self.right>
 *  "ORDER BY left" gives tree view
 *
 * To get all leaf nodes:
 *
 *  SELECT * FROM table WHERE right-1 = left;
 */
function renumber_visitations($id, $parent = null)
{
    global $dbh;
    if ($parent === null) {
        $left = $dbh->getOne("select max(cat_right) + 1 from categories
                              where parent is null");
        $left = ($left !== null) ? $left : 1; // first node
    } else {
        $left = $dbh->getOne("select cat_right from categories where id = $parent");
    }
    $right = $left + 1;
    // update my self
    $err = $dbh->query("update categories
                        set cat_left = $left, cat_right = $right
                        where id = $id");
    if (PEAR::isError($err)) {
        return $err;
    }
    if ($parent === null) {
        return true;
    }
    $err = $dbh->query("update categories set cat_left = cat_left+2
                        where cat_left > $left");
    if (PEAR::isError($err)) {
        return $err;
    }
    // (cat_right >= $left) == update the parent but not the node itself
    $err = $dbh->query("update categories set cat_right = cat_right+2
                        where cat_right >= $left and id <> $id");
    if (PEAR::isError($err)) {
        return $err;
    }
    return true;
}

// }}}

// These classes correspond to tables and methods define operations on
// each.


/**
 * Class to handle notes
 *
 * @class   note
 * @package pearweb
 */
class note
{
    // {{{ +proto bool   note::add(string, int, string, string) API 1.0

    function add($key, $value, $note, $author = "")
    {
        global $dbh, $auth_user;
        if (empty($author)) {
            $author = $auth_user->handle;
        }
        if (!in_array($key, ['uid', 'rid', 'cid', 'pid'], true)) {
            // bad hackers not allowed
            $key = 'uid';
        }
        $nid = $dbh->nextId("notes");
        $stmt = $dbh->prepare("INSERT INTO notes (id,$key,nby,ntime,note) ".
                              "VALUES(?,?,?,?,?)");
        $res = $dbh->execute($stmt, [$nid, $value, $author,
                             gmdate('Y-m-d H:i'), $note]);
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    // }}}
    // {{{ +proto bool   note::remove(int) API 1.0

    function remove($id)
    {
        global $dbh;
        $id = (int)$id;
        $res = $dbh->query("DELETE FROM notes WHERE id = $id");
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    // }}}
    // {{{ +proto bool   note::removeAll(string, int) API 1.0

    function removeAll($key, $value)
    {
        global $dbh;
        $res = $dbh->query("DELETE FROM notes WHERE $key = ". $dbh->quote($value));
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    // }}}
}

class statistics
{
    // {{{ package()

    /**
     * Get general package statistics
     *
     * @param  integer ID of the package
     * @return array
     */
    function package($id)
    {
        global $dbh;
        $query = "SELECT SUM(dl_number) FROM package_stats WHERE pid = " . (int)$id;
        return $dbh->getOne($query);
    }

    // }}}
    // {{{ release()

    function release($id, $rid = "")
    {
        global $dbh;

         $query = 'SELECT s.release, s.dl_number, s.last_dl, r.releasedate '
            . 'FROM package_stats AS s '
            . 'LEFT JOIN releases AS r ON (s.rid = r.id) '
            . "WHERE pid = " . (int)$id;
        if (!empty($rid)) {
            $query .= " AND rid = " . (int)$rid;
        }
        $query .= " GROUP BY rid ORDER BY rid DESC";

        return $dbh->getAll($query, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{ activeRelease()

    function activeRelease($id, $rid = "")
    {
        global $dbh;

         $query = 'SELECT s.release, SUM(s.dl_number) AS dl_number, MAX(s.last_dl) AS last_dl, MIN(r.releasedate) AS releasedate '
            . 'FROM package_stats AS s '
            . 'LEFT JOIN releases AS r ON (s.rid = r.id) '
            . "WHERE pid = " . (int)$id;
        if (!empty($rid)) {
            $query .= " AND rid = " . (int)$rid;
        }
        $query .= " GROUP BY s.release HAVING COUNT(r.id) > 0 ORDER BY r.releasedate DESC";

        return $dbh->getAll($query, DB_FETCHMODE_ASSOC);
    }

    // }}}
}

// {{{ class PEAR_User

class PEAR_User extends DB_storage
{
    public function __construct(&$dbh, $user)
    {
        parent::__construct("users", "handle", $dbh);
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->setup($user);
        $this->popErrorHandling();
    }

    function is($handle)
    {
        $ret = strtolower($this->handle);
        return (strtolower($handle) == $ret);
    }

    function isAdmin()
    {
        return ($this->admin == 1);
    }
}

// }}}
// {{{ class PEAR_Package

class PEAR_Package extends DB_storage
{
    public function __construct(&$dbh, $package, $keycol = "id")
    {
        parent::__construct("packages", $keycol, $dbh);
        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->setup($package);
        $this->popErrorHandling();
    }
}

// }}}

/**
 * Converts a Unix timestamp to a date() formatted string in the UTC time zone
 *
 * @param int    $ts      a Unix timestamp from the local machine.  If none
 *                         is provided the current time is used.
 * @param string $format  a format string, as per https://php.net/date
 *
 * @return string  the time formatted time
 */
function make_utc_date($ts = null, $format = 'Y-m-d H:i \U\T\C') {
    if (!$ts) {
        $ts = time();
    }
    return gmdate($format, $ts);
}
