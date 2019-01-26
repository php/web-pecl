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

/**
 * Send email to PECL contributor.
 */

use App\Repository\UserRepository;

require_once __DIR__.'/../include/pear-prepend.php';

// Redirect to the accounts list if no handle was specified
if (!isset($_GET['handle']) || !preg_match('@^[0-9A-Za-z_]{2,20}$@', $_GET['handle'])) {
    header('Location: /accounts.php', true, 301);
    exit;
} else {
    $handle = $_GET['handle'];
    $message = '';
}

$row = $container->get(UserRepository::class)->findActiveByHandle($handle);

if (!$row) {
    PEAR::raiseError('No account information found!');
}

if (isset($_POST['submit'])) {
    $errors = [];

    if ('' === $_POST['name']) {
        $errors[] = 'You have to specify your name.';
    }

    if ('' === $_POST['email']) {
        $errors[] = 'You have to specify your email address.';
    } elseif (false === filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is considered invalid.';
    }

    if ('' === $_POST['subject']) {
        $errors[] = 'You have to specify the subject of your correspondence.';
    }

    if ('' === $_POST['text']) {
        $errors[] = 'You have to specify the text of your correspondence.';
    }

    if (0 === count($errors)) {
        $text = "[This message has been brought to you via pecl.php.net.]\n\n";
        $text .= wordwrap($_POST['text'], 72);

        if (@mail($row['email'], $_POST['subject'], $text, 'From: "' . $_POST['name'] . '" <' . $_POST['email'] . '>', '-f noreply@php.net')) {
            echo $template->render('pages/account_mail_success.php', [
                'name' => $row['name'],
            ]);
        } else {
            PEAR::raiseError('An error occurred while sending the message!');
        }
    } else {
        echo $template->render('pages/account_mail_error.php', [
            'name' => $row['name'],
            'data' => $_POST,
            'errors' => $errors,
        ]);
    }
} else {
    // Check if the user is logged in.
    if (!empty($auth_user)) {
        $data = [
            'email' => $auth_user->get('email'),
            'name' => $auth_user->get('name')
        ];
    } else {
        $data = [];
    }

    echo $template->render('pages/account_mail.php', [
        'name' => $row['name'],
        'data' => $data,
    ]);
}
