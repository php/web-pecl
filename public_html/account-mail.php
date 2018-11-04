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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

/**
 * Send mail to PECL contributor
 */

// Redirect to the accounts list if no handle was specified
if (!isset($_GET['handle']) || !preg_match('@^[0-9A-Za-z_]{2,20}$@', $_GET['handle'])) {
    header('Location: /accounts.php', true, 301);
    exit;
} else {
    $handle = $_GET['handle'];
    $message = '';
}

function printForm($data = [])
{
    // The first field that's empty
    $focus = '';

    foreach (['name', 'email', 'subject', 'text'] as $key) {
        if (!isset($data[$key])) {
            $data[$key] = '';
            ($focus == '') ? $focus = $key : '';
        }
    }

    $bb = new BorderBox('Send email');

    $vars = [
        'handle' => $_GET['handle'],
        'name' => $data['name'],
        'email' => $data['email'],
        'subject' => $data['subject'],
        'text' => $data['text'],
    ];

    include __DIR__.'/../templates/forms/send_email.php';

    $bb->end();

    echo "<script>\n";
    echo "document.forms.contact." . $focus . ".focus();\n";
    echo "</script>";
}

response_header('Contact');

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow('SELECT * FROM users WHERE registered = 1 '.
                    'AND handle = ?', [$handle]);

if ($row === null) {
    PEAR::raiseError('No account information found!');
}

echo '<h1>Contact ' . $row['name'] . '</h1>';

if (isset($_POST['submit'])) {

    // XXX: Add email validation here
    if ($_POST['name'] == '') {
        $message .= '<li>You have to specify your name.</li>';
    }

    if ($_POST['email'] == '') {
        $message .= '<li>You have to specify your email address.</li>';
    }

    if ($_POST['subject'] == '') {
        $message .= '<li>You have to specify the subject of your correspondence.</li>';
    }

    if ($_POST['text'] == '') {
        $message .= '<li>You have to specify the text of your correspondence.</li>';
    }

    if ($message == '') {
        $text = "[This message has been brought to you via pecl.php.net.]\n\n";
        $text .= wordwrap($_POST['text'], 72);

        if (@mail($row['email'], $_POST['subject'], $text, 'From: "' . $_POST['name'] . '" <' . $_POST['email'] . '>', '-f noreply@php.net')) {
            echo '<p>Your message has been sent successfully.</p>';
        } else {
            PEAR::raiseError('An error occurred while sending the message!');
        }
    } else {
        echo '<p><font color=\'#FF0000\'>An error has occurred:<ul>'
            . $message . '</ul></font></p>';
        printForm($_POST);
    }
} else {
    echo '<p>If you want to get in contact with one of the PECL contributors,'
        . ' you can do this by filling out the following form.</p>';

    // Guess the user if they are logged in
    if (!empty($auth_user)) {
        $data = ['email' => $auth_user->email, 'name' => $auth_user->name];
    } else {
        $data = [];
    }

    printForm($data);
}

response_footer();
