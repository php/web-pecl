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

use App\Entity\Package;
use App\User as BaseUser;
use App\Database;
use App\Rest;
use App\Repository\UserRepository;
use \PEAR as PEAR;

/**
 * Class to handle maintainers
 */
class Maintainer
{
    private $database;
    private $rest;
    private $authUser;
    private $package;

    /**
     * In database these are defined as enum type. Additionally there is
     * a contributor role available which isn't used in the PECL application.
     */
    const ROLES = ['lead', 'developer', 'contributor', 'helper'];

    /**
     * Set database handler.
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Set rest generator.
     */
    public function setRest(Rest $rest)
    {
        $this->rest = $rest;
    }

    /**
     * Set auth user.
     */
    public function setAuthUser($authUser)
    {
        $this->authUser = $authUser;
    }

    /**
     * Set package entity.
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Add new maintainer
     *
     * @param  mixed  Name of the package or it's ID
     * @param  string Handle of the user
     * @param  string Role of the user
     * @param  integer Is the developer actively working on the project?
     * @return mixed True or PEAR error object
     */
    public function add($package, $user, $role, $active = 1)
    {
        if (!BaseUser::exists($user)) {
            return PEAR::raiseError("User $user does not exist");
        }

        if (is_string($package)) {
            $package = $this->package->info($package, 'id');
        }

        $sql = "INSERT INTO maintains (handle, package, role, active) VALUES (?, ?, ?, ?)";
        $result = $this->database->run($sql, [$user, $package, $role, (int)$active]);

        if (!$result) {
            return $result;
        }

        $packagename = $this->package->info($package, 'name');
        $this->rest->savePackageMaintainer($packagename);

        return true;
    }

    /**
     * Check if role is valid
     *
     * @param string Name of the role
     * @return boolean
     */
    private function isValidRole($role)
    {
        return in_array($role, self::ROLES);
    }

    /**
     * Remove user from package
     *
     * @param  mixed Name of the package or it's ID
     * @param  string Handle of the user
     * @return True or PEAR error object
     */
    private function remove($package, $user)
    {
        if (!$this->authUser->isAdmin() && !BaseUser::maintains($this->authUser->handle, $package, 'lead')) {
            return PEAR::raiseError('Maintainer::remove: insufficient privileges');
        }

        if (is_string($package)) {
            $package = $this->package->info($package, 'id');
        }

        $sql = 'DELETE FROM maintains WHERE package = ? AND handle = ?';

        return $this->database->run($sql, [$package, $user]);
    }

    /**
     * Update user and roles of a package
     *
     * @param int $pkgid The package id to update
     * @param array $users Assoc array containing the list of users
     *                     in the form: '<user>' => ['role' => '<role>', 'active' => '<active>']
     * @return mixed PEAR_Error or true
     */
    public function updateAll($pkgid, $users)
    {
        $admin = $this->authUser->isAdmin();

        // Only admins and leads can do this.
        if ($this->mayUpdate($pkgid) == false) {
            return PEAR::raiseError('Maintainer::updateAll: insufficient privileges');
        }

        // Needed for logging
        $pkg_name = $this->package->info((int)$pkgid, "name");

        if (empty($pkg_name)) {
            PEAR::raiseError('Maintainer::updateAll: no such package');
        }

        $userRepository = new UserRepository($this->database);
        $old = $userRepository->findMaintainersByPackageId($pkgid);

        if (!$old) {
            return PEAR::raiseError('Maintainer::updateAll: some error occurred');
        }

        $old_users = array_keys($old);
        $new_users = array_keys($users);

        if (!$admin && !in_array($this->authUser->handle, $new_users)) {
            return PEAR::raiseError("You can not delete your own maintainer role or you will not ".
                                    "be able to complete the update process. Set your name ".
                                    "in package.xml or let the new lead developer upload ".
                                    "the new release");
        }

        foreach ($users as $user => $u) {
            $role = $u['role'];
            $active = $u['active'];

            if (!$this->isValidRole($role)) {
                return PEAR::raiseError("invalid role '$role' for user '$user'");
            }

            // The user is not present -> add him
            if (!in_array($user, $old_users)) {
                $e = $this->add($pkgid, $user, $role, $active);

                if (PEAR::isError($e)) {
                    return PEAR::raiseError($e->getMessage());;
                }

                continue;
            }

            // Users exists but role has changed -> update it
            if ($role !== $old[$user]['role']) {
                $res = $this->update($pkgid, $user, $role, $active);

                if (!$res) {
                    return PEAR::raiseError('Error');
                }
            }
        }

        // Drop users who are no longer maintainers
        foreach ($old_users as $old_user) {
            if (!in_array($old_user, $new_users)) {
                $res = $this->remove($pkgid, $old_user);

                if (!$res) {
                    return PEAR::raiseError('Error occurred');
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
    public function update($package, $user, $role, $active)
    {
        $sql = 'UPDATE maintains SET role = ?, active = ? WHERE package = ? AND handle = ?';

        return $this->database->run($sql, [$role, $active, $package, $user]);
    }

    /**
     * Checks if the current user is allowed to update the maintainer data
     *
     * @param  int  ID of the package
     * @return boolean
     */
    private function mayUpdate($package)
    {
        $admin = $this->authUser->isAdmin();

        if (!$admin && !BaseUser::maintains($this->authUser->handle, $package, 'lead')) {
            return false;
        }

        return true;
    }
}
