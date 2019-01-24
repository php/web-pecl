<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
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
  |          Richard Heyes <richard@php.net>                             |
  |          Martin Jansen <mj@php.net>                                  |
  |          Wez Furlong <wez@php.net>                                   |
  |          Greg Beaver <cellog@php.net>                                |
  |          Ferenc Kovacs <tyrael@php.net>                              |
  |          Pierre Joye <pierre@php.net>                                |
  |          Rasmus Lerdorf <rasmus@php.net>                             |
  |          Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App;

use App\Database;
use App\Karma;
use App\Entity\User;

/**
 * Main authentication service class.
 */
class Auth
{
    private $database;
    private $karma;
    private $user;
    private $tmpDir;

    /**
     * Class constructor with dependencies injection.
     */
    public function __construct(Database $database, Karma $karma)
    {
        $this->database = $database;
        $this->karma = $karma;
    }

    /**
     * Sets cookie parameters and start session.
     */
    public function initSession()
    {
        // Extend the session cookie lifetime
        $params = session_get_cookie_params();
        session_set_cookie_params(
            (!empty($_COOKIE['REMEMBER_ME'])) ? time()+86400 : null,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );

        session_start();
    }

    /**
     * Set temporary directory for logs.
     */
    public function setTmpDir($tmpDir)
    {
        $this->tmpDir = $tmpDir;
    }

    /**
     * Setup the user object.
     */
    public function initUser()
    {
        if (empty($_SESSION['PECL_USER'])) {
            $this->user = null;

            return $this->user;
        }

        if (!empty($this->user)) {
            return $this->user;
        }

        $this->user = new User($this->database, $_SESSION['PECL_USER']);

        if ($this->isLoggedIn()) {
            return $this->user;
        }

        return $this->user = null;
    }

    /**
     * Unset current session and redirect page visitor back to the previous page.
     */
    public function logout()
    {
        session_unset();

        if ($_SERVER['QUERY_STRING'] === 'logout=1') {
            localRedirect($_SERVER['PHP_SELF']);
        } else {
            localRedirect($_SERVER['PHP_SELF'].'?'.preg_replace('/logout=1/', '', $_SERVER['QUERY_STRING']));
        }
    }

    /**
     * Check if current user is logged in.
     */
    public function isLoggedIn()
    {
        if (!$this->user || !$this->user->get('registered')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Require to be logged in.
     */
    public function secure($admin = false)
    {
        $result = true;

        if (!$this->isLoggedIn()) {
            // Exits
            $this->reject();
        }

        $num = func_num_args();
        for ($i = 0; $i < $num; $i++) {
            $arg = func_get_arg($i);
            $result = $this->check($arg);

            if ($result === true) {
                return true;
            }
        }

        if ($result === false) {
            response_header("Insufficient Privileges");
            report_error("Insufficient Privileges");
            response_footer();

            exit;
        }

        return true;
    }

    /**
     * ACL check for the given $atom, where true means pear.admin, false pear.dev.
     * The pear prefix is used here historically from the upstream original
     * pearweb application database schema until migrations in the database can
     * be done.
     */
    public function check($atom)
    {
        // Admins are almighty
        if ($this->user->isAdmin()) {
            return true;
        }

        // Check for backwards compatibility
        if (is_bool($atom)) {
            if ($atom == true) {
                $atom = "pear.admin";
            } else {
                $atom = "pear.dev";
            }
        }

        // Every authenticated user has the pear.user and pear.dev karma
        if (in_array($atom, ["pear.user", "pear.dev"])) {
            return true;
        }

        return $this->karma->has($this->user->handle, $atom);
    }

    /**
     * Verify given username and password against the database.
     */
    public function verify($username, $password)
    {
        if (empty($this->user)) {
            $this->user = new User($this->database, $username);
        }

        $error = '';
        $ok = false;

        switch (strlen($this->user->get('password'))) {
            // Handle old-style DES-encrypted passwords
            case 13:
                $seed = substr($this->user->get('password'), 0, 2);
                $hash = crypt($password, $seed);

                if ($hash === $this->user->get('password')) {
                    // Update users password if it is held in the db crypt()ed
                    $sql = 'UPDATE users SET password = ? WHERE handle = ?';
                    $arguments = [password_hash($password, PASSWORD_DEFAULT), $username];
                    $this->database->run($sql, $arguments);

                    $ok = true;
                } else {
                    $error = "Authentication: user `$username': invalid password (des)";
                }

                break;

            // Handle old MD5-hashed passwords and update them to password_hash()
            case 32:
                $hash = md5($password);

                if ($hash === $this->user->get('password')) {
                    $sql = 'UPDATE users SET password = ? WHERE handle = ?';
                    $arguments = [password_hash($password, PASSWORD_DEFAULT), $username];
                    $this->database->run($sql, $arguments);

                    $ok = true;
                } else {
                    $error = "Authentication: user `$username': invalid password (md5)";
                }

                break;

            default:
                if (password_verify($password, $this->user->get('password'))) {
                    $ok = true;
                } else {
                    $error = "Authentication: user `$username': invalid password (password_verify)";
                }

                break;
        }

        if (empty($this->user->get('registered'))) {
            if ($username) {
                $error = "Authentication: user `$username' not registered";
            }

            $ok = false;
        }

        if ($ok) {
            // Update last login time
            $sql = 'UPDATE users SET lastlogin = NOW() WHERE handle = ?';
            $this->database->run($sql, [$username]);

            return $this->check("pear.user");
        }

        if ($error) {
            error_log("$error\n", 3, $this->tmpDir.'/pecl-errors.log');
        }

        $this->user = null;

        return false;
    }

    /**
     * Reject given location.
     */
    public function reject($message = null)
    {
        if ($message === null) {
            $message = 'Please enter your username and password:';
        }

        response_header('Login');

        $GLOBALS['ONLOAD'] = "document.login.PECL_USER.focus();";

        if ($message) {
            report_error($message);
        }

        print "<form name=\"login\" action=\"/login.php\" method=\"post\">\n";
        print '<table class="form-holder" cellspacing="1">' . "\n";
        print " <tr>\n";
        print '  <th class="form-label_left">';
        print 'Use<span class="accesskey">r</span>name:</th>' . "\n";
        print '  <td class="form-input">';
        print '<input size="20" name="PECL_USER" accesskey="r" /></td>' . "\n";
        print " </tr>\n";
        print " <tr>\n";
        print '  <th class="form-label_left">Password:</th>' . "\n";
        print '  <td class="form-input">';
        print '<input size="20" name="PECL_PW" type="password" /></td>' . "\n";
        print " </tr>\n";
        print " <tr>\n";
        print '  <th class="form-label_left">&nbsp;</th>' . "\n";
        print '  <td class="form-input" style="white-space: nowrap">';
        print '<input type="checkbox" name="PECL_PERSIST" value="on" id="pecl_persist_chckbx" '.((!empty($_COOKIE['REMEMBER_ME']) || !empty($_POST['PECL_PERSIST']))?'checked="checked " ':'').'/> ';
        print '<label for="pecl_persist_chckbx">Remember username and password.</label></td>' . "\n";
        print " </tr>\n";
        print " <tr>\n";
        print '  <th class="form-label_left">&nbsp;</td>' . "\n";
        print '  <td class="form-input"><input type="submit" value="Log in!" /></td>' . "\n";
        print " </tr>\n";
        print "</table>\n";
        print '<input type="hidden" name="redirect_to" value="';

        if (isset($_POST['redirect_to'])) {
            print htmlspecialchars($_POST['redirect_to'], ENT_QUOTES);
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            print htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES);
        } else {
            print 'login.php';
        }

        print "\" />\n";
        print "</form>\n";
        print '<hr>';
        print "<p><strong>Note:</strong> If you just want to browse the website, ";
        print "you will not need to log in. For all tasks that require ";
        print "authentication, you will be redirected to this form ";
        print "automatically. You can sign up for an account ";
        print "<a href=\"/account-request.php\">over here</a>.</p>";

        response_footer();

        exit;
    }
}
