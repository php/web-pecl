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

    $from = $tree['Science_Chemistry']['leftvisit'];
    $to = $tree['Science_Chemistry']['rightvisit'];
    $test = $dbh->getAll("SELECT name FROM packages WHERE leftvisit <= $from ".
                         "AND rightvisit >= $to");
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
