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

require_once 'PEAR/Common.php';

/**
 * Class to handle maintainers
 */
class Maintainer
{
    /**
     * Add new maintainer
     *
     * @param  mixed  Name of the package or it's ID
     * @param  string Handle of the user
     * @param  string Role of the user
     * @param  integer Is the developer actively working on the project?
     * @return mixed True or PEAR error object
     */
    public static function add($package, $user, $role, $active = 1)
    {
        global $dbh, $rest;

        if (!User::exists($user)) {
            return PEAR::raiseError("User $user does not exist");
        }

        if (is_string($package)) {
            $package = Package::info($package, 'id');
        }

        $err = $dbh->query("INSERT INTO maintains (handle, package, role, active) VALUES (?, ?, ?, ?)",
                           [$user, $package, $role, (int)$active]);

        if (DB::isError($err)) {
            return $err;
        }

        $packagename = Package::info($package, 'name');
        $rest->savePackageMaintainer($packagename);

        return true;
    }

    /**
     * Get maintainer(s) for package
     *
     * @param  mixed Name of the package or it's ID
     * @param  boolean Only return lead maintainers?
     * @return array
     */
    public static function get($package, $lead = false)
    {
        global $dbh;

        if (is_string($package)) {
            $package = Package::info($package, 'id');
        }

        $query = 'SELECT handle, role, active FROM maintains WHERE package = ?';

        if ($lead) {
            $query .= " AND role = 'lead'";
        }

        $query .= ' ORDER BY active DESC';

        return $dbh->getAssoc($query, true, [$package], DB_FETCHMODE_ASSOC);
    }

    /**
     * Check if role is valid
     *
     * @param string Name of the role
     * @return boolean
     */
    private static function isValidRole($role)
    {
        static $roles;

        if (empty($roles)) {
            $roles = PEAR_Common::getUserRoles();
        }

        return in_array($role, $roles);
    }

    /**
     * Remove user from package
     *
     * @param  mixed Name of the package or it's ID
     * @param  string Handle of the user
     * @return True or PEAR error object
     */
    private static function remove($package, $user)
    {
        global $dbh, $auth_user;

        if (!$auth_user->isAdmin() && !User::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('Maintainer::remove: insufficient privileges');
        }

        if (is_string($package)) {
            $package = Package::info($package, 'id');
        }

        $sql = 'DELETE FROM maintains WHERE package = ? AND handle = ?';

        return $dbh->query($sql, [$package, $user]);
    }

    /**
     * Update user and roles of a package
     *
     * @param int $pkgid The package id to update
     * @param array $users Assoc array containing the list of users
     *                     in the form: '<user>' => ['role' => '<role>', 'active' => '<active>']
     * @return mixed PEAR_Error or true
     */
    public static function updateAll($pkgid, $users)
    {
        global $dbh, $auth_user;

        $admin = $auth_user->isAdmin();

        // Only admins and leads can do this.
        if (self::mayUpdate($pkgid) == false) {
            return PEAR::raiseError('Maintainer::updateAll: insufficient privileges');
        }

        $pkg_name = Package::info((int)$pkgid, "name", true); // Needed for logging

        if (empty($pkg_name)) {
            PEAR::raiseError('Maintainer::updateAll: no such package');
        }

        $old = self::get($pkgid);

        if (DB::isError($old)) {
            return $old;
        }

        $old_users = array_keys($old);
        $new_users = array_keys($users);

        if (!$admin && !in_array($auth_user->handle, $new_users)) {
            return PEAR::raiseError("You can not delete your own maintainer role or you will not ".
                                    "be able to complete the update process. Set your name ".
                                    "in package.xml or let the new lead developer upload ".
                                    "the new release");
        }

        foreach ($users as $user => $u) {
            $role = $u['role'];
            $active = $u['active'];

            if (!self::isValidRole($role)) {
                return PEAR::raiseError("invalid role '$role' for user '$user'");
            }

            // The user is not present -> add him
            if (!in_array($user, $old_users)) {
                $e = self::add($pkgid, $user, $role, $active);

                if (PEAR::isError($e)) {
                    return $e;
                }

                continue;
            }

            // Users exists but role has changed -> update it
            if ($role != $old[$user]['role']) {
                $res = self::update($pkgid, $user, $role, $active);

                if (DB::isError($res)) {
                    return $res;
                }
            }
        }

        // Drop users who are no longer maintainers
        foreach ($old_users as $old_user) {
            if (!in_array($old_user, $new_users)) {
                $res = self::remove($pkgid, $old_user);

                if (DB::isError($res)) {
                    return $res;
                }
            }
        }

        return true;
    }

    /**
     * Update maintainer entry
     *
     * @param  int Package ID
     * @param  string Username
     * @param  string Role
     * @param  string Is the developer actively working on the package?
     */
    public static function update($package, $user, $role, $active)
    {
        global $dbh;

        $query = 'UPDATE maintains SET role = ?, active = ? WHERE package = ? AND handle = ?';

        return $dbh->query($query, [$role, $active, $package, $user]);
    }

    /**
     * Checks if the current user is allowed to update the maintainer data
     *
     * @param  int  ID of the package
     * @return boolean
     */
    private static function mayUpdate($package)
    {
        global $auth_user;

        $admin = $auth_user->isAdmin();

        if (!$admin && !User::maintains($auth_user->handle, $package, 'lead')) {
            return false;
        }

        return true;
    }
}
