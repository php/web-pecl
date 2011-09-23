<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Stig Bakken <ssb@fast.no>                                   |
   |          Tomas V.V.Cox <cox@php.net>                                 |
   |          Martin Jansen <mj@php.net>                                  |
   |          Pierre Joye <pierre@php.net>                                |
   |          Gregory Beaver <cellog@php.net>                             |
   +----------------------------------------------------------------------+
   $Id$
*/
/**
 * Class to handle packages
 *
 * @class   package
 * @package pearweb
 * @author  Stig S. Bakken <ssb@fast.no>
 * @author  Tomas V.V. Cox <cox@php.net>
 * @author  Martin Jansen <mj@php.net>
 */
class package
{
    // {{{ *proto int    package::add(struct) API 1.0

    /**
     * Add new package
     *
     * @param array
     * @return mixed ID of new package or PEAR error object
     */
    function add($data)
    {
        global $dbh, $pear_rest;
        // name, category
        // license, summary, description
        // lead
        extract($data);
        if (empty($license)) {
            $license = "PHP License";
        }
        if (!empty($category) && (int)$category == 0) {
            $category = $dbh->getOne("SELECT id FROM categories WHERE name = ?",
                                     array($category));
        }
        if (empty($category)) {
            return PEAR::raiseError("package::add: invalid `category' field");
        }
        if (empty($name)) {
            return PEAR::raiseError("package::add: invalid `name' field");
        }
# NOTE WELL! PECL packages are always approved
        $query = "INSERT INTO packages (id,name,package_type,category,license,summary,description,homepage,cvs_link,approved) VALUES(?,?,?,?,?,?,?,?,?,1)";
        $id = $dbh->nextId("packages");
        $err = $dbh->query($query, array($id, $name, 'pecl', $category, $license, $summary, $description, $homepage, $cvs_link));
        if (DB::isError($err)) {
            return $err;
        }
        $sql = "UPDATE categories SET npackages = npackages + 1
                WHERE id = $category";
        if (DB::isError($err = $dbh->query($sql))) {
            return $err;
        }
        $pear_rest->savePackageREST($name);
        if (isset($lead) && DB::isError($err = maintainer::add($id, $lead, 'lead'))) {
            return $err;
        }
        $pear_rest->saveAllPackagesREST();
        $pear_rest->savePackagesCategoryREST(package::info($name, 'category'));
        return $id;
    }

    // }}}
    // {{{ proto string package::getPackageFile(string|int, string) API 1.0

    /**
     * @param string|int package name or id
     * @param string     release version
     * @return string|PEAR_Error|null package.xml contents from this release
     */
    function getPackageFile($package, $version)
    {
        global $dbh;
        if (is_numeric($package)) {
            $what = "id";
        } else {
            $what = "name";
        }
        $relids = $dbh->getRow('SELECT releases.id as rid, packages.id as pid' .
            ' FROM releases, packages WHERE ' .
            "packages.$what = ? AND releases.version = ? AND " .
            'releases.package = packages.id', array($package, $version), DB_FETCHMODE_ASSOC);
        if (PEAR::isError($relids)) {
            return $relids;
        }
        if ($relids === null) {
            $ptest = $dbh->getOne('SELECT id FROM packages WHERE ' . $what . ' = ?', array($package));
            if ($ptest === null) {
                return PEAR::raiseError('Unknown package "' . $package . '"');
            }
            $rtest = $dbh->getOne('SELECT id FROM releases WHERE version = ?', array($version));
            if ($rtest === null) {
                return PEAR::raiseError('No release of version "' . $version . '" for package "' .
                    $package . '"');
            }
        }
        if (is_array($relids) && isset($relids['rid'])) {
            $packagexml = $dbh->getOne('SELECT packagexml FROM files WHERE ' .
                'package = ? AND release = ?', array($relids['pid'], $relids['rid']));
            if (is_string($packagexml)) {
                return $packagexml;
            }
        }
    }

    // }}}
    // {{{ proto array package::getDepDownloadURL(string, struct, struct, [string], [string]) API 1.1

    /**
     * Get a download URL for a dependency, or an array containing the
     * latest version and its release info.
     *
     * If a bundle is specified, then an array of information
     * will be returned
     * @param string package.xml version for the dependency (1.0 or 2.0)
     * @param array dependency information
     * @param array dependent package information
     * @param string preferred state
     * @param string installed version of this dependency
     * @return bool|array
     */
    function getDepDownloadURL($xsdversion, $dependency, $deppackage,
                               $prefstate = 'stable', $installed = false)
    {
        $info = package::info($dependency['name'], 'releases', true);
        if (!count($info)) {
            return false;
        }
        $states = release::betterStates($prefstate, true);
        if (!$states) {
            return PEAR::raiseError("getDepDownloadURL: preferred state '$prefstate' " .
                'is not a valid stability state');
        }
        $exclude = array();
        $min = $max = $recommended = false;
        if ($xsdversion == '1.0') {
            $pinfo['package'] = $dependency['name'];
            $pinfo['channel'] = 'pear.php.net'; // this is always true - don't change this
            switch ($dependency['rel']) {
                case 'ge' :
                    $min = $dependency['version'];
                break;
                case 'gt' :
                    $min = $dependency['version'];
                    $exclude = array($dependency['version']);
                break;
                case 'eq' :
                    $recommended = $dependency['version'];
                break;
                case 'lt' :
                    $max = $dependency['version'];
                    $exclude = array($dependency['version']);
                break;
                case 'le' :
                    $max = $dependency['version'];
                break;
                case 'ne' :
                    $exclude = array($dependency['version']);
                break;
            }
        } elseif ($xsdversion == '2.0') {
            $pinfo['package'] = $dependency['name'];
            if ($dependency['channel'] != 'pecl.php.net' &&
                  $dependency['channel'] != 'pear.php.net') {
                return PEAR::raiseError('getDepDownloadURL channel must be pecl.php.net');
            }
            $min = isset($dependency['min']) ? $dependency['min'] : false;
            $max = isset($dependency['max']) ? $dependency['max'] : false;
            $recommended = isset($dependency['recommended']) ?
                $dependency['recommended'] : false;
            if (isset($dependency['exclude'])) {
                if (!isset($dependency['exclude'][0])) {
                    $exclude = array($dependency['exclude']);
                }
            }
        }
        $found = false;

        foreach ($info as $ver => $release) {

            if (in_array($ver, $exclude)) { // skip excluded versions
                continue;
            }
            // allow newer releases to say "I'm OK with the dependent package"
            if ($xsdversion == '2.0' && isset($release['compatibility'])) {
                if (isset($release['compatibility'][$deppackage['channel']]
                      [$deppackage['package']]) && in_array($ver,
                        $release['compatibility'][$deppackage['channel']]
                        [$deppackage['package']])) {
                    $recommended = $ver;
                }
            }
            if ($recommended) {
                if ($ver != $recommended) { // if we want a specific
                    // version, then skip all others
                    continue;
                } else {
                    if (!in_array($release['state'], $states)) {
                        // the stability is too low, but we must return the
                        // recommended version if possible
                        return array('version' => $ver,
                                     'info' => package::getPackageFile($dependency['name'], $ver));
                    }
                }
            }
            if ($min && version_compare($ver, $min, 'lt')) { // skip too old versions
                continue;
            }
            if ($max && version_compare($ver, $max, 'gt')) { // skip too new versions
                continue;
            }
            if ($installed && version_compare($ver, $installed, '<')) {
                continue;
            }
            if (in_array($release['state'], $states)) { // if in the preferred state...
                $found = true; // ... then use it
                break;
            }
        }
        if ($found) {
            return
                array('version' => $ver,
                      'info' => package::getPackageFile($dependency['name'], $ver),
                      'url' => 'http://' . $_SERVER['SERVER_NAME'] . '/get/' .
                               $pinfo['package'] . '-' . $ver);
        } else {
            reset($info);
            list($ver, ) = each($info);
            return array('version' => $ver,
                         'info' => package::getPackageFile($dependency['name'], $ver));
        }
    }

    // }}}

    // {{{  proto struct package::info(string|int, [string], [bool]) API 1.0
    /*
     * Implemented $field values:
     * releases, notes, category, description, authors, categoryid,
     * packageid, authors
     */

    /**
     * Get package information
     *
     * @static
     * @param  mixed   Name of the package or it's ID
     * @param  string  Single field to fetch
     * @param  boolean Should PEAR packages also be taken into account?
     * @return mixed
     */
    function info($pkg, $field = null, $allow_pear = false)
    {
        global $dbh;
        $allow_pear = false;

        if (is_numeric($pkg)) {
            $what = "id";
        } else {
            $what = "name";
        }

        $pkg_sql = "SELECT p.id AS packageid, p.name AS name, ".
             "p.package_type AS type, ".
             "c.id AS categoryid, c.name AS category, ".
             "p.stablerelease AS stable, p.license AS license, ".
             "p.summary AS summary, p.homepage AS homepage, ".
             "p.description AS description, p.cvs_link AS cvs_link, ".
             "p.doc_link as doc_link, ".
             "p.bug_link as bug_link, ".
             "p.unmaintained as unmaintained, ".
             "p.newpackagename as new_package, ".
             "p.newchannel as new_channel".
             " FROM packages p, categories c ".
             "WHERE p.package_type = 'pecl' AND c.id = p.category AND p.{$what} = ?";

        $rel_sql = "SELECT version, id, doneby, license, summary, ".
             "description, releasedate, releasenotes, state " . //, packagexmlversion ".
             "FROM releases ".
             "WHERE package = ? ".
             "ORDER BY releasedate DESC";
        $notes_sql = "SELECT id, nby, ntime, note FROM notes WHERE pid = ?";
        $deps_sql = "SELECT type, relation, version, `name`, `release`, optional
                     FROM deps
                     WHERE `package` = ? ORDER BY `optional` ASC";
        if ($field === null) {
            $info = $dbh->getRow($pkg_sql, array($pkg), DB_FETCHMODE_ASSOC);

            $info['releases'] =
                 $dbh->getAssoc($rel_sql, false, array($info['packageid']),
                 DB_FETCHMODE_ASSOC);
            $rels = sizeof($info['releases']) ? array_keys($info['releases']) : array('');
            $info['stable'] = $rels[0];
            $info['notes'] =
                 $dbh->getAssoc($notes_sql, false, array(@$info['packageid']),
                 DB_FETCHMODE_ASSOC);
            $deps =
                 $dbh->getAll($deps_sql, array(@$info['packageid']),
                 DB_FETCHMODE_ASSOC);
            foreach($deps as $dep) {
                $rel_version = null;
                foreach($info['releases'] as $version => $rel) {
                    if ($rel['id'] == $dep['release']) {
                        $rel_version = $version;
                        break;
                    };
                };
                if ($rel_version !== null) {
                    unset($dep['release']);
                    $info['releases'][$rel_version]['deps'][] = $dep;
                };
            };
        } else {
            // get a single field
            if ($field == 'releases' || $field == 'notes') {
                if ($what == "name") {
                    $pid = $dbh->getOne("SELECT p.id FROM packages p ".
                                        "WHERE p.package_type = 'pecl' AND p.name = ?", array($pkg));
                } else {
                    $pid = $pkg;
                }

                if ($field == 'releases') {

                    $info = $dbh->getAssoc($rel_sql, false, array($pid),
                    DB_FETCHMODE_ASSOC);
                } elseif ($field == 'notes') {
                    $info = $dbh->getAssoc($notes_sql, false, array($pid),
                    DB_FETCHMODE_ASSOC);
                }

            } elseif ($field == 'category') {
                $sql = "SELECT c.name FROM categories c, packages p ".
                     "WHERE c.id = p.category AND p.package_type = 'pecl' AND  p.{$what} = ?";
                $info = $dbh->getOne($sql, array($pkg));
            } elseif ($field == 'description') {
                $sql = "SELECT description FROM packages p WHERE p.package_type = 'pecl' AND p.{$what} = ?";
                $info = $dbh->query($sql, array($pkg));
            } elseif ($field == 'authors') {
                $sql = "SELECT u.handle, u.name, u.email, u.showemail, m.active, m.role
                        FROM maintains m, users u, packages p
                        WHERE p.package_type = 'pecl' AND m.package = p.id
                        AND p.$what = ?
                        AND m.handle = u.handle";
                $info = $dbh->getAll($sql, array($pkg), DB_FETCHMODE_ASSOC);
            } else {
                if ($field == 'categoryid') {
                    $dbfield = 'category';
                } elseif ($field == 'packageid') {
                    $dbfield = 'id';
                } else {
                    $dbfield = $field;
                }
                $sql = "SELECT $dbfield FROM packages p WHERE p.package_type = 'pecl' AND  p.{$what} = ?";
                $info = $dbh->getOne($sql, array($pkg));
            }
        }
        return $info;
    }

    // }}}
    // {{{  proto struct package::search(string, [bool|string], [bool], [bool], [bool]) API 1.0

    /**
     *
     */
    function search($fragment, $summary = false, $released_only = true, $stable_only = true,
                    $include_pear = false)
    {
        $include_pear = false;
        $all = package::listAll($released_only, $stable_only, $include_pear);
        if (!$all) {
            return PEAR::raiseError('no packages found');
        }
        $ret = array();
        foreach ($all as $name => $info) {
            $found = (!empty($fragment) && stristr($name, $fragment) !== false);
            if (!$found && !(isset($summary) && !empty($summary)
                && (stristr($info['summary'], $summary) !== false
                    || stristr($info['description'], $summary) !== false)))
            {
                continue;
            };
            $ret[$name] = $info;
        }
        return $ret;
    }

    // }}}
    // {{{  proto struct package::listAll([bool], [bool], [bool]) API 1.0

    /**
     * Lists the IDs and names of all approved PEAR packages
     *
     * Returns an associative array where the key of each element is
     * a package ID, while the value is the name of the corresponding
     * package.
     *
     * @static
     * @return array
     */
    function listAllNames()
    {
        global $dbh;

        return $dbh->getAssoc("SELECT id, name FROM packages WHERE package_type = 'pecl' ORDER BY name");
    }

    // }}}
    // {{{  proto struct package::listAll([bool], [bool], [bool]) API 1.0

    /**
     * List all packages
     *
     * @static
     * @param boolean Only list released packages?
     * @param boolean If listing released packages only, only list stable releases?
     * @param boolean List also PEAR packages
     * @return array
     */
    function listAll($released_only = true, $stable_only = true, $include_pear = false)
    {
        global $dbh;
        $include_pear = false;

        $package_type = "p.package_type = 'pecl' AND p.approved = 1 AND ";

        $packageinfo = $dbh->getAssoc("SELECT p.name, p.id AS packageid, ".
            "c.id AS categoryid, c.name AS category, ".
            "p.license AS license, ".
            "p.summary AS summary, ".
            "p.description AS description, ".
            "m.handle AS lead ".
            " FROM packages p, categories c, maintains m ".
            "WHERE p.package_type = 'pecl' AND p.approved = 1 AND  c.id = p.category ".
            "  AND p.id = m.package ".
            "  AND m.role = 'lead' ".
            "ORDER BY p.name", false, null, DB_FETCHMODE_ASSOC);

        $allreleases = $dbh->getAssoc(
            "SELECT p.name, r.id AS rid, r.version AS stable, r.state AS state ".
            "FROM packages p, releases r ".
            "WHERE p.package_type = 'pecl' AND p.approved = 1 AND p.id = r.package ".
            "ORDER BY r.releasedate ASC ", false, null, DB_FETCHMODE_ASSOC);

        $stablereleases = $dbh->getAssoc(
            "SELECT p.name, r.id AS rid, r.version AS stable, r.state AS state ".
            "FROM packages p, releases r ".
            "WHERE p.package_type = 'pecl' AND p.approved = 1 AND p.id = r.package ".
            ($released_only ? "AND r.state = 'stable' " : "").
            "ORDER BY r.releasedate ASC ", false, null, DB_FETCHMODE_ASSOC);


        $deps = $dbh->getAll(
            "SELECT package, `release` , type, relation, version, name ".
            "FROM deps", null, DB_FETCHMODE_ASSOC);

        foreach ($packageinfo as $pkg => $info) {
            $packageinfo[$pkg]['stable'] = false;
        }
        foreach ($stablereleases as $pkg => $stable) {
            $packageinfo[$pkg]['stable'] = $stable['stable'];
            $packageinfo[$pkg]['unstable'] = false;
            $packageinfo[$pkg]['state']  = $stable['state'];
        }
        if (!$stable_only) {
            foreach ($allreleases as $pkg => $stable) {
                if ($stable['state'] == 'stable') {
                    if (version_compare($packageinfo[$pkg]['stable'], $stable['stable'], '<')) {
                        // only change it if the version number is newer
                        $packageinfo[$pkg]['stable'] = $stable['stable'];
                    }
                } else {
                    if (!isset($packageinfo[$pkg]['unstable']) ||
                          version_compare($packageinfo[$pkg]['unstable'], $stable['stable'], '<')) {
                        // only change it if the version number is newer
                        $packageinfo[$pkg]['unstable'] = $stable['stable'];
                    }
                }
                $packageinfo[$pkg]['state']  = $stable['state'];
                if (isset($packageinfo[$pkg]['unstable']) && !$packageinfo[$pkg]['stable']) {
                    $packageinfo[$pkg]['stable'] = $packageinfo[$pkg]['unstable'];
                }
            }
        }
        $var = !$stable_only ? 'allreleases' : 'stablereleases';
        foreach(array_keys($packageinfo) as $pkg) {
            $_deps = array();
            foreach($deps as $dep) {
                if ($dep['package'] == $packageinfo[$pkg]['packageid']
                    && isset($$var[$pkg])
                    && $dep['release'] == $$var[$pkg]['rid'])
                {
                    unset($dep['rid']);
                    unset($dep['release']);
                    if ($dep['type'] == 'pkg' && isset($packageinfo[$dep['name']])) {
                        $dep['package'] = $packageinfo[$dep['name']]['packageid'];
                    } else {
                        $dep['package'] = 0;
                    }
                    $_deps[] = $dep;
                };
            };
            $packageinfo[$pkg]['deps'] = $_deps;
        };

        if ($released_only) {
            if (!$stable_only) {
                foreach ($packageinfo as $pkg => $info) {
                    if (!isset($allreleases[$pkg]) && !isset($stablereleases[$pkg])) {
                        unset($packageinfo[$pkg]);
                    }
                }
            } else {
                foreach ($packageinfo as $pkg => $info) {
                    if (!isset($stablereleases[$pkg])) {
                        unset($packageinfo[$pkg]);
                    }
                }
            }
        }
        return $packageinfo;
    }

    // }}}
    // {{{  proto struct package::listAllwithReleases() API 1.0

    /**
     * Get list of packages and their releases
     *
     * @access public
     * @return array
     * @static
     */
    function listAllwithReleases()
    {
        global $dbh;

        $query = "SELECT
                      p.id AS pid, p.name, r.id AS rid, r.version, r.state
                  FROM packages p, releases r
                  WHERE p.package_type = 'pecl' AND p.approved = 1 AND p.id = r.package
                  ORDER BY p.name, r.version DESC";
        $sth = $dbh->query($query);

        if (DB::isError($sth)) {
            return $sth;
        }

        while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
            $packages[$row['pid']]['name'] = $row['name'];
            $packages[$row['pid']]['releases'][] = array('id' => $row['rid'],
                                                         'version' => $row['version'],
                                                         'state' => $row['state']
                                                         );
        }

        return $packages;
    }

    // }}}
    // {{{  proto struct package::listUpgrades(struct) API 1.0

    /**
     * List available upgrades
     *
     * @static
     * @param array Array containing the currently installed packages
     * @return array
     */
    function listUpgrades($currently_installed)
    {
        global $dbh;
        if (sizeof($currently_installed) == 0) {
            return array();
        }
        $query = "SELECT ".
             "p.name AS package, ".
             "r.id AS releaseid, ".
             "r.package AS packageid, ".
             "r.version AS version, ".
             "r.state AS state, ".
             "r.doneby AS doneby, ".
             "r.license AS license, ".
             "r.summary AS summary, ".
             "r.description AS description, ".
             "r.releasedate AS releasedate, ".
             "r.releasenotes AS releasenotes ".
             "FROM releases r, packages p WHERE p.package_type = 'pecl' AND p.approved = 1 AND r.package = p.id AND (";
        $conditions = array();
        foreach ($currently_installed as $package => $info) {
            extract($info); // state, version
            $conditions[] = "(package = '$package' AND state = '$state')";
        }
        $query .= implode(" OR ", $conditions) . ")";
        return $dbh->getAssoc($query, false, null, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{ +proto bool   package::updateInfo(string|int, struct) API 1.0

    /**
     * Updates fields of an existant package
     *
     * @param int $pkgid The package ID to update
     * @param array $data Assoc in the form 'field' => 'value'.
     * @return mixed True or PEAR_Error
     */
    function updateInfo($pkgid, $data)
    {
        global $dbh, $auth_user;
        $package_id = package::info($pkgid, 'id');
        if (PEAR::isError($package_id) || empty($package_id)) {
            return PEAR::raiseError("Package not registered. Please register it first with \"New Package\"");
        }
        if ($auth_user->isAdmin() == false) {
            $role = user::maintains($auth_user->handle, $package_id);
            if ($role != 'lead' && $role != 'developer') {
                return PEAR::raiseError('package::updateInfo: insufficient privileges');
            }
        }
        // XXX (cox) what about 'name'?
        $allowed = array('license', 'summary', 'description', 'category');
        $fields = $prep = array();
        foreach ($allowed as $a) {
            if (isset($data[$a])) {
                $fields[] = "$a = ?";
                $prep[]   = $data[$a];
            }
        }
        if (!count($fields)) {
            return;
        }
        $sql = 'UPDATE packages SET ' . implode(', ', $fields) .
               " WHERE id=$package_id";
        $row = package::info($pkgid, 'name');
        $GLOBALS['pear_rest']->saveAllPackagesREST();
        $GLOBALS['pear_rest']->savePackageREST($row);
        $GLOBALS['pear_rest']->savePackagesCategoryREST(package::info($pkgid, 'category'));
        return $dbh->query($sql, $prep);
    }

    // }}}
    // {{{ getDependants()

    /**
     * Get packages that depend on the given package
     *
     * @param  string Name of the package
     * @return array  List of package that depend on $package
     */
    function getDependants($package) {
        global $dbh;

        $query = "SELECT p.name AS p_name, d.* FROM deps d, packages p " .
            "WHERE d.package = p.id AND d.type = 'pkg' " .
            "      AND d.name = '" . $package . "' " .
            "GROUP BY d.package";
        return $dbh->getAll($query, null, DB_FETCHMODE_ASSOC);
    }

    // }}}
    // {{{  proto array  package::getRecent(int, string) API 1.0

    /**
     * Get list of recent releases for the given package
     *
     * @param  int Number of releases to return
     * @param  string Name of the package
     * @return array
     */
    function getRecent($n, $package)
    {
        global $dbh;
        $recent = array();

        $query = "SELECT p.id AS id, " .
            "p.name AS name, " .
            "p.summary AS summary, " .
            "r.version AS version, " .
            "r.releasedate AS releasedate, " .
            "r.releasenotes AS releasenotes, " .
            "r.doneby AS doneby, " .
            "r.state AS state " .
            "FROM packages p, releases r " .
            "WHERE p.id = r.package " .
            "AND p.package_type = 'pecl' AND p.approved = 1 " .
            "AND p.name = '" . $package . "'" .
            "ORDER BY r.releasedate DESC";

        $sth = $dbh->limitQuery($query, 0, $n);
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ *proto bool   package::isValid(string) API 1.0

    /**
     * Determines if the given package is valid
     *
     * @access public
     * @param  string Name of the package
     * @return  boolean
     */
    function isValid($package)
    {
        global $dbh;
         $query = "SELECT id FROM packages WHERE package_type = 'pecl' AND approved = 1 AND name = ?";
        $sth = $dbh->query($query, array($package));
        return ($sth->numRows() > 0);
    }

    // }}}
    // {{{ getNotes()

    /**
     * Get all notes for given package
     *
     * @access public
     * @param  int ID of the package
     * @return array
     */
    function getNotes($package)
    {
        global $dbh;

        $query = "SELECT * FROM notes WHERE pid = ? ORDER BY ntime";
        return $dbh->getAll($query, array($package), DB_FETCHMODE_ASSOC);
    }

    // }}}
}