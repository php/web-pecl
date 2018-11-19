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

use App\Entity\Package;
use App\User;
use App\Database;
use App\Rest;
use \PEAR as PEAR;
use \PEAR_Common as PEAR_Common;
use \Archive_Tar as Archive_Tar;
use \PEAR_PackageFile as PEAR_PackageFile;
use \PEAR_Config as PEAR_Config;

/**
 * Class to handle releases
 */
class Release
{
    private $database;
    private $authUser;
    private $rest;
    private $packagesDir;
    private $package;

    /**
     * Set database handler.
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Set the auth user.
     */
    public function setAuthUser($authUser)
    {
        $this->authUser = $authUser;
    }

    /**
     * Set REST generator.
     */
    public function setRest(Rest $rest)
    {
        $this->rest = $rest;
    }

    /**
     * Set directory where to upload packages.
     */
    public function setPackagesDir($dir)
    {
        $this->packagesDir = $dir;
    }

    /**
     * Set package entity.
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Upload new release.
     *
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     */
    public function upload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        $role = User::maintains($this->authUser->handle, $package);

        if ($role != 'lead' && $role != 'developer' && !$this->authUser->isAdmin()) {
            return PEAR::raiseError('Release::upload: insufficient privileges');
        }

        $ref = $this->validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum);

        if (PEAR::isError($ref)) {
            return $ref;
        }

        return $this->confirmUpload($package, $version, $state, $relnotes, $md5sum, $ref['package_id'], $ref['file']);
    }

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
    private function validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        $role = User::maintains($this->authUser->handle, $package);
        if ($role != 'lead' && $role != 'developer' && !$this->authUser->isAdmin()) {
            return PEAR::raiseError('Release::validateUpload: insufficient privileges');
        }

        // (2) verify that package exists
        $package_id = $this->package->info($package, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("package `$package' must be registered first");
        }

        // (3) verify that version does not exist
        $sql = "SELECT version FROM releases WHERE package = ? AND version = ?";
        $test = $this->database->run($sql, [$package_id, $version])->fetch();

        if ($test) {
            return PEAR::raiseError("already exists: $package $version");
        }

        // (4) store tar ball to temp file
        $tempfile = sprintf("%s/%s%s-%s.tgz", $this->packagesDir, ".new.", $package, $version);
        $file = sprintf("%s/%s-%s.tgz", $this->packagesDir, $package, $version);
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

        $info = [
            'package_id' => $package_id,
            'package'    => $package,
            'version'    => $version,
            'state'      => $state,
            'relnotes'   => $relnotes,
            'md5sum'     => $md5sum,
            'file'       => $file
        ];

        $infofile = sprintf("%s/%s%s-%s", $this->packagesDir, ".info.", $package, $version);
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
     * @return string  the file name of the upload or PEAR_Error object if problems
     */
    private function confirmUpload($package, $version, $state, $relnotes, $md5sum, $package_id, $file)
    {
        // TODO: Avoid using globals by using dependency injection.
        global $_PEAR_Common_dependency_types, $_PEAR_Common_dependency_relations;

        $tar = new Archive_Tar($file);

        $oldpackagexml = $tar->extractInString('package.xml');
        if (($packagexml = $tar->extractInString('package2.xml'))
            || ($packagexml = $tar->extractInString('package.xml'))
        ) {
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
        $sql = "INSERT INTO releases (
                    id, package, version, state, doneby, releasedate, releasenotes
                ) VALUES (
                    ?, ?, ?, ?, ?, NOW(), ?
                )";

        $id = $this->database->run("SELECT id FROM releases ORDER BY id DESC")->fetch()['id'];
        $release_id = (!$id) ? 1 : $id + 1;

        $statement = $this->database->run($sql, [$release_id, $package_id, $version, $state, $this->authUser->handle, $relnotes]);

        // Update files table
        $sql = "INSERT INTO files
                    (`id`,`package`,`release`,`md5sum`,`basename`,`fullpath`,`packagexml`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $id = $this->database->run("SELECT id FROM files ORDER BY id DESC")->fetch()['id'];
        $file_id = !$id ? 1 : $id + 1;

        // TODO: Code duplication with deps error. Should be dropped sooner or later
        // using transaction (and add newer MySQL version as a peclweb requirement)
        try {
            $this->database->run($sql, [$file_id, $package_id, $release_id, $md5sum, basename($file), $file, $packagexml]);
        } catch (\PDOException $e) {
            $this->database->run('DELETE FROM releases WHERE id = ?', [$release_id]);

            @unlink($file);

            return;
        }

        // Update dependency table
        $sql = "INSERT INTO deps
                    (`package`, `release`, `type`, `relation`, `version`, `name`, `optional`)
                VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $statement = $this->database->prepare($sql);

        $config = PEAR_Config::singleton();
        $pf = new PEAR_PackageFile($config);

        $pkg_info = $pf->fromXmlString($packagexml, PEAR_VALIDATE_DOWNLOADING, $compatible ? 'package2.xml' : 'package.xml');

        // Get the package2.xml actual content
        $deps = $pkg_info->getDeps(true);

        // Get the BC-compatible content
        $storedeps = $pkg_info->getDeps();
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

                if (empty($dep['type'])
                    || !in_array($dep['type'], $_PEAR_Common_dependency_types)
                ) {
                    $prob[] = 'type';
                }

                if (empty($dep['name'])) {
                    // NOTE from pajoye in ver 1.166: This works for now. This
                    // would require a 'cleaner' InfoFromXXX which may return a
                    // defined set of data using default values if required.
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

                if (empty($dep['rel'])
                    || !in_array($dep['rel'], $_PEAR_Common_dependency_relations)
                ) {
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
                    $res = PEAR::raiseError('The following attribute(s) were missing or need proper values: '.implode(', ', $prob));
                } else {
                    $res = $statement->execute([
                        $package_id,
                        $release_id,
                        $dep['type'],
                        $dep['rel'],
                        @$dep['version'],
                        $dep['name'],
                        $optional
                    ]);
                }

                if (PEAR::isError($res)) {
                    $this->database->query('DELETE FROM deps WHERE '."`release` = $release_id");
                    $this->database->query('DELETE FROM releases WHERE '."id = $release_id");

                    @unlink($file);

                    return $res;
                }
            }
        }

        $res = $this->rest->saveAllReleases($package);

        if (PEAR::isError($res)) {
            $this->database->query('DELETE FROM deps WHERE `release` = '.$release_id);
            $this->database->query('DELETE FROM releases WHERE id = '.$release_id);

            @unlink($file);

            return $res;
        }

        $res = $this->rest->saveRelease($file, $packagexml, $pkg_info, $this->authUser->handle, $release_id);

        if (PEAR::isError($res)) {
            $this->database->query('DELETE FROM deps WHERE `release` = '.$release_id);
            $this->database->query('DELETE FROM releases WHERE id = '.$release_id);

            @unlink($file);

            return $res;
        }

        $res = $this->rest->savePackagesCategory($this->package->info($package, 'category'));

        if (PEAR::isError($res)) {
            $this->database->query('DELETE FROM deps WHERE `release` = '.$release_id);
            $this->database->query('DELETE FROM releases WHERE id = '.$release_id);

            @unlink($file);

            return $res;
        }

        return $file;
    }

    /**
     * Download release via HTTP
     *
     * @param string Name of the package
     * @param string Version string
     * @param string Filename
     * @param boolean Uncompress file before downloading?
     * @return mixed
     */
    public function HTTPdownload($package, $version = null, $file = null, $uncompress = false)
    {
        $package_id = $this->package->info($package, 'packageid', true);

        // If no package id has been set, check if this is package alias maybe
        if (!$package_id) {
            $sql = 'SELECT package_id FROM package_aliases WHERE alias_name = ?';
            $package_id = $this->database->run($sql, [$package])->fetch()['id'];
        }

        if (!$package_id) {
            return PEAR::raiseError("release download:: package '".htmlspecialchars($package)."' does not exist");
        }

        if ($file !== null) {
            $basename = substr($file, 0, -4);

            if (substr($file, -4) == '.tar') {
                $file =  $basename . '.tgz';
                $uncompress = true;
            }

            $sql = "SELECT `fullpath`, `release`, `id` FROM files WHERE UPPER(basename) = ?";

            $row = $this->database->run($sql, [strtoupper($file)])->fetch();

            if (!$row) {
                return PEAR::raiseError("File '$file' not found");
            }

            $path = $row['fullpath'];
            $log_release = $row['release'];
        } elseif ($version == null) {
            // Get the most recent version
            $sql = "SELECT id FROM releases WHERE package = ? ORDER BY releasedate DESC";
            $row = $this->database->run($sql, [$package_id])->fetch();

            if (!$row) {
                return PEAR::raiseError("$package does not have any releases");
            }

            $release_id = $row['id'];
        } elseif ($this->isValidState($version)) {
            $version = strtolower($version);

            // Get the most recent version with a given state
            $sql = "SELECT id FROM releases WHERE package = ? AND state = ? ORDER BY releasedate DESC";
            $row = $this->database->run($sql, [$package_id, $version])->fetch();

            if (!$row) {
                return PEAR::raiseError("$package does not have any releases with state \"$version\"");
                return null;
            }

            $release_id = $row['id'];

            if (!isset($release_id)) {
                return PEAR::raiseError("$package does not have any releases with state \"$version\"");
            }
        } else {
            // Get a specific release
            $sql = "SELECT id FROM releases WHERE package = ? AND version = ?";
            $row = $this->database->run($sql, [$package_id, $version])->fetch();

            if (!$row) {
                return PEAR::raiseError("$package does not have any releases with state \"$version\"");
            }

            $release_id = $row['id'];
        }

        if (!isset($path) && isset($release_id)) {
            $sql = "SELECT fullpath, basename, `id` FROM files WHERE `release` = ?";
            $row = $this->database->run($sql, [$release_id])->fetch();

            if (!$row) {
                return null;
            }

            $basename = $row['basename'];
            $path = $row['fullpath'];

            if (empty($path) || (!@is_file($this->packagesDir.'/'.$basename) && !@is_file($path))) {
                return PEAR::raiseError("release download:: no version information found");
            }

            $basename = substr($basename, 0, -4);
        }

        if ($uncompress) {
            $basename .= '.tar';
        } else {
            $basename .= '.tgz';
        }

        $path = $this->packagesDir.'/'.$basename;

        if (isset($path)) {
            if (!isset($log_release)) {
                $log_release = $release_id;
            }

            $this->logDownload($package_id, $log_release);

            header('Content-Disposition: attachment;filename=' . $basename);
            header('Content-type: application/octet-stream');
            header('X-Sendfile: ' . '/local/www/sites/pecl.php.net/public_html/packages/' . $basename);

            return true;
        }

        header('HTTP/1.0 404 Not Found');
        print 'File not found';
    }

    /**
     * Determine if release state is valid
     *
     * @param string State
     * @return boolean
     */
    private function isValidState($state)
    {
        $states = ['devel', 'snapshot', 'alpha', 'beta', 'stable'];

        return in_array($state, $states);
    }

    /**
     * Log release download
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @param string Filename
     */
    private function logDownload($package, $release_id)
    {
        $sql = 'INSERT INTO aggregated_package_stats
                (package_id, release_id, yearmonth, downloads)
                VALUES (?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE downloads = downloads + 1';

        $this->database->run($sql, [$package, $release_id, date('Y-m-01')]);

        $pkg_info = $this->package->info($package, null);

        $sql = 'SELECT version FROM releases WHERE package = ? AND id = ?';
        $version = $this->database->run($sql, [$package, $release_id])->fetch()['version'];

        // Update package_stats table
        $sql = 'INSERT INTO package_stats
                (dl_number, package, `release`, pid, rid, cid, last_dl)
                VALUES (1, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    dl_number=dl_number+1,
                    last_dl = "' . date('Y-m-d H:i:s') . '"';

        $this->database->run($sql, [
            $pkg_info['name'],
            $version,
            $package,
            $release_id,
            $pkg_info['categoryid'],
            date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Promote new release
     *
     * @param array Coming from PEAR_common::infoFromDescFile('package.xml')
     * @param string Filename of the new uploaded release
     * @return void
     */
    public function promote($pkginfo, $upload)
    {
        if ($_SERVER['SERVER_NAME'] != 'pecl.php.net') {
            return;
        }

        $pacid   = $this->package->info($pkginfo['package'], 'packageid');
        $authors = $this->package->info($pkginfo['package'], 'authors');
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

    /**
     * Promote new release
     *
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @param string Filename of the new uploaded release
     * @return void
     */
    public function promote_v2($pkginfo, $upload)
    {
        if ($_SERVER['SERVER_NAME'] != 'pecl.php.net') {
            return;
        }

        $pacid   = $this->package->info($pkginfo->getPackage(), 'packageid');
        $authors = $this->package->info($pkginfo->getPackage(), 'authors');
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

    /**
     * Remove release
     *
     * @param integer ID of the package
     * @param integer ID of the release
     */
    public function remove($package, $release)
    {
        if (!$this->authUser->isAdmin() && !User::maintains($this->authUser->handle, $package, 'lead')) {
            return PEAR::raiseError('Release::remove: insufficient privileges');
        }

        // get files that have to be removed
        $sql = "SELECT `fullpath` FROM `files` WHERE `package` = ? AND `release` = ?";
        $statement = $this->database->run($sql, [$package, $release]);

        foreach ($statement->fetchAll() as $row) {
            @unlink($row['fullpath']);

            $basename = basename($row['fullpath']);
            $basename = substr($basename, 0, -4);

            @unlink($this->packagesDir.'/'.$basename.'.tar');
        }

        $sql = "DELETE FROM `files` WHERE `package` = ? AND `release` = ?";
        $this->database->run($sql, [$package, $release]);

        $pname = $this->package->info($package, 'name');

        $sql = 'SELECT version from releases WHERE package = ? and id = ?';
        $version = $this->database->run($sql, [$package, $release])->fetch()['version'];

        $sql = "DELETE FROM releases WHERE package = ? AND id = ?";
        $statement = $this->database->run($sql, [$package, $release]);

        $this->rest->saveAllReleases($pname);
        $this->rest->deleteRelease($pname, $version);
        $this->rest->savePackagesCategory($this->package->info($pname, 'category'));

        return $statement;
    }
}
