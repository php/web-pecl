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
        global $database, $rest;

        self::getNote()->removeAll('uid', $uid);

        $rest->deleteMaintainerREST($uid);
        $rest->saveAllMaintainers();

        $statement = $database->run('DELETE FROM users WHERE handle = ?', [$uid]);

        return ($statement->rowCount() > 0);
    }

    /**
     * Reject pending request for user account.
     */
    public static function rejectRequest($uid, $reason)
    {
        global $database, $auth_user;

        $email = $database->run('SELECT email FROM users WHERE handle = ?', [$uid])->fetch()['email'];

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
        global $database, $auth_user, $rest;

        $user = new UserEntity($database, $uid);

        if ($user->registered) {
            return false;
        }

        @$arr = unserialize($user->get('userinfo'));

        self::getNote()->removeAll('uid', $uid);

        $user->set('registered', 1);

        if (is_array($arr)) {
            $user->set('userinfo', $arr[1]);
        }

        $user->set('created', gmdate('Y-m-d H:i'));
        $user->set('createdby', $auth_user->handle);
        $user->set('registered', 1);
        $user->save();

        self::getNote()->add('uid', $uid, 'Account opened');

        $rest->saveMaintainer($user->handle);
        $rest->saveAllmaintainers();

        $msg = "Your PECL account request has been opened.\n".
             "To log in, go to https://pecl.php.net/ and click on \"login\" in\n".
             "the top-right menu.\n";
        $xhdr = "From: " . $auth_user->handle . "@php.net";

        mail($user->get('email'), "Your PECL Account Request", $msg, $xhdr, "-f noreply@php.net");

        return true;
    }

    /**
     * Check if given username is administrator.
     */
    public static function isAdmin($handle)
    {
        global $database;

        $sql = 'SELECT handle FROM users WHERE handle = ? AND admin = 1';
        $statement = $database->run($sql, [$handle])->fetch();

        return (bool)$statement;
    }

    /**
     * Check if given username exists.
     */
    public static function exists($handle)
    {
        global $database;

        $sql = 'SELECT handle FROM users WHERE handle = ?';
        $statement = $database->run($sql, [$handle])->fetch();

        return (bool)$statement;
    }

    /**
     * Check if user maintains a package.
     */
    public static function maintains($user, $pkgid, $role = 'any')
    {
        global $database, $packageEntity;

        $package_id = $packageEntity->info($pkgid, 'id');

        if ($role == 'any') {
            return $database->run('SELECT role FROM maintains WHERE handle = ? '.
                                'AND package = ?', [$user, $package_id])->fetch()['role'];
        }

        if (is_array($role)) {
            return $database->run('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                                'AND role IN ("?")', [$user, $package_id, implode('","', $role)])->fetch()['role'];
        }

        return $database->run('SELECT role FROM maintains WHERE handle = ? AND package = ? '.
                            'AND role = ?', [$user, $package_id, $role])->fetch()['role'];
    }

    /**
     * Get user information.
     */
    public static function info($user, $field = null)
    {
        global $database;

        if ($field === null) {
            return $database->run('SELECT * FROM users WHERE handle = ?', [$user])->fetch();
        }

        if ($field == 'password' || preg_match('/[^a-z]/', $user)) {
            return null;
        }

        $validFields = [
            'handle',
            'name',
            'email',
            'homepage',
            'created',
            'createdby',
            'lastlogin',
            'showemail',
            'registered',
            'admin',
            'userinfo',
            'pgpkeyid',
            'pgpkey',
            'wishlist',
            'longitude',
            'latitude',
            'active',
            'from_site',
        ];

        if (!in_array($field, $validFields)) {
            return null;
        }

        return $database->run("SELECT $field FROM users WHERE handle = ?", [$user])->fetch()[$field];
    }

    /**
     * Update user information
     *
     * @param  array User information
     * @return object Instance of UserEntity
     */
    public static function update($data)
    {
        global $database;

        $fields = ['name', 'email', 'homepage', 'showemail', 'userinfo', 'pgpkeyid', 'wishlist'];

        $user = new UserEntity($database, $data['handle']);

        foreach ($data as $key => $value) {
            if (!in_array($key, $fields)) {
                continue;
            }

            $user->set($key, $value);
        }

        $user->save();

        return $user;
    }
}
