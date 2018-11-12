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
 * User service class.
 */
class User
{
    /**
     * Remove user.
     */
    public static function remove($uid)
    {
        global $dbh, $rest;

        Note::removeAll("uid", $uid);

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

        Note::add("uid", $uid, "Account rejected: $reason");

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

        $user = new PEAR_User($dbh, $uid);

        if (@$user->registered) {
            return false;
        }

        @$arr = unserialize($user->userinfo);
        Note::removeAll("uid", $uid);
        $user->set('registered', 1);

        if (is_array($arr)) {
            $user->set('userinfo', $arr[1]);
        }

        $user->set('created', gmdate('Y-m-d H:i'));
        $user->set('createdby', $auth_user->handle);
        $user->set('registered', 1);
        $user->store();
        Note::add("uid", $uid, "Account opened");

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
        global $dbh;

        $package_id = Package::info($pkgid, 'id');

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
     * Get all registered users.
     */
    public static function listAll($registered_only = true)
    {
        global $dbh;

        $query = "SELECT * FROM users";

        if ($registered_only === true) {
            $query .= " WHERE registered = 1";
        }

        $query .= " ORDER BY handle";

        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    /**
     * Update user information
     *
     * @param  array User information
     * @return object Instance of PEAR_User
     */
    public static function update($data)
    {
        global $dbh;

        $fields = ["name", "email", "homepage", "showemail", "userinfo", "pgpkeyid", "wishlist"];

        $user = new PEAR_User($dbh, $data['handle']);

        foreach ($data as $key => $value) {
            if (!in_array($key, $fields)) {
                continue;
            }

            $user->set($key, $value);
        }

        $user->store();

        return $user;
    }

    /**
     * Get recent releases for the given user
     *
     * @param  string Handle of the user
     * @param  int    Number of releases (default is 10)
     * @return array
     */
    public static function getRecentReleases($handle, $n = 10)
    {
        global $dbh;

        $recent = [];

        $query = "SELECT p.id AS id, " .
            "p.name AS name, " .
            "p.summary AS summary, " .
            "r.version AS version, " .
            "r.releasedate AS releasedate, " .
            "r.releasenotes AS releasenotes, " .
            "r.doneby AS doneby, " .
            "r.state AS state " .
            "FROM packages p, releases r, maintains m " .
            "WHERE p.package_type = 'pecl' AND p.id = r.package " .
            "AND p.id = m.package AND m.handle = '" . $handle . "' " .
            "ORDER BY r.releasedate DESC";
        $sth = $dbh->limitQuery($query, 0, $n);

        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }

        return $recent;
    }
}
