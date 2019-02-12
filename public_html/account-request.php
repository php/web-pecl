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

use App\Repository\UserRepository;
use App\Utils\PhpMasterClient;

require_once __DIR__.'/../include/pear-prepend.php';

$jumpTo = 'handle';
$errors = [];
$requestError = '';
$mailSent = false;

$fields = [
    'handle',
    'firstname',
    'lastname',
    'email',
    'purpose',
    'sponsor',
    'email',
    'moreinfo',
    'homepage',
    'needphp',
    'showemail',
    'password',
    'password2',
];

foreach ($fields as $field) {
    $$field = isset($_POST[$field]) ? $_POST[$field] : null;
}

if (isset($_POST['submit'])) {
    $required = [
        'handle'    => 'your desired username',
        'firstname' => 'your first name',
        'lastname'  => 'your last name',
        'password'  => 'the password',
        'email'     => 'your email address',
        'purpose'   => 'the purpose of your PECL account',
        'sponsor'   => 'references to current users sponsoring your request',
        'language'  => 'programming language being developed',
    ];

    $name = $firstname.' '.$lastname;

    foreach ($required as $field => $desc) {
        if (empty($_POST[$field])) {
            $errors[] = "Please enter $desc";
            $jumpTo = $field;
            break;
        }
    }

    if ('php' !== strtolower(trim($_POST['language']))) {
        $errors[] = 'That was the wrong language choice';
        $jumpTo = 'language';
    }

    if (!preg_match($container->get('valid_usernames_regex'), $handle)) {
        $errors[] = 'Username must start with a letter and contain only letters and digits.';
    }

    if (strlen($handle) > $container->get('max_username_length')) {
        $errors[] = 'Username is too long. It must have '.$container->get('max_username_length').' characters or less.';
    }

    if ($password != $password2) {
        $errors[] = 'Passwords did not match';
        $password = $password2 = '';
        $jumpTo = 'password';
    }

    $handle = strtolower($handle);
    $userRepository = $container->get(UserRepository::class);

    if ($userRepository->findByHandle($handle)) {
        $errors[] = 'Sorry, that username is already taken';
        $jumpTo = 'handle';
    }

    if ($userRepository->findByEmail($email)) {
        $errors[] = 'Sorry, that email is already registered in the database';
        $jumpTo = 'email';
    }

    if (0 === count($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $showemail = @(bool) $showemail;
        $needphp = @(bool) $needphp;

        // Hack to temporarily embed the purpose in the user's userinfo column
        $purpose .= "\n\nSponsor:\n".$sponsor;
        $userinfo = serialize([$purpose, $moreinfo]);
        $sql = "INSERT INTO users (
                    handle,
                    name,
                    email,
                    password,
                    registered,
                    showemail,
                    homepage,
                    userinfo,
                    from_site,
                    active,
                    created
                ) VALUES(
                    ?, ?, ?, ?, 0, ?, ?, ?, 'pecl', 0, ?)
        ";
        $result = $database->run($sql, [$handle, $name, $email, $hash, $showemail ? 1 : 0, $homepage, $userinfo, gmdate('Y-m-d H:i')]);

        // Send request for PHP.net account.
        if ($needphp) {
            $error = $container->get(PhpMasterClient::class)->post([
                'username' => $handle,
                'name'     => $name,
                'email'    => $email,
                'passwd'   => $password,
                'note'     => $purpose,
                'group'    => 'pecl',
                'yesno'    => 'yes',
            ]);

            if ($error) {
                $requestError = "Problem submitting the php.net account request: $error";
            }
        }

        $msg = "Requested from:   {$_SERVER['REMOTE_ADDR']}\n".
                "Username:         {$handle}\n".
                "Real Name:        {$name}\n".
                "Email:            {$email}".
                (@$showemail ? " (show address)" : " (hide address)") . "\n".
                "Need php.net Account: " . (@$needphp ? "yes" : "no") . "\n".
                "Purpose:\n".
                "$purpose\n\n".
                'To handle: '.$container->get('scheme').'://'.$container->get('host')."/admin/?acreq={$handle}\n";

        if ($moreinfo) {
            $msg .= "\nMore info:\n$moreinfo\n";
        }

        $xhdr = "From: $name <$email>";
        $subject = "PECL Account Request: {$handle}";
        $mailSent = mail('pecl-dev@lists.php.net', $subject, $msg, $xhdr, '-f noreply@php.net');
    }
}

echo $template->render('pages/account_request.php', [
    'errors' => $errors,
    'jumpTo' => $jumpTo,
    'requestError' => $requestError,
    'mailSent' => $mailSent,
    'container' => $container,
    'handle' => $handle,
    'firstname' => $firstname,
    'lastname' => $lastname,
    'email' => $email,
    'purpose' => $purpose,
    'sponsor' => $sponsor,
    'email' => $email,
    'moreinfo' => $moreinfo,
    'homepage' => $homepage,
    'needphp' => $needphp,
    'showemail' => $showemail,
]);
