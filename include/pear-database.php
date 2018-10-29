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
require_once __DIR__.'/../src/Maintainer.php';
require_once __DIR__.'/../src/Package.php';
require_once __DIR__.'/../src/Release.php';

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
