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
   $Id: account-mail.php 315662 2011-08-29 00:07:22Z tyrael $
*/

/**
 * Send mail to PEAR contributor
 */

/**
 * Redirect to the accounts list if no handle was specified
 */
if (!isset($_GET['handle'])) {
    localRedirect('/accounts.php');
} else {
    $handle = $_GET['handle'];
    $message = '';
}

require_once 'HTML/Form.php';

// {{{ printForm

function printForm($data = array()) 
{
    // The first field that's empty
    $focus = '';

    foreach (array('name', 'email', 'subject', 'text') as $key) {
        if (!isset($data[$key])) {
            $data[$key] = '';
            ($focus == '') ? $focus = $key : '';
        }
    }

    $bb = new BorderBox('Send email');
    $form = new HTML_Form(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . '?handle=' . htmlspecialchars($_GET['handle'], ENT_QUOTES), 'post', 'contact');

    $form->addText('name', 'Your name:', $data['name'], 30);
    $form->addText('email', 'EMail address:', $data['email'], 30);
    $form->addText('subject', 'Subject:', $data['subject'], 30);
    $form->addTextarea('text', 'Text:', $data['text'], 35, 10);
    $form->addSubmit('submit', 'Submit');
    $form->display();

    $bb->end();

    echo "<script language=\"JavaScript\">\n";
    echo "<!--\n";
    echo "document.forms.contact." . $focus . ".focus();\n";
    echo "-->\n";
    echo "</script>";
}

// }}}

response_header('Contact');

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow('SELECT * FROM users WHERE registered = 1 '.
                    'AND handle = ?', array($handle));

if ($row === null) {
    PEAR::raiseError('No account information found!');
}

echo '<h1>Contact ' . $row['name'] . '</h1>';

if (isset($_POST['submit'])) {

    //XXX: Add email validation here
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
        $text = "[This message has been brought to you via pear.php.net.]\n\n";
        $text .= wordwrap($_POST['text'], 72);

        if (@mail($row['email'], $_POST['subject'], $text, 'From: "' . $_POST['name'] . '" <' . $_POST['email'] . '>', '-f bounces-ignored@php.net')) {
            echo '<p>Your message has been sent successfully.</p>';
        } else {
            PEAR::raiseError('An error occured while sending the message!');
        }
    } else {
        echo '<p><font color=\'#FF0000\'>An error has occurred:<ul>'
            . $message . '</ul></font></p>';
        printForm($_POST);
    }
} else {
    echo '<p>If you want to get in contact with one of the PEAR contributors,'
        . ' you can do this by filling out the following form.</p>';

    /** Guess the user if he is logged in */
    if (!empty($auth_user)) {
        $data = array('email' => $auth_user->email, 'name' => $auth_user->name);
    } else {
        $data = array();
    }

    printForm($data);
}

response_footer();
?>
