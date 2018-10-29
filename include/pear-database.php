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
require_once __DIR__.'/../src/Package.php';

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
 * Class to handle maintainers
 *
 * @class   maintainer
 * @package pearweb
 */
class maintainer
{
    // {{{ +proto int    maintainer::add(int|string, string, string) API 1.0

    /**
     * Add new maintainer
     *
     * @static
     * @param  mixed  Name of the package or it's ID
     * @param  string Handle of the user
     * @param  string Role of the user
     * @param  integer Is the developer actively working on the project?
     * @return mixed True or PEAR error object
     */
    function add($package, $user, $role, $active = 1)
    {
        global $dbh, $rest;

        if (!user::exists($user)) {
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

    // }}}
    // {{{  proto struct maintainer::get(int|string, [bool]) API 1.0

    /**
     * Get maintainer(s) for package
     *
     * @static
     * @param  mixed Name of the package or it's ID
     * @param  boolean Only return lead maintainers?
     * @return array
     */
    function get($package, $lead = false)
    {
        global $dbh;
        if (is_string($package)) {
            $package = Package::info($package, 'id');
        }
        $query = "SELECT handle, role, active FROM maintains WHERE package = ?";
        if ($lead) {
            $query .= " AND role = 'lead'";
        }
        $query .= " ORDER BY active DESC";

        return $dbh->getAssoc($query, true, [$package], DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{  proto bool   maintainer::isValidRole(string) API 1.0

    /**
     * Check if role is valid
     *
     * @static
     * @param string Name of the role
     * @return boolean
     */
    function isValidRole($role)
    {
        require_once "PEAR/Common.php";

        static $roles;
        if (empty($roles)) {
            $roles = PEAR_Common::getUserRoles();
        }
        return in_array($role, $roles);
    }

    // }}}
    // {{{ +proto bool   maintainer::remove(int|string, string) API 1.0

    /**
     * Remove user from package
     *
     * @static
     * @param  mixed Name of the package or it's ID
     * @param  string Handle of the user
     * @return True or PEAR error object
     */
    function remove($package, $user)
    {
        global $dbh, $auth_user;
        if (!$auth_user->isAdmin() && !user::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('maintainer::remove: insufficient privileges');
        }
        if (is_string($package)) {
            $package = Package::info($package, 'id');
        }
        $sql = "DELETE FROM maintains WHERE package = ? AND handle = ?";
        return $dbh->query($sql, [$package, $user]);
    }

    // }}}
    // {{{ +proto bool   maintainer::updateAll(int, array) API 1.0

    /**
     * Update user and roles of a package
     *
     * @static
     * @param int $pkgid The package id to update
     * @param array $users Assoc array containing the list of users
     *                     in the form: '<user>' => ['role' => '<role>', 'active' => '<active>']
     * @return mixed PEAR_Error or true
     */
    function updateAll($pkgid, $users)
    {

        global $dbh, $auth_user;

        $admin = $auth_user->isAdmin();

        // Only admins and leads can do this.
        if (maintainer::mayUpdate($pkgid) == false) {
            return PEAR::raiseError('maintainer::updateAll: insufficient privileges');
        }

        $pkg_name = Package::info((int)$pkgid, "name", true); // Needed for logging
        if (empty($pkg_name)) {
            PEAR::raiseError('maintainer::updateAll: no such package');
        }

        $old = maintainer::get($pkgid);
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

            if (!maintainer::isValidRole($role)) {
                return PEAR::raiseError("invalid role '$role' for user '$user'");
            }
            // The user is not present -> add him
            if (!in_array($user, $old_users)) {
                $e = maintainer::add($pkgid, $user, $role, $active);
                if (PEAR::isError($e)) {
                    return $e;
                }
                continue;
            }
            // Users exists but role has changed -> update it
            if ($role != $old[$user]['role']) {
                $res = maintainer::update($pkgid, $user, $role, $active);
                if (DB::isError($res)) {
                    return $res;
                }
            }
        }
        // Drop users who are no longer maintainers
        foreach ($old_users as $old_user) {
            if (!in_array($old_user, $new_users)) {
                $res = maintainer::remove($pkgid, $old_user);
                if (DB::isError($res)) {
                    return $res;
                }
            }
        }
        return true;
    }

    // }}}
    // {{{

    /**
     * Update maintainer entry
     *
     * @access public
     * @param  int Package ID
     * @param  string Username
     * @param  string Role
     * @param  string Is the developer actively working on the package?
     */
    function update($package, $user, $role, $active) {
        global $dbh;

        $query = "UPDATE maintains SET role = ?, active = ? " .
            "WHERE package = ? AND handle = ?";
        return $dbh->query($query, [$role, $active, $package, $user]);
    }

    // {{{ NOEXPORT  maintainer::mayUpdate(int)

    /**
     * Checks if the current user is allowed to update the maintainer data
     *
     * @access public
     * @param  int  ID of the package
     * @return boolean
     */
    function mayUpdate($package) {
        global $auth_user;

        $admin = $auth_user->isAdmin();

        if (!$admin && !user::maintains($auth_user->handle, $package, 'lead')) {
            return false;
        }

        return true;
    }

    // }}}
}

/**
 * Class to handle releases
 *
 * @class   release
 * @package pearweb
 */
class release
{
    // {{{  proto array  release::getRecent([int]) API 1.0

    /**
     * Get recent releases
     *
     * @static
     * @param  integer Number of releases to return
     * @return array
     */
    function getRecent($n = 5)
    {
        global $dbh;
        $sth = $dbh->limitQuery("SELECT packages.id AS id, ".
                                "packages.name AS name, ".
                                "packages.summary AS summary, ".
                                "releases.version AS version, ".
                                "releases.releasedate AS releasedate, ".
                                "releases.releasenotes AS releasenotes, ".
                                "releases.doneby AS doneby, ".
                                "releases.state AS state ".
                                "FROM packages, releases ".
                                "WHERE packages.id = releases.package ".
                                "AND packages.approved = 1 ".
                                "AND packages.package_type = 'pecl' ".
                                "ORDER BY releases.releasedate DESC", 0, $n);
        $recent = [];
        // XXX Fixme when DB gets limited getAll()
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ +proto string release::upload(string, string, string, string, binary, string) API 1.0

    /**
     * Upload new release
     *
     * @static
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     */
    function upload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $auth_user;
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && $role != 'developer' && !$auth_user->isAdmin()) {
            return PEAR::raiseError('release::upload: insufficient privileges');
        }
        $ref = release::validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum);
        if (PEAR::isError($ref)) {
            return $ref;
        }

        return release::confirmUpload($package, $version, $state, $relnotes, $md5sum, $ref['package_id'], $ref['file']);
    }

    // }}}
    // {{{ +proto string release::validateUpload(string, string, string, string, binary, string) API 1.0

    /**
     * Determine if uploaded file is a valid release
     *
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     * @return mixed
     */
    function validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $dbh, $auth_user;
        $role = user::maintains($auth_user->handle, $package);
        if ($role != 'lead' && $role != 'developer' && !$auth_user->isAdmin()) {
            return PEAR::raiseError('release::validateUpload: insufficient privileges');
        }
        // (2) verify that package exists
        $package_id = Package::info($package, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("package `$package' must be registered first");
        }

        // (3) verify that version does not exist
        $test = $dbh->getOne("SELECT version FROM releases ".
                             "WHERE package = ? AND version = ?",
                             [$package_id, $version]);
        if (PEAR::isError($test)) {
            return $test;
        }
        if ($test) {
            return PEAR::raiseError("already exists: $package $version");
        }

        // (4) store tar ball to temp file
        $tempfile = sprintf("%s/%s%s-%s.tgz",
                            PEAR_TARBALL_DIR, ".new.", $package, $version);
        $file = sprintf("%s/%s-%s.tgz", PEAR_TARBALL_DIR, $package, $version);
        if (!@copy($tarball, $tempfile)) {
            return PEAR::raiseError("writing $tempfile failed: $php_errormsg");
        }

        if (!isset($package_id)) {
            return PEAR::raiseError("bad upload: package_id missing");
        }

        // later: do lots of integrity checks on the tarball
        if (!@rename($tempfile, $file)) {
            return PEAR::raiseError("renaming failed: $php_errormsg");
        }

        // (5) verify MD5 checksum
        $testsum = md5_file($file);
        if ($testsum != $md5sum) {
            $bytes = strlen($data);
            return PEAR::raiseError("bad md5 checksum (checksum=$testsum ($bytes bytes: $data), specified=$md5sum)");
        }

        $info = ["package_id" => $package_id,
                      "package" => $package,
                      "version" => $version,
                      "state" => $state,
                      "relnotes" => $relnotes,
                      "md5sum" => $md5sum,
                      "file" => $file];
        $infofile = sprintf("%s/%s%s-%s",
                            PEAR_TARBALL_DIR, ".info.", $package, $version);
        $fp = @fopen($infofile, "w");
        if (!is_resource($fp)) {
            return PEAR::raiseError("writing $infofile failed: $php_errormsg");
        }
        fwrite($fp, serialize($info));
        fclose($fp);

		/* We have to save uncompressed version too, as we use X-Sendfile header */
		$fp = fopen('compress.zlib://' . $file, 'rb');
		$tarfilepath = substr($file, 0, -4) . '.tar';
		if (!@file_put_contents($tarfilepath, $fp)) {
            return PEAR::raiseError("Copy uncompressed archive failed: $php_errormsg");
		}

        return $info;
    }

    // }}}
    // {{{ +proto bool   release::confirmUpload(string, string, string, string, string, int, binary) API 1.0

    /**
     * Confirm release upload
     *
     * @param string Package name
     * @param string Package version
     * @param string Package state
     * @param string Release notes
     * @param string md5
     * @param int    Package id from database
     * @param string package contents
     * @static
     * @return string  the file name of the upload or PEAR_Error object if problems
     */
    function confirmUpload($package, $version, $state, $relnotes, $md5sum, $package_id, $file)
    {
        require_once "PEAR/Common.php";

        global $dbh, $auth_user, $_PEAR_Common_dependency_types,
               $_PEAR_Common_dependency_relations;

        require_once 'Archive/Tar.php';
        $tar = new Archive_Tar($file);
        $oldpackagexml = $tar->extractInString('package.xml');
        if (($packagexml = $tar->extractInString('package2.xml')) ||
              ($packagexml = $tar->extractInString('package.xml'))) {
            // success
        } else {
            return PEAR::raiseError('Archive uploaded does not appear to contain a package.xml!');
        }
        if ($oldpackagexml != $packagexml) {
            $compatible = true;
        } else {
            $compatible = false;
        }
        // Update releases table
        $query = "INSERT INTO releases (id,package,version,state,doneby,".
             "releasedate,releasenotes) VALUES(?,?,?,?,?,NOW(),?)";
        $sth = $dbh->prepare($query);
        $release_id = $dbh->nextId("releases");
        $dbh->execute($sth, [$release_id, $package_id, $version, $state,
                                  $auth_user->handle, $relnotes]);
        // Update files table
        $query = "INSERT INTO files ".
             "(`id`,`package`,`release`,`md5sum`,`basename`,`fullpath`,`packagexml`) ".
             "VALUES(?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $file_id = $dbh->nextId("files");
        $ok = $dbh->execute($sth, [$file_id, $package_id, $release_id,
                                        $md5sum, basename($file), $file, $packagexml]);
        /*
         * Code duplication with deps error
         * Should be droped soon or later using transaction
         * (and add mysql4 as a pe(ar|cl)web requirement)
         */
        if (PEAR::isError($ok)) {
            $dbh->query("DELETE FROM releases WHERE id = $release_id");
            @unlink($file);
            return $ok;
        }

        // Update dependency table
        $query = "INSERT INTO deps " .
            "(`package`, `release`, `type`, `relation`, `version`, `name`, `optional`) " .
            "VALUES (?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);

        require_once 'PEAR/PackageFile.php';
        require_once 'PEAR/Config.php';
        $config = PEAR_Config::singleton();
        $pf = new PEAR_PackageFile($config);
        $pkg_info = $pf->fromXmlString($packagexml, PEAR_VALIDATE_DOWNLOADING,
            $compatible ? 'package2.xml' : 'package.xml');

        $deps = $pkg_info->getDeps(true); // get the package2.xml actual content
        $storedeps = $pkg_info->getDeps(); // get the BC-compatible content
        $pearused = false;
        if (isset($deps['required']['package'])) {
            if (!isset($deps['required']['package'][0])) {
                $deps['required']['package'] = [$deps['required']['package']];
            }
            foreach ($deps['required']['package'] as $pkgdep) {
                if ($pkgdep['channel'] == 'pear.php.net' && strtolower($pkgdep['name']) == 'pear') {
                    $pearused = true;
                }
            }
        }
        if (is_array($storedeps)) {
            foreach ($storedeps as $dep) {
                $prob = [];

                if (empty($dep['type']) ||
                    !in_array($dep['type'], $_PEAR_Common_dependency_types))
                {
                    $prob[] = 'type';
                }

                if (empty($dep['name'])) {
                    /*
                     * NOTE from pajoye in ver 1.166:
                     * This works for now.
                     * This would require a 'cleaner' InfoFromXXX
                     * which may return a defined set of data using
                     * default values if required.
                     */
                    if (strtolower($dep['type']) == 'php') {
                        $dep['name'] = 'PHP';
                    } else {
                        $prob[] = 'name';
                    }
                } elseif (strtolower($dep['name']) == 'pear') {
                    if (!$pearused && $compatible) {
                        // there is no need for a PEAR dependency here
                        continue;
                    }
                    if (!$pearused && !$compatible) {
                        $dep['name'] = 'PEAR Installer';
                    }
                }

                if (empty($dep['rel']) ||
                    !in_array($dep['rel'], $_PEAR_Common_dependency_relations))
                {
                    $prob[] = 'rel';
                }

                if (empty($dep['optional'])) {
                    $optional = 0;
                } else {
                    if ($dep['optional'] != strtolower($dep['optional'])) {
                        $prob[] = 'optional';
                    }
                    if ($dep['optional'] == 'yes') {
                        $optional = 1;
                    } else {
                        $optional = 0;
                    }
                }

                if (count($prob)) {
                    $res = PEAR::raiseError('The following attribute(s) ' .
                            'were missing or need proper values: ' .
                            implode(', ', $prob));
                } else {
                    $res = $dbh->execute($sth,
                            [
                                $package_id,
                                $release_id,
                                $dep['type'],
                                $dep['rel'],
                                @$dep['version'],
                                $dep['name'],
                                $optional]);
                }

                if (PEAR::isError($res)) {
                    $dbh->query('DELETE FROM deps WHERE ' .
                                "`release` = $release_id");
                    $dbh->query('DELETE FROM releases WHERE ' .
                                "id = $release_id");
                    @unlink($file);
                    return $res;
                }
            }
        }
        $res = $GLOBALS['rest']->saveAllReleases($package);
        if (PEAR::isError($res)) {
            $dbh->query('DELETE FROM deps WHERE ' .
                "`release` = $release_id");
            $dbh->query('DELETE FROM releases WHERE ' .
                "id = $release_id");
            @unlink($file);
            return $res;
        }

        $res = $GLOBALS['rest']->saveRelease($file, $packagexml, $pkg_info, $auth_user->handle, $release_id);
        if (PEAR::isError($res)) {
            $dbh->query('DELETE FROM deps WHERE ' .
                "`release` = $release_id");
            $dbh->query('DELETE FROM releases WHERE ' .
                "id = $release_id");
            @unlink($file);
            return $res;
        }

        $res = $GLOBALS['rest']->savePackagesCategory(Package::info($package, 'category'));
        if (PEAR::isError($res)) {
            $dbh->query('DELETE FROM deps WHERE ' .
                "`release` = $release_id");
            $dbh->query('DELETE FROM releases WHERE ' .
                "id = $release_id");
            @unlink($file);
            return $res;
        }

        return $file;
    }

    // }}}
    // {{{ NOEXPORT      release::HTTPdownload(string, [string], [string], [bool])

    /**
     * Download release via HTTP
     *
     *
     * @param string Name of the package
     * @param string Version string
     * @param string Filename
     * @param boolean Uncompress file before downloading?
     * @return mixed
     * @static
     */
    function HTTPdownload($package, $version = null, $file = null, $uncompress = false)
    {
        global $dbh;

        require_once "HTTP.php";

        $package_id = Package::info($package, 'packageid', true);
        if (!$package_id) {
            $package_id = $dbh->getOne('SELECT package_id FROM package_aliases WHERE alias_name=' . $dbh->quoteSmart($package));
            if (!$package_id) {
                return PEAR::raiseError("release download:: package '".htmlspecialchars($package).
                                    "' does not exist");
            }
        }

        if (PEAR::isError($package_id)) {
            return $package_id;
        }

        if ($file !== null) {
            $basename = substr($file, 0, -4);
            if (substr($file, -4) == '.tar') {
                $file =  $basename . '.tgz';
                $uncompress = true;
            }

            $row = $dbh->getRow("SELECT `fullpath`, `release`, `id` FROM files ".
                                "WHERE UPPER(basename) = ?", [strtoupper($file)],
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            } elseif ($row === null) {
                return PEAR::raiseError("File '$file' not found");
            }
            $path = $row['fullpath'];
            $log_release = $row['release'];
            $log_file = $row['id'];
        } elseif ($version == null) {
            // Get the most recent version
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "ORDER BY releasedate DESC", DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        } elseif (release::isValidState($version)) {
            $version = strtolower($version);
            // Get the most recent version with a given state
            $row = $dbh->getRow("SELECT id FROM releases ".
                                "WHERE package = $package_id ".
                                "AND state = '$version' ".
                                "ORDER BY releasedate DESC",
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
            if (!isset($release_id)) {
                return PEAR::raiseError("$package does not have any releases with state \"$version\"");
            }
        } else {
            // Get a specific release
            $row = $dbh->getRow("SELECT id FROM releases ".
                                " WHERE package = " . $dbh->quoteSmart($package_id).
                                " AND version = " . $dbh->quoteSmart($version),
                                DB_FETCHMODE_ASSOC);
            if (PEAR::isError($row)) {
                return $row;
            }
            $release_id = $row['id'];
        }
        if (!isset($path) && isset($release_id)) {
            $sql = "SELECT fullpath, basename, `id` FROM files WHERE `release` = ".
                 $release_id;
            $row = $dbh->getRow($sql, DB_FETCHMODE_ORDERED);
            if (PEAR::isError($row)) {
                return $row;
            }
            list($path, $basename, $log_file) = $row;
            if (empty($path) || (!@is_file(PEAR_TARBALL_DIR . '/' . $basename) && !@is_file($path))) {
                return PEAR::raiseError("release download:: no version information found");
            }
            $basename = substr($basename, 0, -4);

        }

        if ($uncompress) {
            $basename .= '.tar';
        } else {
            $basename .= '.tgz';
        }
        $path = PEAR_TARBALL_DIR . '/' . $basename;

        if (isset($path)) {
            if (!isset($log_release)) {
                $log_release = $release_id;
            }
            release::logDownload($package_id, $log_release, $log_file);
            header('Content-Disposition: attachment;filename=' . $basename);
            header('Content-type: application/octet-stream');
            header('X-Sendfile: ' . '/local/www/sites/pecl.php.net/public_html/packages/' . $basename);

            return true;
        }
        header('HTTP/1.0 404 Not Found');
        print 'File not found';
    }

    // }}}
    // {{{  proto bool   release::isValidState(string) API 1.0

    /**
     * Determine if release state is valid
     *
     * @static
     * @param string State
     * @return boolean
     */
    function isValidState($state)
    {
        static $states = ['devel', 'snapshot', 'alpha', 'beta', 'stable'];
        return in_array($state, $states);
    }

    // }}}
    // {{{  proto array  release::betterStates(string) API 1.0

    /**
     * Convert a state into an array of less stable states
     *
     * @param string Release state
     * @param boolean include the state in the array returned
     * @return boolean
     */
    function betterStates($state, $include = false)
    {
        static $states = ['snapshot', 'devel', 'alpha', 'beta', 'stable'];
        $i = array_search($state, $states);
        if ($include) {
            $i--;
        }
        if ($i === false) {
            return false;
        }
        return array_slice($states, $i + 1);
    }

    // }}}
    // {{{ NOEXPORT      release::logDownload(integer, string, string)

    /**
     * Log release download
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @param string Filename
     */
    function logDownload($package, $release_id, $file = null)
    {
        global $dbh;

        $dbh->query('INSERT INTO aggregated_package_stats
                    (package_id, release_id, yearmonth, downloads)
                    VALUES(?,?,?,1)
                    ON DUPLICATE KEY UPDATE downloads=downloads+1',
            [$package, $release_id, date('Y-m-01')]);


        $pkg_info = Package::info($package, null);

        $query = 'SELECT version FROM releases'
               . ' WHERE package = ? AND id = ?';
        $version = $dbh->getOne($query, [$package, $release_id]);

        // {{{ Update package_stats table
        $query = 'INSERT INTO package_stats
        (dl_number, package, `release`, pid, rid, cid, last_dl)
        VALUES (1, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        dl_number=dl_number+1,
        last_dl = "' . date('Y-m-d H:i:s') . '"';

        $dbh->query($query, [$pkg_info['name'],
              $version,
              $package,
              $release_id,
              $pkg_info['categoryid'],
              date('Y-m-d H:i:s')
              ]
        );
        // }}}
    }

    // }}}
    // {{{ +proto string release::promote(array, string) API 1.0

    /**
     * Promote new release
     *
     * @param array Coming from PEAR_common::infoFromDescFile('package.xml')
     * @param string Filename of the new uploaded release
     * @return void
     */
    function promote($pkginfo, $upload)
    {
        if ($_SERVER['SERVER_NAME'] != 'pecl.php.net') {
            return;
        }
        $pacid   = Package::info($pkginfo['package'], 'packageid');
        $authors = Package::info($pkginfo['package'], 'authors');
        $txt_authors = '';
        foreach ($authors as $a) {
            $txt_authors .= $a['name'];
            if ($a['showemail']) {
                $txt_authors .= " <{$a['email']}>";
            }
            $txt_authors .= " ({$a['role']})\n";
        }
        $upload = basename($upload);
        $release = "{$pkginfo['package']}-{$pkginfo['version']} ({$pkginfo['release_state']})";
        $txtanounce =<<<END
The new PECL package $release has been released at https://pecl.php.net/.

Release notes
-------------
{$pkginfo['release_notes']}

Package Info
-------------
{$pkginfo['description']}

Related Links
-------------
Package home: https://pecl.php.net/package/$pkginfo[package]
   Changelog: https://pecl.php.net/package-changelog.php?package=$pkginfo[package]
    Download: https://pecl.php.net/get/$upload

Authors
-------------
$txt_authors
END;
        $to   = '"PECL developers list" <pecl-dev@lists.php.net>';
        $from = '"PECL Announce" <pecl-dev@lists.php.net>';
        $subject = "[ANNOUNCEMENT] $release Released.";
        mail($to, $subject, $txtanounce, "From: $from", "-f noreply@php.net");
    }

    // }}}
    // {{{ +proto string release::promote_v2(array, string) API 1.0

    /**
     * Promote new release
     *
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @param string Filename of the new uploaded release
     * @return void
     */
    function promote_v2($pkginfo, $upload)
    {
        if ($_SERVER['SERVER_NAME'] != 'pecl.php.net') {
            return;
        }
        $pacid   = Package::info($pkginfo->getPackage(), 'packageid');
        $authors = Package::info($pkginfo->getPackage(), 'authors');
        $txt_authors = '';
        foreach ($authors as $a) {
            $txt_authors .= $a['name'];
            if ($a['showemail']) {
                $txt_authors .= " <{$a['email']}>";
            }
            $txt_authors .= " ({$a['role']})\n";
        }
        $upload = basename($upload);
        $release = $pkginfo->getPackage() . '-' . $pkginfo->getVersion() .
             ' (' . $pkginfo->getState() . ')';
        $txtanounce ='The new PECL package ' . $release . ' has been released at https://pecl.php.net/.

Release notes
-------------
' . $pkginfo->getNotes() . '

Package Info
-------------
' . $pkginfo->getDescription() . '

Related Links
-------------
Package home: https://pecl.php.net/package/' . $pkginfo->getPackage() . '
   Changelog: https://pecl.php.net/package-changelog.php?package=' . $pkginfo->getPackage() . '
    Download: https://pecl.php.net/get/' . $upload . '

Authors
-------------
' . $txt_authors;

        $to   = '"PECL developers list" <pecl-dev@lists.php.net>';
        $from = '"PECL Announce" <pecl-dev@lists.php.net>';
        $subject = "[ANNOUNCEMENT] $release Released.";
        mail($to, $subject, $txtanounce, "From: $from", "-f noreply@php.net");
    }

    // }}}
    // {{{ NOEXPORT      release::remove(int, int)

    /**
     * Remove release
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @return boolean
     */
    function remove($package, $release)
    {
        global $dbh, $auth_user;
        if (!$auth_user->isAdmin() &&
            !user::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('release::remove: insufficient privileges');
        }

        $success = true;

        // get files that have to be removed
        $query = sprintf("SELECT `fullpath` FROM `files` WHERE `package` = '%s' AND `release` = '%s'",
                         $package,
                         $release);

        $sth = $dbh->query($query);

        while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
            if (!@unlink($row['fullpath'])) {
                $success = false;
            }
			$basename = basename($row['fullpath']);
			$basename = substr($basename, 0, -4);
			@unlink(PEAR_TARBALL_DIR . '/' . $basename . '.tar');
        }

        $query = sprintf("DELETE FROM `files` WHERE `package` = '%s' AND `release` = '%s'",
                         $package,
                         $release
                         );
        $sth = $dbh->query($query);

        $pname = Package::info($package, 'name');
        $version = $dbh->getOne('SELECT version from releases WHERE package = ? and id = ?',
            [$package, $release]);
        $query = sprintf("DELETE FROM releases WHERE package = '%s' AND id = '%s'",
                         $package,
                         $release
                         );
        $sth = $dbh->query($query);
        $GLOBALS['rest']->saveAllReleases($pname);
        $GLOBALS['rest']->deleteRelease($pname, $version);
        $GLOBALS['rest']->savePackagesCategory(Package::info($pname, 'category'));

        if (PEAR::isError($sth)) {
            return false;
        } else {
            return true;
        }
    }

    // }}}
}


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

class user
{
    // {{{ *proto bool   user::remove(string) API 1.0

    function remove($uid)
    {
        global $dbh;
        note::removeAll("uid", $uid);
        $GLOBALS['rest']->deleteMaintainerREST($uid);
        $GLOBALS['rest']->saveAllMaintainers();
        $dbh->query('DELETE FROM users WHERE handle = '. $dbh->quote($uid));
        return ($dbh->affectedRows() > 0);
    }

    // }}}
    // {{{ *proto bool   user::rejectRequest(string, string) API 1.0

    function rejectRequest($uid, $reason)
    {
        global $dbh, $auth_user;
        list($email) = $dbh->getRow('SELECT email FROM users WHERE handle = ?',
                                    [$uid]);
        note::add("uid", $uid, "Account rejected: $reason");
        $msg = "Your PECL account request was rejected by " . $auth_user->handle . ":\n".
             "$reason\n";
        $xhdr = "From: " . $auth_user->handle . "@php.net";
        mail($email, "Your PECL Account Request", $msg, $xhdr, "-f noreply@php.net");
        return true;
    }

    // }}}
    // {{{ *proto bool   user::activate(string) API 1.0

    function activate($uid)
    {
        global $dbh, $auth_user;

        $user = new PEAR_User($dbh, $uid);
        if (@$user->registered) {
            return false;
        }
        @$arr = unserialize($user->userinfo);
        note::removeAll("uid", $uid);
        $user->set('registered', 1);
        if (is_array($arr)) {
            $user->set('userinfo', $arr[1]);
        }
        $user->set('created', gmdate('Y-m-d H:i'));
        $user->set('createdby', $auth_user->handle);
        $user->set('registered', 1);
        $user->store();
        note::add("uid", $uid, "Account opened");
        $GLOBALS['rest']->saveMaintainer($user->handle);
        $GLOBALS['rest']->saveAllmaintainers();
        $msg = "Your PECL/PEAR account request has been opened.\n".
             "To log in, go to https://pecl.php.net/ and click on \"login\" in\n".
             "the top-right menu.\n";
        $xhdr = "From: " . $auth_user->handle . "@php.net";
        mail($user->email, "Your PECL Account Request", $msg, $xhdr, "-f noreply@php.net");
        return true;
    }

    // }}}
    // {{{ +proto bool   user::isAdmin(string) API 1.0

    function isAdmin($handle)
    {
        global $dbh;

        $query = "SELECT handle FROM users WHERE handle = ? AND admin = 1";
        $sth = $dbh->query($query, [$handle]);

        return ($sth->numRows() > 0);
    }

    // }}}
    // {{{ +proto bool   user::exists(string) API 1.0

    function exists($handle)
    {
        global $dbh;
        $sql = "SELECT handle FROM users WHERE handle=?";
        $res = $dbh->query($sql, [$handle]);
        return ($res->numRows() > 0);
    }

    // }}}
    // {{{ +proto string user::maintains(string|int, [string]) API 1.0

    function maintains($user, $pkgid, $role = 'any')
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

    // }}}
    // {{{  proto string user::info(string, [string]) API 1.0

    function info($user, $field = null)
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

    // }}}
    // {{{ listAll()

    function listAll($registered_only = true)
    {
        global $dbh;
        $query = "SELECT * FROM users";
        if ($registered_only === true) {
            $query .= " WHERE registered = 1";
        }
        $query .= " ORDER BY handle";
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{ add()

    /**
     * Add a new user account
     *
     * During most of this method's operation, PEAR's error handling
     * is set to PEAR_ERROR_RETURN.
     *
     * But, during the DB_storage::set() phase error handling is set to
     * PEAR_ERROR_CALLBACK the report_warning() function.  So, if an
     * error happens a warning message is printed AND the incomplete
     * user information is removed.
     *
     * @param array $data  Information about the user
     *
     * @return mixed  true if there are no problems, false if sending the
     *                email failed, 'set error' if DB_storage::set() failed
     *                or an array of error messages for other problems
     *
     * @access public
     */
    function add(&$data)
    {
        global $dbh;

        PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
        $errors = [];

        $required = [
            'handle'     => 'Username',
            'firstname'  => 'First Name',
            'lastname'   => 'Last Name',
            'email'      => 'Email address',
            'purpose'    => 'Intended purpose',
        ];

        $name = $data['firstname'] . " " . $data['lastname'];

        foreach ($required as $field => $desc) {
            if (empty($data[$field])) {
                $data['jumpto'] = $field;
                $errors[] = 'Please enter ' . $desc;
            }
        }

        if (!preg_match(PEAR_COMMON_USER_NAME_REGEX, $data['handle'])) {
            $errors[] = 'Username must start with a letter and contain'
                      . ' only letters and digits';
        }

        // Basic name validation

        // First- and lastname must be longer than 1 character
        if (strlen($data['firstname']) == 1) {
            $errors[] = 'Your firstname appears to be too short.';
        }
        if (strlen($data['lastname']) == 1) {
            $errors[] = 'Your lastname appears to be too short.';
        }

        // Firstname and lastname must start with an uppercase letter
        if (!preg_match("/^[A-Z]/", $data['firstname'])) {
            $errors[] = 'Your firstname must begin with an uppercase letter';
        }
        if (!preg_match("/^[A-Z]/", $data['lastname'])) {
            $errors[] = 'Your lastname must begin with an uppercase letter';
        }

        // No names with only uppercase letters
        if ($data['firstname'] === strtoupper($data['firstname'])) {
            $errors[] = 'Your firstname must not consist of only uppercase letters.';
        }
        if ($data['lastname'] === strtoupper($data['lastname'])) {
            $errors[] = 'Your lastname must not consist of only uppercase letters.';
        }

        if ($data['password'] != $data['password2']) {
            $data['password'] = $data['password2'] = "";
            $data['jumpto'] = "password";
            $errors[] = 'Passwords did not match';
        }

        if (!$data['password']) {
            $data['jumpto'] = "password";
            $errors[] = 'Empty passwords not allowed';
        }

        $handle = strtolower($data['handle']);
        $obj = new PEAR_User($dbh, $handle);

        if (isset($obj->created)) {
            $data['jumpto'] = "handle";
            $errors[] = 'Sorry, that username is already taken';
        }

        if ($errors) {
            $data['display_form'] = true;
            return $errors;
        }

        $err = $obj->insert($handle);

        if (DB::isError($err)) {
            if ($err->getCode() == DB_ERROR_CONSTRAINT) {
                $data['display_form'] = true;
                $data['jumpto'] = 'handle';
                $errors[] = 'Sorry, that username is already taken';
            } else {
                $data['display_form'] = false;
                $errors[] = $err;
            }
            return $errors;
        }

        $data['display_form'] = false;
        $md5pw = md5($data['password']);
        $showemail = @(bool)$data['showemail'];
        // hack to temporarily embed the "purpose" in
        // the user's "userinfo" column
        $userinfo = serialize([$data['purpose'], $data['moreinfo']]);
        $set_vars = ['name' => $name,
                          'email' => $data['email'],
                          'homepage' => $data['homepage'],
                          'showemail' => $showemail,
                          'password' => $md5pw,
                          'registered' => 0,
                          'userinfo' => $userinfo];

        PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'report_warning');
        foreach ($set_vars as $var => $value) {
            $err = $obj->set($var, $value);
            if (PEAR::isError($err)) {
                user::remove($data['handle']);
                return 'set error';
            }
        }
        PEAR::popErrorHandling();

        $msg = "Requested from:   {$_SERVER['REMOTE_ADDR']}\n".
               "Username:         {$handle}\n".
               "Real Name:        {$name}\n".
               (isset($data['showemail']) ? "Email:            {$data['email']}\n" : "") .
               "Purpose:\n".
               "{$data['purpose']}\n\n".
               "To handle: http://{$_SERVER['SERVER_NAME']}/admin/?acreq={$handle}\n";

        if ($data['moreinfo']) {
            $msg .= "\nMore info:\n{$data['moreinfo']}\n";
        }

        $xhdr = "From: $name <{$data['email']}>\nMessage-Id: <account-request-{$handle}@" .
            PEAR_CHANNELNAME . ">\n";
        $subject = "PEAR Account Request: {$handle}";

        if (DEVBOX == false) {
            if (PEAR_CHANNELNAME == 'pear.php.net') {
                $ok = @mail('pear-group@php.net', $subject, $msg, $xhdr,
                            '-f noreply@php.net');
            }
        } else {
            $ok = true;
        }

        PEAR::popErrorHandling();

        return $ok;
    }

    // }}}
    // {{{ update

    /**
     * Update user information
     *
     * @access public
     * @param  array User information
     * @return object Instance of PEAR_User
     */
    function update($data) {
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

    // }}}
    // {{{ getRecentReleases(string, [int])

    /**
     * Get recent releases for the given user
     *
     * @access public
     * @param  string Handle of the user
     * @param  int    Number of releases (default is 10)
     * @return array
     */
    function getRecentReleases($handle, $n = 10)
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
