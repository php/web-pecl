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
 * Class to handle releases
 */
class Release
{
    /**
     * Upload new release
     *
     * @param string Name of the package
     * @param string Version string
     * @param string State of the release
     * @param string Release notes
     * @param string Filename of the release tarball
     * @param string MD5 checksum of the tarball
     */
    public static function upload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $auth_user;

        $role = User::maintains($auth_user->handle, $package);

        if ($role != 'lead' && $role != 'developer' && !$auth_user->isAdmin()) {
            return PEAR::raiseError('Release::upload: insufficient privileges');
        }

        $ref = self::validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum);

        if (PEAR::isError($ref)) {
            return $ref;
        }

        return self::confirmUpload($package, $version, $state, $relnotes, $md5sum, $ref['package_id'], $ref['file']);
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
    private static function validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $dbh, $auth_user;

        $role = User::maintains($auth_user->handle, $package);
        if ($role != 'lead' && $role != 'developer' && !$auth_user->isAdmin()) {
            return PEAR::raiseError('Release::validateUpload: insufficient privileges');
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
        $tempfile = sprintf("%s/%s%s-%s.tgz", PEAR_TARBALL_DIR, ".new.", $package, $version);
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

        $info = [
            'package_id' => $package_id,
            'package'    => $package,
            'version'    => $version,
            'state'      => $state,
            'relnotes'   => $relnotes,
            'md5sum'     => $md5sum,
            'file'       => $file
        ];
        $infofile = sprintf("%s/%s%s-%s", PEAR_TARBALL_DIR, ".info.", $package, $version);
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
    private static function confirmUpload($package, $version, $state, $relnotes, $md5sum, $package_id, $file)
    {
        global $dbh, $auth_user, $_PEAR_Common_dependency_types, $_PEAR_Common_dependency_relations, $rest;

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
        $query = "INSERT INTO releases (id,package,version,state,doneby,".
             "releasedate,releasenotes) VALUES(?,?,?,?,?,NOW(),?)";
        $sth = $dbh->prepare($query);
        $release_id = $dbh->nextId("releases");
        $dbh->execute($sth, [$release_id, $package_id, $version, $state, $auth_user->handle, $relnotes]);

        // Update files table
        $query = "INSERT INTO files ".
             "(`id`,`package`,`release`,`md5sum`,`basename`,`fullpath`,`packagexml`) ".
             "VALUES(?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $file_id = $dbh->nextId("files");
        $ok = $dbh->execute($sth, [$file_id, $package_id, $release_id, $md5sum, basename($file), $file, $packagexml]);

        // Code duplication with deps error
        // Should be dropped sooner or later using transaction
        // (and add mysql4 as a pe(ar|cl)web requirement)
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
                    $res = PEAR::raiseError('The following attribute(s) ' .
                            'were missing or need proper values: ' .
                            implode(', ', $prob));
                } else {
                    $res = $dbh->execute($sth, [
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
                    $dbh->query('DELETE FROM deps WHERE '."`release` = $release_id");
                    $dbh->query('DELETE FROM releases WHERE '."id = $release_id");
                    @unlink($file);

                    return $res;
                }
            }
        }

        $res = $rest->saveAllReleases($package);

        if (PEAR::isError($res)) {
            $dbh->query('DELETE FROM deps WHERE ' .
                "`release` = $release_id");
            $dbh->query('DELETE FROM releases WHERE ' .
                "id = $release_id");
            @unlink($file);

            return $res;
        }

        $res = $rest->saveRelease($file, $packagexml, $pkg_info, $auth_user->handle, $release_id);

        if (PEAR::isError($res)) {
            $dbh->query('DELETE FROM deps WHERE ' .
                "`release` = $release_id");
            $dbh->query('DELETE FROM releases WHERE ' .
                "id = $release_id");
            @unlink($file);
            return $res;
        }

        $res = $rest->savePackagesCategory(Package::info($package, 'category'));

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

    /**
     * Download release via HTTP
     *
     * @param string Name of the package
     * @param string Version string
     * @param string Filename
     * @param boolean Uncompress file before downloading?
     * @return mixed
     */
    public static function HTTPdownload($package, $version = null, $file = null, $uncompress = false)
    {
        global $dbh;

        $package_id = Package::info($package, 'packageid', true);
        if (!$package_id) {
            $package_id = $dbh->getOne('SELECT package_id FROM package_aliases WHERE alias_name=' . $dbh->quoteSmart($package));

            if (!$package_id) {
                return PEAR::raiseError("release download:: package '".htmlspecialchars($package)."' does not exist");
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
        } elseif (self::isValidState($version)) {
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

            self::logDownload($package_id, $log_release, $log_file);

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
    private static function isValidState($state)
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
    private static function logDownload($package, $release_id, $file = null)
    {
        global $dbh;

        $dbh->query('INSERT INTO aggregated_package_stats
                    (package_id, release_id, yearmonth, downloads)
                    VALUES(?,?,?,1)
                    ON DUPLICATE KEY UPDATE downloads=downloads+1',
            [$package, $release_id, date('Y-m-01')]);

        $pkg_info = Package::info($package, null);

        $query = 'SELECT version FROM releases WHERE package = ? AND id = ?';
        $version = $dbh->getOne($query, [$package, $release_id]);

        // Update package_stats table
        $query = 'INSERT INTO package_stats
        (dl_number, package, `release`, pid, rid, cid, last_dl)
        VALUES (1, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        dl_number=dl_number+1,
        last_dl = "' . date('Y-m-d H:i:s') . '"';

        $dbh->query($query, [
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
    public static function promote($pkginfo, $upload)
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

    /**
     * Promote new release
     *
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @param string Filename of the new uploaded release
     * @return void
     */
    public static function promote_v2($pkginfo, $upload)
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

    /**
     * Remove release
     *
     * @param integer ID of the package
     * @param integer ID of the release
     * @return boolean
     */
    public static function remove($package, $release)
    {
        global $dbh, $auth_user, $rest;

        if (!$auth_user->isAdmin() && !User::maintains($auth_user->handle, $package, 'lead')) {
            return PEAR::raiseError('Release::remove: insufficient privileges');
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
        $version = $dbh->getOne('SELECT version from releases WHERE package = ? and id = ?', [$package, $release]);
        $query = sprintf("DELETE FROM releases WHERE package = '%s' AND id = '%s'",
                         $package,
                         $release
                         );
        $sth = $dbh->query($query);

        $rest->saveAllReleases($pname);
        $rest->deleteRelease($pname, $version);
        $rest->savePackagesCategory(Package::info($pname, 'category'));

        if (PEAR::isError($sth)) {
            return false;
        } else {
            return true;
        }
    }
}
