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
    // {{{ +proto int package::_getID(string|int)

    function _getID($package)
    {
        global $dbh;
        // verify that package exists
        if (preg_match('/^\d+$/', $package)) {
            $package_id = $package;
            $error = $dbh->getOne("SELECT name FROM packages ".
                                    "WHERE id = ?", array($package));
            if (PEAR::isError($error)) {
                return $error;
            }
        } else {
            $package_id = $dbh->getOne("SELECT id FROM packages ".
                                       "WHERE name = ?", array($package));
        }
        if (empty($package_id)) {
            return PEAR::raiseError("no such package: $package");
        }
        return $package_id;
    }

    // }}}
    // {{{ *proto struct package::info(string|int)

    function info($pkg)
    {
        global $dbh;
        if ($pkg === (string)((int)$pkg)) {
            $what = "id";
        } else {
            $what = "name";
        }
        $info =
             $dbh->getRow("SELECT p.id AS packageid, p.name AS name, ".
                          "c.id AS categoryid, c.name AS category, ".
                          "p.stablerelease AS stable, p.license AS license, ".
                          "p.summary AS summary, ".
                          "p.description AS description".
                          " FROM packages p, categories c ".
                          "WHERE c.id = p.category AND p.{$what} = ?",
                          array($pkg), DB_FETCHMODE_ASSOC);
        $info['releases'] =
             $dbh->getAssoc("SELECT version, id, doneby, license, summary, ".
                            "description, releasedate, releasenotes, maturity ".
                            "FROM releases WHERE package = ?", false,
                            array($info['packageid']));
        $info['notes'] =
             $dbh->getAssoc("SELECT id, nby, ntime, note FROM notes ".
                            "WHERE pid = ?", false, array($info['packageid']));
        return $info;
    }

    // }}}
    // {{{ -proto struct package::listAll()

    function listAll()
    {
        global $dbh;
        return $dbh->getAll("SELECT p.id AS packageid, p.name AS name, ".
							"c.id AS categoryid, c.name AS category, ".
							"p.stablerelease AS stable, ".
							"p.license AS license, ".
							"p.summary AS summary, ".
							"p.description AS description, ".
							"m.handle AS lead ".
							" FROM packages p, categories c, maintains m ".
							"WHERE c.id = p.category ".
							"  AND p.id = m.package ".
							"  AND m.role = 'lead' ".
							"ORDER BY p.name", null, DB_FETCHMODE_ASSOC);
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
}

class release
{
    // {{{ -proto array release::getRecent([int])

    function getRecent($n = 5)
    {
        global $dbh;
        $sth = $dbh->query("SELECT packages.id AS id, ".
                           "packages.name AS name, ".
                           "packages.summary AS summary, ".
                           "releases.version AS version, ".
                           "releases.releasedate AS releasedate, ".
                           "releases.releasenotes AS releasenotes, ".
                           "releases.doneby AS doneby ".
                           "FROM packages, releases ".
                           "WHERE packages.name = releases.package ".
                           "ORDER BY releases.releasedate DESC");
        $recent = array();
        // XXX FIXME when DB gets rowlimit support
        while ($n-- > 0 && ($err = $sth->fetchInto($row, DB_FETCHMODE_ASSOC)) === DB_OK) {
            $recent[] = $row;
        }
        return $recent;
    }

    // }}}
    // {{{ +proto bool release::upload(string, string, string, string, binary, string)

    function upload($package, $version, $state, $relnotes, $tarball, $md5sum)
    {
        global $dbh, $auth_user, $PHP_AUTH_USER;
        // (2) verify that package exists
        $package_id = package::_getID($package);
		if (PEAR::isError($package_id)) {
            return $package_id;
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
            return PEAR::raiseError("fopen($tempfile) failed: $php_errormsg");
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

    function HTTPdownload($package, $version = null)
    {
        global $dbh;
        $package_id = package::_getID($package);
        if (PEAR::isError($package_id)) {
            return $package_id;
        }
        // We want the lastest version
        if ($version == null) {
            $sql = "SELECT f.fullpath FROM releases r, files f
                    WHERE r.package = $package_id
                    AND r.package = f.package
                    AND r.id = f.release
                    ORDER BY r.releasedate DESC";
            // XXX Fixme when Pear DB supports "limitGetOne()"
            $res = $dbh->limitQuery($sql, 0, 1);
            if (PEAR::isError($res)) {
                return $res;
            }
            $path = $res->fetchRow(DB_FETCHMODE_ORDERED);
        // specific version
        } else {
            $sql = "SELECT f.fullpath FROM releases r, files f
                    WHERE r.package = $package_id
                    AND r.package = f.package
                    AND r.id = f.release
                    AND r.version = ?";
            $path = $db->getOne($sql, array($version));
        }
        if (PEAR::isError($path)) {
            return $path;
        }
        if (empty($path) || !@is_file($path)) {
            return PEAR::raiseError("release download:: no version information found");
        }
        header('Content-type: application/octet-stream');
        header('Content-disposition: attachment; filename="'. basename($path) .'"');
        readfile($path);
    }
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
}

// {{{ +proto string logintest()

function testerror()
{
	return "ok";
}

// }}}

// {{{ mail_pear_admins()

function mail_pear_admins($subject, $msg, $xhdr = '')
{
    global $dbh;
    $admins = $dbh->getCol("SELECT email FROM users WHERE admin = 1");
    if (is_array($admins)) {
        $rcpt = implode(", ", $admins);
        return mail($rcpt, "PEAR Account Request", $msg, $xhdr);
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
		$fp = fopen($filename, "r");
		if (is_resource($fp)) {
			return md5(fread($fp, filesize($filename)));
		}
		return null;
	}
}

?>
