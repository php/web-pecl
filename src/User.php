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

use App\Entity\Note;
use App\Entity\User as UserEntity;

/**
 * User service class.
 */
class User
{
    /**
     * Get a note entity object.
     */
    private static function getNote()
    {
        global $database, $auth_user;

        $note = new Note();

        $note->setDatabase($database);
        $note->setAuthUser($auth_user);

        return $note;
    }

    /**
     * Remove user.
     */
    public static function remove($uid)
    {
        global $dbh, $rest;

        self::getNote()->removeAll('uid', $uid);

        $rest->deleteMaintainerREST($uid);
        $rest->saveAllMaintainers();

        $dbh->query('DELETE FROM users WHERE handle = '. $dbh->quote($uid));

        return ($dbh->affectedRows() > 0);
    }

    /**
     * Reject pending request for user account.
     */
    public static function rejectRequest($uid, $reason)
    {
        global $dbh, $auth_user;

        list($email) = $dbh->getRow('SELECT email FROM users WHERE handle = ?', [$uid]);

        self::getNote()->add('uid', $uid, "Account rejected: $reason");

        $msg = "Your PECL account request was rejected by " . $auth_user->handle . ":\n"."$reason\n";
        $xhdr = "From: " . $auth_user->handle . "@php.net";

        mail($email, "Your PECL Account Request", $msg, $xhdr, "-f noreply@php.net");

        return true;
    }

    /**
     * Activate user account.
     */
    public static function activate($uid)
    {
        global $dbh, $auth_user, $rest;

        $user = new UserEntity($dbh, $uid);

        if (@$user->registered) {
            return false;
        }

        @$arr = unserialize($user->userinfo);

        self::getNote()->removeAll('uid', $uid);

        $user->set('registered', 1);

        if (is_array($arr)) {
            $user->set('userinfo', $arr[1]);
        }

        $user->set('created', gmdate('Y-m-d H:i'));
        $user->set('createdby', $auth_user->handle);
        $user->set('registered', 1);
        $user->store();

        self::getNote()->add('uid', $uid, 'Account opened');

        $rest->saveMaintainer($user->handle);
        $rest->saveAllmaintainers();

        $msg = "Your PECL account request has been opened.\n".
             "To log in, go to https://pecl.php.net/ and click on \"login\" in\n".
             "the top-right menu.\n";
        $xhdr = "From: " . $auth_user->handle . "@php.net";

        mail($user->email, "Your PECL Account Request", $msg, $xhdr, "-f noreply@php.net");

        return true;
    }

    /**
     * Check if given username is administrator.
     */
    public static function isAdmin($handle)
    {
        global $dbh;

        $query = 'SELECT handle FROM users WHERE handle = ? AND admin = 1';
        $sth = $dbh->query($query, [$handle]);

        return ($sth->numRows() > 0);
    }

    /**
     * Check if given username exists.
     */
    public static function exists($handle)
    {
        global $dbh;

        $sql = 'SELECT handle FROM users WHERE handle=?';
        $res = $dbh->query($sql, [$handle]);

        return ($res->numRows() > 0);
    }

    /**
     * Check if user maintains a package.
     */
    public static function maintains($user, $pkgid, $role = 'any')
    {
        global $dbh, $packageEntity;

        $package_id = $packageEntity->info($pkgid, 'id');

        if ($role == 'any') {
            return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? '.
                                'AND package = ?', [$user, $package_id]);
        }

        if (is_array($role)) {
            return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                                'AND role IN ("?")', [$user, $package_id, implode('","', $role)]);
        }

        return $dbh->getOne('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                            'AND role = ?', [$user, $package_id, $role]);
    }

    /**
     * Get user information.
     */
    public static function info($user, $field = null)
    {
        global $dbh;

        if ($field === null) {
            return $dbh->getRow('SELECT * FROM users WHERE handle = ?',
                                [$user], DB_FETCHMODE_ASSOC);
            unset($row['password']);
            return $row;
        }

        if ($field == 'password' || preg_match('/[^a-z]/', $user)) {
            return null;
        }

        return $dbh->getRow('SELECT ! FROM users WHERE handle = ?',
                            [$field, $user], DB_FETCHMODE_ASSOC);

    }

    /**
     * Update user information
     *
     * @param  array User information
     * @return object Instance of UserEntity
     */
    public static function update($data)
    {
        global $dbh;

        $fields = ["name", "email", "homepage", "showemail", "userinfo", "pgpkeyid", "wishlist"];

        $user = new UserEntity($dbh, $data['handle']);

        foreach ($data as $key => $value) {
            if (!in_array($key, $fields)) {
                continue;
            }

            $user->set($key, $value);
        }

        $user->store();

        return $user;
    }
}
