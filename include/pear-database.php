<?php

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

// {{{ visit_node()

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
function visit_node(&$tree, $node, &$cnt, $debug) {
    // XXX this stuff seriously needs to be reimplemented
    static $pkg_visitno, $cat_visitno;
    if (empty($pkg_visitno) || empty($node)) {
        $pkg_visitno = 1;
    }
    if (empty($cat_visitno) || empty($node)) {
        $cat_visitno = 1;
    }
    $tree[$node]['cat_left'] = $cat_visitno++;
    $tree[$node]['pkg_left'] = $pkg_visitno;
    $inc = 1;
    if (isset($cnt[$node])) {
        $inc += $cnt[$node];
    }
    if ($debug) {
        var_dump($cnt[$node]);
        print "inc=$inc<br />\n";
    }
    $pkg_visitno += $inc;
    if (isset($tree[$node]['children'])) {
        foreach ($tree[$node]['children'] as $cnode) {
            visit_node($tree, $cnode, $cnt, $debug);
        }
    }
    $tree[$node]['cat_right'] = $cat_visitno++;
    $tree[$node]['pkg_right'] = $pkg_visitno;
    $pkg_visitno += $inc;
}

// }}}
// {{{ renumber_visitations()

function renumber_visitations($debug = false)
{
    global $dbh;
    $sth = $dbh->query("SELECT category FROM packages");
    if (DB::isError($sth)) {
        return $sth;
    }
    $pkg_count = array();
    while ($sth->fetchInto($row, DB_FETCHMODE_ORDERED) === DB_OK) {
        if (isset($pkg_count[$row[0]])) {
            $pkg_count[$row[0]]++;
        } else {
            $pkg_count[$row[0]] = 1;
        }
    }
    $sth->free();
    $sth = $dbh->query("SELECT * FROM categories ORDER BY name");
    if (DB::isError($sth)) {
        return $sth;
    }
    $tree = array(0 => array("children" => array()));
    $cat_oldleft = array();
    $cat_oldright = array();
    $pkg_oldleft = array();
    $pkg_oldright = array();
    $new_count = array();
    while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC) === DB_OK) {
        extract($row);
        settype($parent, 'integer');
        $tree[$parent]["children"][] = $id;
        $tree[$id]["parent"] = $parent;
        $cat_oldleft[$id] = (int)$cat_left;
        $cat_oldright[$id] = (int)$cat_right;
        $pkg_oldleft[$id] = (int)$pkg_left;
        $pkg_oldright[$id] = (int)$pkg_right;
        if (!isset($pkg_count[$id])) {
            $new_count[$id] = 0;
        } elseif ($npackages != $pkg_count[$id]) {
            $new_count[$id] = $pkg_count[$id];
        }
    }
    $pkg_visitno = 0;
    $cat_visitno = 0;
    visit_node($tree, 0, $pkg_count, $pkg_visitno, $debug);
    foreach ($tree as $node => $data) {
        if (!isset($pkg_oldleft[$node])) {
            continue;
        }
        $pl = $data["pkg_left"];
        $pr = $data["pkg_right"];
        $cl = $data["cat_left"];
        $cr = $data["cat_right"];
        if ($pkg_oldleft[$node] == $pl && $pkg_oldright[$node] == $pr &&
            $cat_oldleft[$node] == $cl && $cat_oldright[$node] == $cr) {
            if ($debug) {
                print "keeping $node<br />\n";
            }
            continue;
        }
        if ($debug) {
            print "updating $node<br />\n";
        }
        $query = "UPDATE categories SET pkg_left=$pl, pkg_right=$pr";
        $query .= ", cat_left=$cl, cat_right=$cr";
        if (isset($new_count[$node])) {
            $query .= ", npackages={$new_count[$node]}";
        }
        $query .= " WHERE id=$node";
        if ($debug) {
            print "$query\n";
        }
        $dbh->query($query);
    }
    return DB_OK;
}

// }}}

// {{{ add_category()

/*
$data = array(
    'name'   => 'category name',
    'desc'   => 'category description',
    'parent' => 'category parent id'
    );
*/
function add_category($data)
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
    $err = renumber_visitations();
    if (PEAR::isError($err)) {
        return $err;
    }
    return $id;
}

// }}}
// {{{ add_package()

// add a package, return new package id or PEAR error
function add_package($data)
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
        return PEAR::raiseError("add_package: invalid `category' field");
    }
    if (empty($name)) {
        return PEAR::raiseError("add_package: invalid `name' field");
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
    if (isset($lead) && DB::isError($err = add_maintainer($id, $lead, 'lead'))) {
        return $err;
    }
    if (DB::isError($err = renumber_visitations())) {
        return $err;
    }
    return $id;
}

// }}}
// {{{ add_maintainer()

function add_maintainer($package, $user, $role)
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
    return DB_OK;
}

// }}}

// {{{ get_recent_releases()

function &get_recent_releases($n = 5) {
    global $dbh;
    $sth = $dbh->query("SELECT packages.name, packages.summary, ".
                       "releases.version, releases.releasedate, ".
                       "releases.releasenotes, releases.doneby ".
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
// {{{ release_upload()

function release_upload($package, $version, $relnotes, &$tarball, $md5sum)
{
    global $_return_value; // used by XML-RPC backend
    global $dbh, $auth_user;

    // (2) verify that package exists
    $test = $dbh->getOne("SELECT name FROM packages WHERE name = ?",
                         array($package));
    if (isset($_return_value)) return $_return_value;
    if (empty($test)) {
        return "no such package: $package";
    }

    // (3) verify that version does not exist
    $test = $dbh->getOne("SELECT version FROM releases ".
                         "WHERE package = ? AND version = ?",
                         array($package, $version));
    if (isset($_return_value)) return $_return_value;
    if ($test) {
        return "already exists: $package $version";
    }

    // (4) store tar ball to temp file
    $tempfile = sprintf("%s/%s%s-%s.tgz",
                        PEAR_TARBALL_DIR, ".new.", $package, $version);
    $file = sprintf("%s/%s-%s.tgz", PEAR_TARBALL_DIR, $package, $version);
    $fp = @fopen($tempfile, "w");
    if (!$fp) {
        return "fopen failed: $php_errormsg";
    }
    fwrite($fp, $distfile);
    fclose($fp);
    // later: do lots of integrity checks on the tarball
    if (!@rename($tempfile, $file)) {
        return "rename failed: $php_errormsg";
    }

    // (5) verify MD5 checksum
    ob_start();
    readfile($file);
    $data = ob_get_contents();
    ob_end_clean();
    if (md5($data) != $md5sum) {
        return "bad md5 checksum";
    }

    // Update releases table
    $query = "INSERT INTO releases VALUES(?,?,?,?,?,?,?)";
    $sth = $dbh->prepare($query);
    $dbh->execute($sth, array($package, $version, $auth_user->handle,
                              gmdate('Y-m-d H:i'), $relnotes, $md5sum,
                              $file));
    if (isset($_return_value)) return $_return_value;

}

// }}}

// {{{ add_note()

function add_note($key, $value, $note)
{
    global $dbh, $PHP_AUTH_USER;
    $nby = $PHP_AUTH_USER;
    $nid = $dbh->nextId("notes");
    $stmt = $dbh->prepare("INSERT INTO notes (id,$key,nby,ntime,note) ".
                          "VALUES(?,?,?,?,?)");
    return $dbh->execute($stmt, array($nid, $value, $nby,
                                      gmdate('Y-m-d H:i'), $note));
}

// }}}
// {{{ delete_note()

function delete_note($id)
{
    global $dbh;
    $id = (int)$id;
    return $dbh->query("DELETE FROM notes WHERE id = $id");
}

// }}}
// {{{ delete_all_notes()

function delete_all_notes($key, $value)
{
    global $dbh;
    return $dbh->query("DELETE FROM notes WHERE $key = ". $dbh->quote($value));
}

// }}}

// {{{ delete_account()

function delete_account($uid)
{
    global $dbh;
    delete_all_notes("uid", $uid);
    $dbh->query('DELETE FROM users WHERE handle = '. $dbh->quote($uid));
    return ($dbh->affectedRows() > 0);
}

// }}}
// {{{ reject_account_request()

function reject_account_request($uid, $reason)
{
    global $PHP_AUTH_USER, $dbh;
    list($email) = $dbh->getRow('SELECT email FROM users WHERE handle = ?',
                                array($uid));
    add_note("uid", $uid, "Account rejected: $reason");
    $msg = "Your PEAR account request was rejected by $PHP_AUTH_USER:\n".
           "$reason\n";
    $xhdr = "From: $PHP_AUTH_USER@php.net";
    mail($email, "Your PEAR Account Request", $msg, $xhdr);
    return true;
}

// }}}
// {{{ open_account()

function open_account($uid)
{
    global $PHP_AUTH_USER, $dbh;

    $user =& new PEAR_User($dbh, $uid);
    if (@$user->registered) {
        return false;
    }
    @$arr = unserialize($user->userinfo);
    delete_all_notes("uid", $uid);
    $user->set('registered', 1);
    if (is_array($arr)) {
        $user->set('userinfo', $arr[1]);
    }
    $user->set('created', gmdate('Y-m-d H:i'));
    $user->set('createdby', $PHP_AUTH_USER);
    $user->store();
    add_note("uid", $uid, "Account opened");
    $msg = "Your PEAR account request has been opened.\n".
           "To log in, go to http://pear.php.net/ and click on \"login\" in\n".
           "the top-right menu.\n";
    $xhdr = "From: $PHP_AUTH_USER@php.net";
    mail($user->email, "Your PEAR Account Request", $msg, $xhdr);
    return true;
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

?>
