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

use App\Auth;
use App\Entity\User as UserEntity;
use App\User;

require_once __DIR__.'/../include/pear-prepend.php';

$container->get(Auth::class)->secure();

if (isset($_GET['handle'])) {
    $handle = $_GET['handle'];
} elseif (isset($_POST['handle'])) {
    $handle = $_POST['handle'];
} else {
    header('Location: /accounts.php');
    exit;
}

if ($handle && !preg_match('@^[0-9A-Za-z_]{2,20}$@', $handle)) {
    echo $template->render('error.php', [
        'errors' => ['No valid handle given.'],
    ]);

    exit;
}

$user = $container->get('auth_user')->is($handle);

if (!$container->get('auth_user')->isAdmin() && !$user) {
    echo $template->render('error.php', [
        'errors' => ['Only the user or PECL administrators may edit account information.'],
    ]);

    exit;
}

$command = isset($_POST['command']) ? $_POST['command'] : 'display';
$content = '';

switch ($command) {
    case 'update':
        $fields = ['name', 'email', 'homepage', 'showemail', 'userinfo', 'pgpkeyid', 'wishlist'];

        $userPostData = ['handle' => $handle];
        foreach ($fields as $field) {
            if ('showemail' === $field) {
                $userPostData['showemail'] = isset($_POST['showemail']) ? 1 : 0;
                continue;
            }

            if (!isset($_POST[$field])) {
                echo $template->render('error.php', [
                    'errors' => ['Invalid data submitted.'],
                ]);

                exit;
            }

            $userPostData[$field] = $_POST[$field];

            if ('userinfo' === $field && strlen($userPostData[$field]) > '255') {
                echo $template->render('error.php', [
                    'errors' => ['User information exceeds the allowed length (255 chars).'],
                ]);

                exit;
            }
        }

        $user = User::update($userPostData);

        $sql = 'SELECT path FROM cvs_acl WHERE username = ? AND access = 1';
        $oldAcl = $database->run($sql, [$handle])->fetchAll(\PDO::FETCH_COLUMN);
        $newAcl = preg_split("/[\r\n]+/", trim(strip_tags($_POST['cvs_acl'])));

        $lostEntries = array_diff($oldAcl, $newAcl);
        $newEntries = array_diff($newAcl, $oldAcl);

        if (count($lostEntries) > 0) {
            $statement = $database->prepare("DELETE FROM cvs_acl WHERE username = ? AND path = ?");

            foreach ($lostEntries as $ent) {
                $content .= "Removing CVS access to $ent for $handle...<br>\n";
                $statement->execute([$handle, $ent]);
            }
        }

        if (count($newEntries) > 0) {
            $statement = $database->prepare("INSERT INTO cvs_acl (username, path, access) VALUES(?,?,?)");
            foreach ($newEntries as $ent) {
                $content .= "Adding CVS access to $ent for $handle...<br />\n";
                $statement->execute([$handle, $ent, 1]);
            }
        }

        $content .= '<div class="success">Your information was successfully updated.</div>';

        break;

    case 'change_password':
        $user = new UserEntity($database, $handle);

        if (
            empty($_POST['password_old'])
            || empty($_POST['password'])
            || empty($_POST['password2'])
        ) {
            echo $template->render('error.php', [
                'errors' => ['Please fill out all password fields.'],
            ]);

            exit;
        }

        if (
            !$container->get('auth_user')->isAdmin()
            && !password_verify($_POST['password_old'], $user->get('password'))
        ) {
            echo $template->render('error.php', [
                'errors' => ['You provided a wrong old password.'],
            ]);

            exit;
        }

        if ($_POST['password'] != $_POST['password2']) {
            echo $template->render('error.php', [
                'errors' => ['The new passwords do not match.'],
            ]);

            exit;
        }

        $user->set('password', password_hash($_POST['password'], PASSWORD_DEFAULT));
        if ($user->save()) {
            if (!empty($_POST['PECL_PERSIST'])) {
                $expire = 2147483647;
            } else {
                $expire = 0;
            }

            $content .= '<div class="success">Your password was successfully updated.</div>';
        }

        break;
}

$row = $database->run('SELECT * FROM users WHERE handle = ?', [$handle])->fetch();

$cvsAcl = $database->run('SELECT path FROM cvs_acl WHERE username = ? AND access = 1', [$handle])->fetchAll(\PDO::FETCH_COLUMN);

if (null === $row) {
    echo $template->render('error.php', [
        'errors' => [$handle.' is not a valid account name.'],
        'title' => 'Invalid Account',
    ]);

    exit;
}

echo $template->render('pages/account_edit.php', [
    'user' => $row,
    'handle' => $row['handle'],
    'content' => $content,
    'cvsAcl'   => implode("\n", $cvsAcl),
]);
