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
   $Id: account-edit.php -1   $
*/

require_once 'HTML/Form.php';

/*  TODO: use handle regex */
$handle = filter_input(INPUT_GET, 'handle', FILTER_SANITIZE_STRING);

$admin = $auth_user->isAdmin();

if (!$admin && !$user) {
    PEAR::raiseError("Only the user or PECL administrators may edit account information.");
    response_footer();
    exit();
}

$fields_list = array("homepage", "userinfo", "wishlist");

$user_data_post = array('handle' => $handle);

foreach ($fields_list as $k) {
    if (!isset($_POST[$k])) {
        report_error('Invalid data submitted.');

        exit();
    }

    $user_data_post[$k] = htmlspecialchars($_POST[$k], ENT_QUOTES);

    if ($k == 'userinfo' && strlen($user_data_post[$k]) > '255') {
        report_error('User information exceeds the allowed length (255 chars).');
        exit();
    }
}

$user = user::update($user_data_post);

report_success('Your information was successfully updated.');



$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$row = $dbh->getRow('SELECT * FROM users WHERE handle = ?', array($handle));

if ($row === null) {
    error_handler($handle . ' is not a valid account name.', 'Invalid Account');
}

$data = array();
$page = new PeclPage('/developer/page_developer.html');
$page->title = 'Edit Package ' . $package_name;
$page->jquery = true;
$page->addData($data);
$page->addJsSrc('/js/package-edit.js');
$page->setTemplate(PECL_TEMPLATE_DIR . '/developer/package-edit.html');
$page->render();

echo $page->html;
/*
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
*/