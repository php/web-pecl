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
