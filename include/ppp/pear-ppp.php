<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

define("PEARWEB_AUTO_DELETE", 11);
define("PEARWEB_USER_DELETE", 12);

/**
 * The PEAR Package Proposal class
 *
 * @author  Martin Jansen <mj@php.net>
 * @package pearweb
 * @version $Revision$
 */
class proposal {

    /**
     * List all open proposals
     *
     * @return array
     */
    function listAll($where = "status = 'open' AND date_ends >= NOW()")
    {
        global $dbh;
        $proposals = array();

        /**
         * Merge together the proposal and their votes
         */
        $query = "SELECT p.*, c.name AS category, " .
            "UNIX_TIMESTAMP(date_ends)-UNIX_TIMESTAMP(NOW()) AS duration " .
            "FROM package_proposals p " .
            "LEFT JOIN categories c ON c.id = p.category " .
            "WHERE " . $where . " ORDER BY date_created ASC";
        $sth = $dbh->query($query);

        while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
            $proposals[$row['id']] = $row;
            $proposals[$row['id']]['votes_pos'] = 0;
            $proposals[$row['id']]['votes_neg'] = 0;
        }

        $query = "SELECT v.* FROM package_votes v, package_proposals p " .
            "WHERE p.id = v.package AND p.status = 'open'";
        $votes = $dbh->getAll($query, DB_FETCHMODE_ASSOC);

        foreach ($votes as $vote) {
            if (!empty($proposals[$vote['package']])) {
                if ((int)$vote['vote_value'] == 1) {
                    $proposals[$vote['package']]['votes_pos']++;
                } else {
                    $proposals[$vote['package']]['votes_neg']++;
                }
            }
        }

        return $proposals;
    }

    /**
     * List information about package proposal
     *
     * @param  int ID
     * @return array
     */
    function listOne($id)
    {
        global $dbh;
        $query = "SELECT p.*, u.email, u.name AS user_name FROM package_proposals p " .
            "LEFT JOIN users u ON u.handle = p.handle " .
            "WHERE id = '" . $id . "'";
        return $dbh->getRow($query, DB_FETCHMODE_ASSOC);
    }

    /**
     * List packages, for which the voting is finished
     *
     * @return array
     */
    function listFinished()
    {
        return proposal::listAll(" AND date_ends < NOW() ");
    }

    /**
     * Add new package proposal
     *
     * @param array
     * @return object PEAR::DB Result or PEAR_Error
     */
    function add($pkg)
    {
        global $dbh;
        $i = 0;

        // Create a new user first
        while (true) {
            // If the user is logged in:
            if (!empty($pkg['handle'])) {
                $handle = $pkg['handle'];
                $user =& new PEAR_User($dbh, $handle);

                $set_vars = array('name' => $user->get("name"),
                                  'email' => $user->get("email"),
                                  );
                break;
            }

            $set_vars = array('name' => $pkg['user_firstname'] . " " . $pkg['user_lastname'],
                              'email' => $pkg['user_email'],
                              'showemail' => 1,
                              'password' => md5($pkg['user_password']),
                              'registered' => 1,  // ???
                              'ppp_only'   => 1
                              );

            // Sanity check
            if ($i > 5) {
                return PEAR::raiseError("Creating user failed: The " .
                                        "handle could not be created");
            }

            $handle = proposal::getHandle($pkg, $i);
            $user =& new PEAR_User($dbh, $handle);
            if (!isset($user->created)) {
                $err = $user->insert($handle);

                $errors = 0;
                foreach ($set_vars as $var => $value) {
                    $err = $user->set($var, $value);
                    if (PEAR::isError($err)) {
                        $errors++;
                    }
                }

                if ($errors > 0) {
                    return PEAR::raiseError("Creating user failed");
                }
                break;
            }
            $i++;
        }

        $query = "INSERT INTO package_proposals VALUES " .
            "(?, ?, ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), ?, ?)";
        $sth   = $dbh->prepare($query);
        $id    = $dbh->nextId("package_proposals");

        $err   = $dbh->execute($sth,
                               array($id, $pkg['name'], $pkg['category'],
                                     $handle, $pkg['summary'],
                                     $pkg['desc'],
                                     $pkg['homepage'],
                                     $pkg['source_links'],
                                     "0000-00-00 00:00:00", "open"
                                     )
                               );
        if (!DB::isError($err)) {
            $pkg['id'] = $id;
            proposal::sendMail("new", array_merge($set_vars, $pkg));
        }
        return $err;
    }

    /**
     * Create user handle based on first- and lastname
     *
     * If the name is Mick Jagger, the handle will be "micjag"
     * or "micjag1".
     *
     * @param array   Package-Information
     * @param integer Additional ID which will be appended to
     *                to the handle
     * @return string
     */
    function getHandle($pkg, $i)
    {
        $pkg['user_firstname'] = strtolower($pkg['user_firstname']);
        $pkg['user_lastname'] = strtolower($pkg['user_lastname']);

        if ($i == 0) {
            return substr($pkg['user_firstname'], 0, 3) .
                substr($pkg['user_lastname'], 0, 3);
        } else {
            return substr($pkg['user_firstname'], 0, 3) .
                substr($pkg['user_lastname'], 0, 3) . $i;
        }
    }

    /**
     * Delete package proposal
     *
     * @param integer Proposal ID
     * @param integer Who has caused the removement?
     * @return object PEAR::DB Result or PEAR_Error
     */
    function delete($id, $type = PEARWEB_AUTO_DELETE)
    {
        global $dbh;

        $query = "DELETE FROM package_proposals WHERE id = " . (int)$id;
        $sth = $dbh->query($query);

        if (!DB::isError($sth)) {
            if ($type == PEARWEB_AUTO_DELETE) {
                proposal::sendMail("auto_delete");
            } else {
                proposal::sendMail("user_delete");
            }
        }

        return $sth;
    }


    /**
     * Voting for a package
     *
     * @access  public
     * @param   array
     * @return  object
     */
    function vote($data)
    {
        global $dbh;

        // Check if the user has already voted for this proposal
        if (!empty($_COOKIE['PEAR_VOTE_' . $data['id']])) {
            return PEAR::raiseError("You have already given a vote for this proposal.");
        }

        // The cookie expires in 15 days
        $cookie_lifetime = time()+(60*60*24*15);

        $admin = (user::isAdmin(@$_COOKIE['PEAR_USER']) ? true : false);

        $query = "INSERT INTO package_votes VALUES (?, NOW(), ?, ?, ?)";
        $sth   = $dbh->prepare($query);
        $err   = $dbh->execute($sth, array($data['id'], (int)$admin,
                                           $data['val'], 
                                           substr($data['comment'], 0, 1000)
                                           )
                               );
        if (!DB::isError($err)) {
            setcookie("PEAR_VOTE_" . $data['id'], $data['id'], $cookie_lifetime);
        }
        return $err;
    }

    /**
     * Evaluate all expired votes that are still marked as open
     *
     * @static
     * @return void
     */
    function evaluate()
    {
        $list = proposal::listAll("status = 'open' AND date_ends < NOW()");

        foreach ($list as $vote) {
            if (proposal::isAccepted($vote)) {
                proposal::accept($vote);
            } else {
                proposal::reject($vote);
            }
        }
    }

    /**
     * Evaluates a single voting result
     *
     * If the voting result matches the PEAR proposal rules, the
     * method returns true. Otherwise it returns false.
     *
     * @param  array
     * @return boolean
     */
    function isAccepted(&$vote)
    {
        return false;
    }

    function close($vote)
    {
        global $dbh;

        $query = sprintf("UPDATE package_proposals SET status = 'closed' WHERE id = '%s'",
                         $vote['id']
                         );
        return $dbh->query($query);
    }

    /**
     * Accept package proposal
     *
     * @param array
     * @return mixed
     */
    function accept($vote)
    {
        $result = proposal::close($vote);
        if (!DB::isError($result)) {
            proposal::sendMail("accepted");
            return true;
        }
        return $result;
    }

    /**
     * Reject package proposal
     *
     * @param array
     * @return mixed
     */
    function reject($vote)
    {
        $result = proposal::close($vote);
        if (!DB::isError($result)) {
            proposal::sendMail("rejected");
            return true;
        }
        return $result;
    }

    function sendMail($type, $data)
    {
        switch ($type) {
        case "new":
            $content = file_get_contents("new.txt");
            $content = preg_replace("/{([^}*])}/", "[\\1]", $content);

            $headers = sprintf("From: \"%s\" <%s>\nReply-To: pear-dev@lists.php.net, %s\n" .
                               "X-Admin: mj@php.net\nMessage-ID: <proposal-%s@pear.php.net>\n",
                               $data['name'],
                               $data['email'],
                               $data['email'],
                               $data['id']
                               );
            mail("martin@trior", "Proposal: " . $data['name'], $content, $headers);
            break;

        case "auto_delete":
        case "user_delete":
        }
    }

    function formatDuration($duration)
    {
        $days = floor($duration / (60*60*24));
        $duration = $duration - ($days*60*60*24);
        $hours = floor($duration / (60*60));
        $duration = $duration - ($hours*60*60);
        $minutes = floor($duration / 60);
        return array($days, $hours, $minutes);
    }
}
?>
