<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Stig Sæther Bakken <ssb@fast.no>                            |
   |          Tomas V.V.Cox <cox@php.net>                                 |
   |          Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
 */

require_once "DB/storage.php";

// {{{ validate()

function validate($entity, $field, $value /* , $oldvalue, $object */) {
    switch ("$entity/$field") {
        case "users/handle":
            if (!preg_match('/^[a-z][a-z0-9]+$/i', $value)) {
                return false;
            }
            break;
        case "users/name":
            if (!$value) {
                return false;
            }
            break;
        case "users/email":
            if (!preg_match('/[a-z0-9_\.\+%]@[a-z0-9\.]+\.[a-z]+$', $email)) {
                return false;
            }
            break;
    }
    return true;
}

// }}}

// {{{ renumber_visitations()

/*

Some useful "visitation model" tricks:

To find the number of child elements:
 (right - left - 1) / 2

To find the number of child elements (including self):
 (right - left + 1) / 2

To get all child nodes:

 SELECT * FROM table WHERE left > <self.left> AND left < <self.right>


To get all child nodes, including self:

 SELECT * FROM table WHERE left BETWEEN <self.left> AND <self.right>
 "ORDER BY left" gives tree view

To get all leaf nodes:

 SELECT * FROM table WHERE right-1 = left;

 */

function renumber_visitations($id, $parent)
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
// each.  They are packaged into classes for easier xmlrpc
// integration.

class category
{
    // {{{ *proto int category::add(struct)

    /*
    $data = array(
        'name'   => 'category name',
        'desc'   => 'category description',
        'parent' => 'category parent id'
        );
    */
    function add($data)
    {
        global $dbh;
        $name = $data['name'];
        if (empty($name)) {
            return PEAR::raiseError('no name given');
        }
        $desc   = (empty($data['desc'])) ? 'none' : $data['desc'];
        $parent = (empty($data['parent'])) ? null : $data['parent'];

        $sql = 'INSERT INTO categories (id, name, description, parent)'.
             'VALUES (?, ?, ?, ?)';
        $id  = $dbh->nextId('categories');
        $sth = $dbh->prepare($sql);
        if (DB::isError($sth)) {
            return $sth;
        }
        $err = $dbh->execute($sth, array($id, $name, $desc, $parent));
        if (DB::isError($err)) {
            return $err;
        }
        $err = renumber_visitations($id, $parent);
        if (PEAR::isError($err)) {
            return $err;
        }
        return $id;
    }

    // }}}
    // {{{ -proto array category::listAll()

    function listAll()
    {
        global $dbh;
        return $dbh->getAll("SELECT * FROM categories ORDER BY id",
                            null, DB_FETCHMODE_ASSOC);
    }

    // }}}
}

class package
{
    // {{{ *proto int package::add(struct)

    // add a package, return new package id or PEAR error
    function add($data)
    {
        global $dbh;
        // name, category
        // license, summary, description
        // lead
        extract($data);
        if (empty($license)) {
            $license = "PEAR License";
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
        $query = "INSERT INTO packages (id,name,category,license,summary,description) VALUES(?,?,?,?,?,?)";
        $id = $dbh->nextId("packages");
        if (DB::isError($sth = $dbh->prepare($query))) {
            return $sth;
        }
        $err = $dbh->execute($sth, array($id, $name, $category, $license, $summary, $description));
        if (DB::isError($err)) {
            return $err;
        }
        if (isset($lead) && DB::isError($err = maintainer::add($id, $lead, 'lead'))) {
            return $err;
        }
        $sql = "update categories set npackages = npackages + 1
                where id = $category";
        if (DB::isError($err = $dbh->query($sql))) {
            return $err;
        }
        return $id;
    }

    // }}}
    // {{{ *proto struct package::info(string|int, [string])

    function info($pkg, $field = null)
    {
        global $dbh;
        if ($pkg === (string)((int)$pkg)) {
            $what = "id";
        } else {
            $what = "name";
        }
		$pkg_sql = "SELECT p.id AS packageid, p.name AS name, ".
			 "c.id AS categoryid, c.name AS category, ".
			 "p.stablerelease AS stable, p.license AS license, ".
			 "p.summary AS summary, ".
			 "p.description AS description".
			 " FROM packages p, categories c ".
			 "WHERE c.id = p.category AND p.{$what} = ?";
		$rel_sql = "SELECT version, id, doneby, license, summary, ".
			 "description, releasedate, releasenotes, state ".
			 "FROM releases WHERE package = ?";
		$notes_sql = "SELECT id, nby, ntime, note FROM notes WHERE pid = ?";
		if ($field === null) {
			$info =
				 $dbh->getRow($pkg_sql, array($pkg), DB_FETCHMODE_ASSOC);
			$info['releases'] =
				 $dbh->getAssoc($rel_sql, false, array($info['packageid']),
				                DB_FETCHMODE_ASSOC);
			$info['notes'] =
				 $dbh->getAssoc($notes_sql, false, array($info['packageid']),
				                DB_FETCHMODE_ASSOC);
		} else {
			// get a single field
			if ($field == 'releases' || $field == 'notes') {
				if ($what == "name") {
					$pid = $dbh->getOne("SELECT id FROM packages ".
										"WHERE name = ?", array($pkg));
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
					 "WHERE c.id = p.category AND p.$what = ?";
				$info = $dbh->getAssoc($sql, false, array($pkg));
			} else {
				if ($field == 'categoryid') {
					$dbfield = 'category';
				} elseif ($field == 'packageid') {
					$dbfield = 'id';
				} else {
					$dbfield = $field;
				}
				$sql = "SELECT $dbfield FROM packages WHERE $what = ?";
				$info = $dbh->getOne($sql, array($pkg));
			}
		}
        return $info;
    }

    // }}}
    // {{{ -proto struct package::listAll([bool])

    function listAll($released_only = true)
    {
        global $dbh;
        $packageinfo = $dbh->getAssoc(
			"SELECT p.name, p.id AS packageid, ".
			"c.id AS categoryid, c.name AS category, ".
			"p.license AS license, ".
			"p.summary AS summary, ".
			"p.description AS description, ".
			"m.handle AS lead ".
			" FROM packages p, categories c, maintains m ".
			"WHERE c.id = p.category ".
			"  AND p.id = m.package ".
			"  AND m.role = 'lead' ".
			"ORDER BY p.name", false, null, DB_FETCHMODE_ASSOC);
		$stablereleases = $dbh->getAssoc(
			"SELECT p.name, r.version AS stable ".
			"FROM packages p, releases r ".
			"WHERE p.id = r.package AND r.state = 'stable'");
		foreach ($stablereleases as $pkg => $stable) {
			$packageinfo[$pkg]['stable'] = $stable;
		}
		if ($released_only) {
			foreach ($packageinfo as $pkg => $info) {
				if (!isset($stablereleases[$pkg])) {
					unset($packageinfo[$pkg]);
				}
			}
		}
		return $packageinfo;
    }

    // }}}
	// {{{ -proto struct package::listUpgrades(struct)

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
			 "FROM releases r, packages p WHERE r.package = p.id AND (";
		$conditions = array();
		foreach ($currently_installed as $package => $info) {
			extract($info); // state, version
			$conditions[] = "(package = '$package' AND state = '$state')";
		}
		$query .= implode(" OR ", $conditions) . ")";
		return $dbh->getAssoc($query, false, null, DB_FETCHMODE_ASSOC);
	}

    // }}}
}

class maintainer
{
    // {{{ +proto int maintainer::add(int, string, string)

    function add($package, $user, $role)
    {
        global $dbh;
        $query = "INSERT INTO maintains VALUES(?,?,?)";
        $sth = $dbh->prepare($query);
        if (DB::isError($sth)) {
            return $sth;
        }
        $err = $dbh->execute($sth, array($user, $package, $role));
        if (DB::isError($err)) {
            return $err;
        }
        return true;
    }

    // }}}
    // {{{ *proto int maintainer::get(int, bool)

    function get($package, $lead = false)
    {
        global $dbh;
        $query = "SELECT handle FROM maintains WHERE package = '" . $package . "'";

        if ($lead) {
            $query .= " AND role = 'lead'";
        }

        $sth = $dbh->query($query);

        if (DB::isError($sth)) {
            return sth;
        }

        while ($row = $sth->fetchRow()) {
            $rows[] = $row[0];
        }

        return $rows;
    }

    // }}}
}

class release
{
    // {{{ -proto array release::getRecent([int])

    function getRecent($n = 5)
    {
        global $dbh;
        $sth = $dbh->limitQuery("SELECT packages.id AS id, ".
                           "packages.name AS name, ".
                           "packages.summary AS summary, ".
                           "releases.version AS version, ".
                           "releases.releasedate AS releasedate, ".
                           "releases.releasenotes AS releasenotes, ".
                           "releases.doneby AS doneby ".
                           "FROM packages, releases ".
                           "WHERE packages.id = releases.package ".
                           "ORDER BY releases.releasedate DESC", 0, $n);
        $recent = array();
        // XXX Fixme when DB gets limited getAll()
        while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ +proto string release::upload(string, string, string, string, binary, string)

    function upload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
		$ref = release::validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum);
		if (PEAR::isError($ref)) {
			return $ref;
		}
		return release::confirmUpload($ref);
    }

    // }}}
    // {{{ +proto string release::validateUpload(string, string, string, string, binary, string)

    function validateUpload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $dbh, $auth_user, $PHP_AUTH_USER;
        // (2) verify that package exists
        $package_id = package::info($package, 'id');
        if (PEAR::isError($package_id)) {
            return PEAR::raiseError("package `$package' must be registered first");
        }

        // (3) verify that version does not exist
        $test = $dbh->getOne("SELECT version FROM releases ".
                             "WHERE package = ? AND version = ?",
                             array($package_id, $version));
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
        // later: do lots of integrity checks on the tarball
        if (!@rename($tempfile, $file)) {
            return PEAR::raiseError("rename failed: $php_errormsg");
        }

        // (5) verify MD5 checksum
        ob_start();
        readfile($file);
        $data = ob_get_contents();
        ob_end_clean();
        $testsum = md5($data);
        if ($testsum != $md5sum) {
            $bytes = strlen($data);
            return PEAR::raiseError("bad md5 checksum (checksum=$testsum ($bytes bytes: $data), specified=$md5sum)");
        }

		$info = array("package_id" => $package_id,
					  "version" => $version,
					  "state" => $state,
					  "relnotes" => $relnotes,
					  "md5sum" => $md5sum,
		              "file" => $file);
        $infofile = sprintf("%s/%s%s-%s",
                            PEAR_TARBALL_DIR, ".info.", $package, $version);
		$fp = @fopen($infofile, "w");
		if (!is_resource($fp)) {
            return PEAR::raiseError("writing $infofile failed: $php_errormsg");
		}
		fwrite($fp, serialize($info));
		fclose($info);
		return $infofile;
    }

    // }}}
    // {{{ +proto bool release::confirmUpload(string)

    function confirmUpload($upload_ref)
    {
		$fp = @fopen($upload_ref, "r");
		if (!is_resource($fp)) {
            return PEAR::raiseError("invalid upload reference: $upload_ref");
		}
		$info = unserialize(fread($fp, filesize($upload_ref)));
		extract($info);
		@unlink($upload_ref);

        // Update releases table
        $query = "INSERT INTO releases (id,package,version,state,doneby,".
             "releasedate,releasenotes) VALUES(?,?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $release_id = $dbh->nextId("releases");
        $dbh->execute($sth, array($release_id, $package_id, $version, $state,
                                  $PHP_AUTH_USER, gmdate('Y-m-d H:i'),
                                  $relnotes));
        // Update files table
        $query = "INSERT INTO files ".
             "(id,package,release,md5sum,basename,fullpath) ".
             "VALUES(?,?,?,?,?,?)";
        $sth = $dbh->prepare($query);
        $file_id = $dbh->nextId("files");
        $ok = $dbh->execute($sth, array($file_id, $package_id, $release_id,
                                        $md5sum, basename($file), $file));
        if (PEAR::isError($ok)) {
            $dbh->query("DELETE FROM releases WHERE id = $release_id");
            @unlink($file);
        }
        return true;
    }

    // }}}
    // {{{ +proto bool release::dismissUpload(string)

    function dismissUpload($upload_ref)
    {
		return (bool)@unlink($upload_ref);
    }

    // }}}
    // {{{        void release::HTTPdownload(string, [string], [string])

	// not for xmlrpc export
    function HTTPdownload($package, $version = null, $file = null)
    {
        global $dbh;
        $package_id = package::info($package, 'packageid');

        if (!$package_id) {
            return PEAR::raiseError("release download:: package '$package' does not exist");
        } elseif (PEAR::isError($package_id)) {
            return $package_id;
        }

		if ($file !== null) {
			$path = $dbh->getRow("SELECT fullpath, release, id FROM files ".
								 "WHERE basename = '" . $file . "'");
			if (PEAR::isError($path)) {
				return $path;
			}
			$log_release = $path[1];
			$log_file = $path[2];
			$basename = $file;
		} elseif ($version == null) {
			// Get the most recent version
			$row = $dbh->getRow("SELECT id FROM releases ".
								"WHERE package = $package_id ".
								"ORDER BY releasedate DESC",
								DB_FETCHMODE_ORDERED);
			if (PEAR::isError($row)) {
				return $row;
			}
			$release_id = $row[0];
		} elseif (release::isValidState($version)) {
			// Get the most recent version with a given state
			$row = $dbh->getRow("SELECT id FROM releases ".
								"WHERE package = $package_id ".
								"AND state = '$version' ".
								"ORDER BY releasedate DESC",
								DB_FETCHMODE_ORDERED);
			if (PEAR::isError($row)) {
				return $row;
			}
			$release_id = $row[0];
		} else {
			// Get a specific release
			$row = $dbh->getRow("SELECT id FROM releases ".
								"WHERE package = $package_id ".
								"AND version = '$version'",
								DB_FETCHMODE_ORDERED);
			if (PEAR::isError($row)) {
				return $row;
			}
			$release_id = $row[0];
		}
		if (!isset($path) && isset($release_id)) {
			$sql = "SELECT fullpath, basename FROM files WHERE release = ".
				 $release_id;
			$row = $dbh->getRow($sql, DB_FETCHMODE_ORDERED);
			if (PEAR::isError($row)) {
				return $row;
			}
			list($path, $basename) = $row;
			if (empty($path) || !@is_file($path)) {
				return PEAR::raiseError("release download:: no version information found");
			}
		}
		if (isset($path)) {
			header('Content-type: application/octet-stream');
			header('Content-disposition: attachment; filename="'.$basename.'"');
			readfile($path);

			if (!isset($log_release)) {
			    $log_release = $release_id;
			}
			release::logDownload($package_id, $log_release, $log_file);

			return true;
		}
		header('HTTP/1.0 404 Not Found');
		print 'File not found';
    }

    // }}}
	// {{{ -proto bool release::isValidState(string)

	function isValidState($state)
    {
		static $states = array('devel', 'snapshot', 'alpha', 'beta', 'stable');
		return in_array($state, $states);
	}

    // }}}
    // {{{ *proto bool release::logDownload(integer, string, string)

    function logDownload($package, $release_id, $file = null)
    {
        global $dbh;

        $id = $dbh->nextId("downloads");

        $query = "INSERT INTO downloads VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sth = $dbh->prepare($query);

        if (DB::isError($sth)) {
            return false;
        }

        $err = $dbh->execute($sth, array($id, $file, $package,
                                         $release_id, date("Y-m-d H:i:s"),
                                         $_SERVER['REMOTE_ADDR'],
                                         gethostbyaddr($_SERVER['REMOTE_ADDR'])
                                        ));

        if (DB::isError($err)) {
            return false;
        } else {
            return true;
        }
    }

    // }}}
}

class note
{
    // {{{ +proto bool note::add(string, int, string)

    function add($key, $value, $note)
    {
        global $dbh, $PHP_AUTH_USER;
        $nby = $PHP_AUTH_USER;
        $nid = $dbh->nextId("notes");
        $stmt = $dbh->prepare("INSERT INTO notes (id,$key,nby,ntime,note) ".
                              "VALUES(?,?,?,?,?)");
        $res = $dbh->execute($stmt, array($nid, $value, $nby,
                             gmdate('Y-m-d H:i'), $note));
        if (DB::isError($res)) {
            return $res;
        }
        return true;
    }

    // }}}
    // {{{ +proto bool note::remove(int)

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
    // {{{ +proto bool note::removeAll(string, int)

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
    // {{{ *proto bool user::remove(string)

    function remove($uid)
    {
        global $dbh;
        note::removeAll("uid", $uid);
        $dbh->query('DELETE FROM users WHERE handle = '. $dbh->quote($uid));
        return ($dbh->affectedRows() > 0);
    }

    // }}}
    // {{{ *proto bool user::rejectRequest(string, string)

    function rejectRequest($uid, $reason)
    {
        global $PHP_AUTH_USER, $dbh;
        list($email) = $dbh->getRow('SELECT email FROM users WHERE handle = ?',
                                    array($uid));
        note::add("uid", $uid, "Account rejected: $reason");
        $msg = "Your PEAR account request was rejected by $PHP_AUTH_USER:\n".
             "$reason\n";
        $xhdr = "From: $PHP_AUTH_USER@php.net";
        mail($email, "Your PEAR Account Request", $msg, $xhdr);
        return true;
    }

    // }}}
    // {{{ *proto bool user::activate(string)

    function activate($uid)
    {
        global $PHP_AUTH_USER, $dbh;

        $user =& new PEAR_User($dbh, $uid);
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
        $user->set('createdby', $PHP_AUTH_USER);
        $user->store();
        note::add("uid", $uid, "Account opened");
        $msg = "Your PEAR account request has been opened.\n".
             "To log in, go to http://pear.php.net/ and click on \"login\" in\n".
             "the top-right menu.\n";
        $xhdr = "From: $PHP_AUTH_USER@php.net";
        mail($user->email, "Your PEAR Account Request", $msg, $xhdr);
        return true;
    }

    // }}}
    // {{{ *proto bool user::isAdmin(string)

    function isAdmin($handle)
    {
        global $dbh;

        $query = "SELECT handle FROM users WHERE handle = '" . $handle . "' AND admin = 1";
        $sth = $dbh->query($query);

        if ($sth->numRows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // }}}
}

// {{{ +proto string logintest()

function testerror()
{
    return "ok";
}

// }}}

// {{{ mail_pear_admins()

function mail_pear_admins($subject = "PEAR Account Request", $msg, $xhdr = '')
{
    global $dbh;
    $admins = $dbh->getCol("SELECT email FROM users WHERE admin = 1");
    if (is_array($admins)) {
        $rcpt = implode(", ", $admins);
        return mail($rcpt, $subject, $msg, $xhdr);
    }
    return false;
}

// }}}

// {{{ class PEAR_User

class PEAR_User extends DB_storage
{
    function PEAR_User(&$dbh, $user)
    {
        $this->DB_storage("users", "handle", $dbh);
        // XXX horrible hack until we get temporary error handlers
        $oldmode = $this->_default_error_mode;
        $this->_default_error_mode = PEAR_ERROR_RETURN;
        $this->setup($user);
        if (empty($oldmode)) {
            unset($this->_default_error_mode);
        } else {
            $this->_default_error_mode = $oldmode;
        }
    }
}

// }}}
// {{{ class PEAR_Package

class PEAR_Package extends DB_storage
{
    function PEAR_Package(&$dbh, $package, $keycol = "id")
    {
        $this->DB_storage("packages", $keycol, $dbh);
        // XXX horrible hack until we get temporary error handlers
        $oldmode = $this->_default_error_mode;
        $this->_default_error_mode = PEAR_ERROR_RETURN;
        $this->setup($package);
        if (empty($oldmode)) {
            unset($this->_default_error_mode);
        } else {
            $this->_default_error_mode = $oldmode;
        }
    }
}

// }}}
// {{{ class PEAR_Release

class PEAR_Release extends DB_storage
{
    function PEAR_Release(&$dbh, $release)
    {
        $this->DB_storage("releases", "id", $dbh);
        // XXX horrible hack until we get temporary error handlers
        $oldmode = $this->_default_error_mode;
        $this->_default_error_mode = PEAR_ERROR_RETURN;
        $this->setup($release);
        if (empty($oldmode)) {
            unset($this->_default_error_mode);
        } else {
            $this->_default_error_mode = $oldmode;
        }
    }
}

// }}}

if (!function_exists("md5_file")) {
    function md5_file($filename) {
        $fp = @fopen($filename, "r");
        if (is_resource($fp)) {
            return md5(fread($fp, filesize($filename)));
        }
        return null;
    }
}

?>
