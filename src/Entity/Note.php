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

namespace App\Entity;

use App\Database;

/**
 * Entity class representing notes table row.
 */
class Note
{
    private $database;
    private $authUser;

    /**
     * Array of valid column names to use in SQL query.
     */
    private $validKeys = ['uid', 'rid', 'cid', 'pid'];

    /**
     * Set database handler.
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Set database handler.
     */
    public function setAuthUser($authUser)
    {
        $this->authUser = $authUser;
    }

    /**
     * Create a new note.
     */
    public function add($key, $value, $note, $author = "")
    {
        if (empty($author)) {
            $author = $this->authUser->handle;
        }

        // Validate key
        if (!in_array($key, $this->validKeys, true)) {
            $key = 'uid';
        }

        $sql = "SELECT id FROM notes ORDER by id DESC";
        $id = $this->database->run($sql)->fetch()['id'];
        $id = !$id ? 1 : $id + 1;

        $sql = "INSERT INTO notes (id, $key, nby, ntime, note) VALUES (?, ?, ?, ?, ?)";
        $arguments = [$id, $value, $author, gmdate('Y-m-d H:i'), $note];

        return $this->database->run($sql, $arguments);
    }

    /**
     * Remove note.
     */
    public function remove($id)
    {
        return $this->database->run("DELETE FROM notes WHERE id = ?", [(int)$id]);
    }

    /**
     * Remove all notes by key.
     */
    public function removeAll($key, $value)
    {
        // Validate key
        if (!in_array($key, $this->validKeys, true)) {
            $key = 'uid';
        }

        return $this->database->run("DELETE FROM notes WHERE $key = ?", [$value]);
    }
}
