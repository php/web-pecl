<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
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
   $Id$
*/

auth_require();

define('HTML_FORM_TH_ATTR', 'class="form-label_left"');
define('HTML_FORM_TD_ATTR', 'class="form-input"');
require_once 'HTML/Form.php';

if (isset($_GET['handle'])) {
    $handle = $_GET['handle'];
} elseif (isset($_POST['handle'])) {
    $handle = $_POST['handle'];
} else {
    $handle = false;
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

$admin = $auth_user->isAdmin();
$user  = $auth_user->is($handle);

if (!$admin && !$user) {
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
        $fields_list = array("name", "email", "homepage", "showemail", "userinfo", "pgpkeyid", "wishlist");

        $user_data_post = array('handle' => $handle);
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

        $user = user::update($user_data_post);

        $old_acl = $dbh->getCol('SELECT path FROM cvs_acl '.
                                'WHERE username = ' . "'$handle'" . ' AND access = 1', 0);

        $new_acl = preg_split("/[\r\n]+/", trim(strip_tags($_POST['cvs_acl'])));

        $lost_entries = array_diff($old_acl, $new_acl);
        $new_entries = array_diff($new_acl, $old_acl);

        if (sizeof($lost_entries) > 0) {
            $sth = $dbh->prepare("DELETE FROM cvs_acl WHERE username = ? ".
                                 "AND path = ?");
            foreach ($lost_entries as $ent) {
                $del = $dbh->affectedRows();
                print "Removing CVS access to $ent for $handle...<br />\n";
                $dbh->execute($sth, array($handle, $ent));
            }
        }

        if (sizeof($new_entries) > 0) {
            $sth = $dbh->prepare("INSERT INTO cvs_acl (username,path,access) ".
                                 "VALUES(?,?,?)");
            foreach ($new_entries as $ent) {
                print "Adding CVS access to $ent for $handle...<br />\n";
                $dbh->execute($sth, array($handle, $ent, 1));
            }
        }

        report_success('Your information was successfully updated.');
        break;

    case 'change_password':
        $user = &new PEAR_User($dbh, $handle);

        if (empty($_POST['password_old']) || empty($_POST['password']) ||
            empty($_POST['password2'])) {

            PEAR::raiseError('Please fill out all password fields.');
            break;
        }

        if (!$admin && $user->get('password') != md5($_POST['password_old'])) {
            PEAR::raiseError('You provided a wrong old password.');
            break;
        }

        if ($_POST['password'] != $_POST['password2']) {
            PEAR::raiseError('The new passwords do not match.');
            break;
        }

        $user->set('password', md5($_POST['password']));
        if ($user->store()) {
            if (!empty($_POST['PEAR_PERSIST'])) {
                $expire = 2147483647;
            } else {
                $expire = 0;
            }

            report_success('Your password was successfully updated.');
        }
        break;
}


$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow('SELECT * FROM users WHERE handle = ?', array($handle));

$cvs_acl_arr = $dbh->getCol('SELECT path FROM cvs_acl'
                            . ' WHERE username = ? AND access = 1', 0,
                            array($handle));
$cvs_acl = implode("\n", $cvs_acl_arr);

if ($row === null) {
    error_handler($handle . ' is not a valid account name.', 'Invalid Account');
}


$form = new HTML_Form(htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES), 'post');

$form->addText('name', '<span class="accesskey">N</span>ame:',
        $row['name'], 40, null, 'accesskey="n"');
$form->addText('email', 'Email:',
        $row['email'], 40, null);
$form->addCheckbox('showemail', 'Show email address?',
        $row['showemail']);
$form->addText('homepage', 'Homepage:',
        $row['homepage'], 40, null);
$form->addText('wishlist', 'Wishlist URI:',
        $row['wishlist'], 40, null);
$form->addText('pgpkeyid', 'PGP Key ID:'
        . '<p class="cell_note">(Without leading 0x)</p>',
        $row['pgpkeyid'], 40, 20);
$form->addTextarea('userinfo',
        'Additional User Information:'
        . '<p class="cell_note">(limited to 255 chars)</p>',
        $row['userinfo'], 40, 5, null);
$form->addTextarea('cvs_acl',
        'SVN Access:',
        $cvs_acl, 40, 5, null);
$form->addSubmit('submit', 'Submit');
$form->addHidden('handle', $handle);
$form->addHidden('command', 'update');
$form->display('class="form-holder" style="margin-bottom: 2em;"'
               . ' cellspacing="1"',
               'Edit Your Information', 'class="form-caption"');


print '<a name="password"></a>' . "\n";
print '<h2>&raquo; Manage your password</h2>' . "\n";

$form = new HTML_Form(htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES), 'post');
$form->addPlaintext('<span class="accesskey">O</span>ld Password:',
        $form->returnPassword('password_old', '', 40, 0,
                              'accesskey="o"'));
$form->addPassword('password', 'Password',
        '', 10, null);
$form->addCheckbox('PEAR_PERSIST', 'Remember username and password?',
        '');
$form->addSubmit('submit', 'Submit');
$form->addHidden('handle', $handle);
$form->addHidden('command', 'change_password');
$form->display('class="form-holder" cellspacing="1"',
               'Change Password', 'class="form-caption"');
ob_end_flush();
response_footer();
?>
