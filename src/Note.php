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

namespace App;

use \DB as DB;

/**
 * Class to handle notes.
 */
class Note
{
    /**
     * Create a new note.
     */
    public static function add($key, $value, $note, $author = "")
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

    /**
     * Remove note.
     */
    public static function remove($id)
    {
        global $dbh;

        $id = (int)$id;
        $res = $dbh->query("DELETE FROM notes WHERE id = $id");

        if (DB::isError($res)) {
            return $res;
        }

        return true;
    }

    /**
     * Remove all notes by key.
     */
    public static function removeAll($key, $value)
    {
        global $dbh;

        $res = $dbh->query("DELETE FROM notes WHERE $key = ". $dbh->quote($value));

        if (DB::isError($res)) {
            return $res;
        }

        return true;
    }
}
