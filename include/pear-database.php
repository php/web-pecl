<?php

function validate($entity, $field, $value) {
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

function visit_node(&$tree, $node) {
    static $visitno;
    if (empty($visitno) || empty($node)) {
        $visitno = 1;
    }
    $tree[$node]['leftvisit'] = $visitno++;
    if (isset($tree[$node]['children'])) {
        foreach ($tree[$node]['children'] as $cnode) {
            visit_node($tree, $cnode);
        }
    }
    $tree[$node]['rightvisit'] = $visitno++;
}

function renumber_visitations($debug = false)
{
    global $dbh;
    $sth = $dbh->query("SELECT * FROM packages ORDER BY name");
    if (DB::isError($sth)) {
        return $sth;
    }
    $tree = array('' => array("children" => array()));
    $oldleft = array();
    $oldright = array();
    while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC) === DB_OK) {
        extract($row);
        if ($name == '') {
            continue;
        }
        $tree[$parent]["children"][] = $name;
        $tree[$name]["parent"] = $parent;
        $oldleft[$name] = (int)$leftvisit;
        $oldright[$name] = (int)$rightvisit;
    }
    visit_node($tree, '');
    foreach ($tree as $node => $data) {
        if (!isset($oldleft[$node])) {
            continue;
        }
        $l = $data["leftvisit"];
        $r = $data["rightvisit"];
        if ($oldleft[$node] == $l && $oldright[$node] == $r) {
            if ($debug) {
                print "keeping $node\n";
            }
            continue;
        }
        if ($debug) {
            print "updating $node\n";
        }
        $query = "UPDATE packages SET leftvisit = $l, rightvisit = $r ".
            "WHERE name = '$node'";
        $dbh->query($query);
    }
    return true;
}

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

function invalidHandle($handle) {
    if (preg_match('/^[a-z]+-?[a-z]+$/i', $handle)) {
	return false;
    }
    return 'handle must be all letters, optionally with one dash';
}

function invalidName($name) {
    if (trim($name)) {
	return $false;
    }
    return 'name must not be empty';
}

function invalidEmail($email) {
    if (preg_match('/[a-z0-9_\.\+%]@[a-z0-9\.]+\.[a-z]+$', $email)) {
	return false;
    }
    return 'invalid email address';
}

function invalidHomepage($homepage) {
    if (preg_match('!http://.+!i', $homepage)) {
	return false;
    }
    return 'invalid URL';
}



class PEAR_User extends DB_storage
{
    function PEAR_User(&$dbh, $user)
    {
	$this->DB_storage("users", "handle", &$dbh);
        $this->setup($user);
    }
}

class PEAR_Package extends DB_storage
{
    function PEAR_User(&$dbh, $package)
    {
        $this->DB_storage("packages", "name", &$dbh);
        $this->setup($package);
    }
}

class PEAR_Release extends DB_storage
{
    function PEAR_Release(&$dbh, $package, $release)
    {
        $this->DB_storage("releases", array("package", "release"), &$dbh);
        $this->setup(array($package, $release));
    }
}

?>
