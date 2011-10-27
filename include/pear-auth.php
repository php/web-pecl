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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id: pear-auth.php 317355 2011-09-26 21:08:47Z pajoye $
*/
include __DIR__ . '/pear-database.php';

function auth_reject($realm = null, $message = null)
{
    session_destroy();
    if ($message === null) {
        $message = "Please enter your username and password:";
    }

	$GLOBALS['ONLOAD'] = "document.login.PEAR_USER.focus();";

    $redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_STRING);
    $old_url = filter_input(INPUT_POST, 'old_url', FILTER_SANITIZE_URL);
    /*
     * TODO: does not work anymore, fix redirect after login.
     */
    if ($old_url) {
        $data = array('old_url' => htmlspecialchars($old_url));
    } else {
        $data = array('old_url' => htmlspecialchars(PECL_DEVELOPER_URL . $_SERVER['REQUEST_URI']));
    }

    $data['message'] = $message;
    $page = new PeclPage('/developer/page_developer.html');
    $page->title = 'login';
    $page->addData($data);
    $page->setTemplate(PECL_TEMPLATE_DIR . '/developer/login.html');
    $page->render();
    echo $page->html;
    exit();
}

// verify user + pass against the database
function auth_verify($user, $passwd)
{
    global $dbh, $auth_user;

	$error = false;

	if(!auth_verify_master($user, $passwd)) {
		$auth_user = null;
		return false;
	}

    if (empty($auth_user)) {
        $auth_user = new PEAR_User($user);
    }
    $auth_user->isAdmin();
	if(!$auth_user->registered){
		//FIXME: create user in local db
		$users = @json_decode(@file_get_contents(SVN_USERLIST));
		if(!is_object($users)){
			error_log("missing file or malformed json in ".SVN_USERLIST."\n", 3, PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log');
			return false;
		}
		if(!isset($users->$user)){
			error_log("$user is missing from ".SVN_USERLIST.", try rebuilding the cache\n", 3, PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log');
			return false;
		}
		$sth = $dbh->prepare("INSERT INTO users
		        (handle, name, email, registered, from_site, active)
		        VALUES(?, ?, ?, 1, 'pecl', 1)");
		$res = $dbh->execute($sth, array($user, $users->$user, $user.'@php.net'));
		if(DB::isError($res)){
			return false;
		}
		$auth_user = new PEAR_User($user);
	}
	$auth_user->_readonly = true;
	return auth_check("developer");
}

// acl check for the given $atom, where true means admin, false developer
function auth_check($atom)
{
    global $dbh;
    static $karma;

    require_once "Damblan/Karma.php";
    
    global $auth_user;

    // admins are almighty
    if (user::isAdmin($auth_user->handle)) {
        return true;
    }

    // Check for backwards compatibility
    if (is_bool($atom)) {
        if ($atom == true) {
            $atom = "admin";
        } else {
            $atom = "developer";
        }
    }

    // every authenticated user has the developer karma
    if (in_array($atom, array("developer"))) {
        return true;
    }

    if (!isset($karma)) {
        $karma = new Damblan_Karma($dbh);
    }
    $a = $karma->has($auth_user->handle, $atom);
    if (PEAR::isError($a)) {
        return false;
    }
    return $a;
}

function auth_require($admin = false)
{
    $res = true;

    if (!is_logged_in()) {
        auth_reject(); // exits
    }

    $num = func_num_args();
    for ($i = 0; $i < $num; $i++) {
        $arg = func_get_arg($i);
        $res = auth_check($arg);
        if ($res == true) {
            return true;
        }
    }

    if ($res == false) {
        response_header("Insufficient Privileges");
        report_error("Insufficient Privileges");
        response_footer();
        exit;
    }

    return true;
}

/**
 * Perform logout for the current user
 */
function auth_logout()
{
	session_unset();
    if ($_SERVER['QUERY_STRING'] == 'logout=1') {
        localRedirect($_SERVER['PHP_SELF']);
    } else {
        localRedirect($_SERVER['PHP_SELF'] . '?' .
                   preg_replace('/logout=1/',
                                '', $_SERVER['QUERY_STRING']));
    }
}

/**
 * check if the user is logged in
 */
function is_logged_in()
{
	global $auth_user;
	if (!$auth_user || !@$auth_user->registered) {
	    return false;
	}
	else{
		return true;
	}
}


$cvspasswd_file = "/repository/CVSROOT/passwd";

function cvs_find_password($user)
{
    global $cvspasswd_file;
    $fp = fopen($cvspasswd_file,"r");
    while ($line = fgets($fp, 120)) {
        list($luser, $passwd, $groups) = explode(":", $line);
        if ($user == $luser) {
            fclose($fp);
            return $passwd;
        }
    }
    fclose($fp);
    return false;
}

function cvs_verify_password($user, $pass)
{
    $psw = cvs_find_password($user);
    if (strlen($psw) > 0) {
        if (crypt($pass,substr($psw,0,2)) == $psw) {
            return true;
        }
    }
    return false;
}

/*
* setup the $auth_user object
*/
function init_auth_user()
{
    global $auth_user;
    if (empty($_SESSION['handle'])) {
        $auth_user = null;
        return false;
    }
    if (!empty($auth_user)) {
        return true;
    }
    $auth_user = new PEAR_User($_SESSION['handle']);
    if (is_logged_in()) {
        return true;
    }
    $auth_user = null;
    return false;
}

function auth_verify_master($user, $pass)
{
    $post = http_build_query(
        array(
            'token' => getenv('AUTH_TOKEN'),
            'username' => $user,
            'password' => $pass,
        )
    );

    $opts = array(
        'method'	=> 'POST',
        'header'	=> 'Content-type: application/x-www-form-urlencoded',
        'content'	=> $post,
    );

    $ctx = stream_context_create(array('http' => $opts));

    $s = file_get_contents('https://master.php.net/fetch/cvsauth.php', false, $ctx);

    $a = @unserialize($s);
    if (!is_array($a)) {
        $error = "Failed to get authentication information.Maybe master is down?";
        error_log("$error\n", 3, PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log');
        return false;
    }
    if (isset($a['errno'])) {
        $error = "Authentication failed: {$a['errstr']}";
        error_log("$error\n", 3, PEAR_TMPDIR . DIRECTORY_SEPARATOR . 'pear-errors.log');
        return false;
    }

    return true;
}

function auth_verify_master_status($user, $pass){
	$post = http_build_query(
	    array(
	        'token' => getenv('AUTH_TOKEN'),
	        'username' => $user,
	        'password' => $pass,
	    )
	);

	$opts = array(
	    'method'	=> 'POST',
	    'header'	=> 'Content-type: application/x-www-form-urlencoded',
	    'content'	=> $post,
	);

	$ctx = stream_context_create(array('http' => $opts));

	$s = file_get_contents('https://master.php.net/fetch/cvsauth.php', false, $ctx);

	return @unserialize($s);
}
?>
	