<?php
/**
 * Send mail to PEAR contributor
 *
 * $Id$
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
    foreach (array('name', 'email', 'subject', 'text') as $key) {
        if (!isset($data[$key])) {
            $data[$key] = '';
        }
    }

    $bb = new Borderbox('Send email');

    $form = new HTML_Form($_SERVER['PHP_SELF'] . '?handle=' . $_GET['handle'], 'post');
    $form->addText('name', 'Your name:', $data['name'], 30);
    $form->addText('email', 'EMail address:', $data['email'], 30);
    $form->addText('subject', 'Subject:', $data['subject'], 30);
    $form->addTextarea('text', 'Text:', $data['text'], 35, 10);
    $form->addSubmit('submit', 'Submit');
    $form->display();

    $bb->end();
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

        if (@mail('martin@urmel', $_POST['subject'], $text)) {
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
    if (isset($_COOKIE['PEAR_USER'])) {
        $user =& new PEAR_User($dbh, $_COOKIE['PEAR_USER']);
        $data = array('email' => $user->email, 'name' => $user->name);
    } else {
        $data = array();
    }

    printForm($data);
}

response_footer();
?>
