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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

use App\Entity\User as UserEntity;
use App\User;

$auth->secure();

if (isset($_GET['handle'])) {
    $handle = $_GET['handle'];
} elseif (isset($_POST['handle'])) {
    $handle = $_POST['handle'];
} else {
    $handle = false;
    header('Location: /accounts.php');
    exit;
}

if ($handle && !preg_match('@^[0-9A-Za-z_]{2,20}$@', $handle)) {
    response_header('Error:');
    report_error("No valid handle given!");
    response_footer();
    exit();
}

ob_start();
response_header('Edit Profile :: ' . $handle);

print '<h1>Edit Profile: ';
print '<a href="/user/'. $handle . '">' . $handle . '</a></h1>' . "\n";

print "<ul><li><a href=\"#password\">Manage your password</a></li></ul>";

$user  = $auth_user->is($handle);

if (!$auth_user->isAdmin() && !$user) {
    PEAR::raiseError("Only the user or PECL administrators may edit account information.");
    response_footer();

    exit();
}

if (isset($_POST['command']) && strlen($_POST['command'] < 32)) {
    $command = htmlspecialchars($_POST['command'], ENT_QUOTES);
} else {
    $command = 'display';
}

switch ($command) {
    case 'update':
        $fields_list = ["name", "email", "homepage", "showemail", "userinfo", "pgpkeyid", "wishlist"];

        $user_data_post = ['handle' => $handle];
        foreach ($fields_list as $k) {
            if ($k == 'showemail') {
                $user_data_post['showemail'] =  isset($_POST['showemail']) ? 1 : 0;
                continue;
            }

            if (!isset($_POST[$k])) {
                report_error('Invalid data submitted.');
                response_footer();
                exit();
            }

            $user_data_post[$k] = htmlspecialchars($_POST[$k], ENT_QUOTES);

            if ($k == 'userinfo' && strlen($user_data_post[$k]) > '255') {
                report_error('User information exceeds the allowed length (255 chars).');
                response_footer();
                exit();
            }
        }

        $user = User::update($user_data_post);

        $sql = 'SELECT path FROM cvs_acl WHERE username = ? AND access = 1';
        $old_acl = $database->run($sql, [$handle])->fetchAll(\PDO::FETCH_COLUMN);

        $new_acl = preg_split("/[\r\n]+/", trim(strip_tags($_POST['cvs_acl'])));

        $lost_entries = array_diff($old_acl, $new_acl);
        $new_entries = array_diff($new_acl, $old_acl);

        if (count($lost_entries) > 0) {
            $statement = $database->prepare("DELETE FROM cvs_acl WHERE username = ? AND path = ?");

            foreach ($lost_entries as $ent) {
                print "Removing CVS access to $ent for $handle...<br />\n";
                $statement->execute([$handle, $ent]);
            }
        }

        if (count($new_entries) > 0) {
            $statement = $database->prepare("INSERT INTO cvs_acl (username, path, access) VALUES(?,?,?)");
            foreach ($new_entries as $ent) {
                print "Adding CVS access to $ent for $handle...<br />\n";
                $statement->execute([$handle, $ent, 1]);
            }
        }

        echo '<div class="success">Your information was successfully updated.</div>';

        break;

    case 'change_password':
        $user = new UserEntity($database, $handle);

        if (empty($_POST['password_old']) || empty($_POST['password']) ||
            empty($_POST['password2'])) {

            PEAR::raiseError('Please fill out all password fields.');

            break;
        }

        if (!$auth_user->isAdmin() && !password_verify($_POST['password_old'], $user->get('password'))) {
            PEAR::raiseError('You provided a wrong old password.');

            break;
        }

        if ($_POST['password'] != $_POST['password2']) {
            PEAR::raiseError('The new passwords do not match.');

            break;
        }

        $user->set('password', password_hash($_POST['password'], PASSWORD_DEFAULT));
        if ($user->save()) {
            if (!empty($_POST['PECL_PERSIST'])) {
                $expire = 2147483647;
            } else {
                $expire = 0;
            }

            echo '<div class="success">Your password was successfully updated.</div>';
        }

        break;
}

$row = $database->run('SELECT * FROM users WHERE handle = ?', [$handle])->fetch();

$cvs_acl_arr = $database->run('SELECT path FROM cvs_acl WHERE username = ? AND access = 1', [$handle])->fetchAll(\PDO::FETCH_COLUMN);

$cvs_acl = implode("\n", $cvs_acl_arr);

if ($row === null) {
    error_handler(htmlspecialchars($handle, ENT_QUOTES) . ' is not a valid account name.', 'Invalid Account');
}

// Edit account form
$vars = [
    'name'      => $row['name'],
    'email'     => $row['email'],
    'showemail' => $row['showemail'],
    'homepage'  => $row['homepage'],
    'wishlist'  => $row['wishlist'],
    'pgpkeyid'  => $row['pgpkeyid'],
    'userinfo'  => $row['userinfo'],
    'cvs_acl'   => $cvs_acl,
    'handle'    => $row['handle'],
];

include __DIR__.'/../templates/forms/account_edit.php';

// Change password form
$vars = [
    'handle' => $row['handle'],
];

include __DIR__.'/../templates/forms/account_password.php';

ob_end_flush();
response_footer();
