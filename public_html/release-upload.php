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
   $Id$
*/

auth_require('pear.dev');

define('HTML_FORM_MAX_FILE_SIZE', 16 * 1024 * 1024); // 16 MB
define('HTML_FORM_TH_ATTR', 'class="form-label_left"');
define('HTML_FORM_TD_ATTR', 'class="form-input"');

require_once 'HTML/Form.php';

$display_form         = true;
$display_verification = false;
$success              = false;
$errors               = array();

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

do {
    if (isset($_POST['upload'])) {
        // Upload Button

        include_once 'HTTP/Upload.php';
        $upload_obj = new HTTP_Upload('en');
        $file = $upload_obj->getFiles('distfile');
        if (PEAR::isError($file)) {
            $errors[] = $file->getMessage();
            break;
        }

        if ($file->isValid()) {
            $file->setName('uniq', 'pear-');
            $file->setValidExtensions('tgz', 'accept');
            $tmpfile = $file->moveTo(PEAR_UPLOAD_TMPDIR);
            if (PEAR::isError($tmpfile)) {
                $errors[] = $tmpfile->getMessage();
                break;
            }
            $tmpsize = $file->getProp('size');
        } elseif ($file->isMissing()) {
            $errors[] = 'No file has been uploaded.';
            break;
        } elseif ($file->isError()) {
            $errors[] = $file->errorMsg();
            break;
        }

        $display_form = false;
        $display_verification = true;

    } elseif (isset($_POST['verify'])) {
        // Verify Button

        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($_POST['distfile']);
        if (!@is_file($distfile)) {
            $errors[] = 'No verified file found.';
            break;
        }

        include_once 'PEAR/Common.php';
        $util =& new PEAR_Common;
        $info = $util->infoFromTgzFile($distfile);
        if (class_exists('PEAR_PackageFile')) {
            $config = &PEAR_Config::singleton();
            $pkg = &new PEAR_PackageFile($config);
            $info = &$pkg->fromTgzFile($distfile, PEAR_VALIDATE_NORMAL);
            if (PEAR::isError($info)) {
                if (is_array($info->getUserInfo())) {
                    foreach ($info->getUserInfo() as $err) {
                        $errors[] = $err['message'];
                    }
                    $errors[] = $info->getMessage();
                }
                break;
            } else {
                $pacid = package::info($info->getPackage(), 'id');
                if (PEAR::isError($pacid)) {
                    $errors[] = $pacid->getMessage();
                    break;
                }
                if (!user::isAdmin($_COOKIE['PEAR_USER']) &&
                    !user::maintains($_COOKIE['PEAR_USER'], $pacid, 'lead')) {
                    $errors[] = 'You don\'t have permissions to upload this release.';
                    break;
                }
                $license = $info->getLicense();
                if (is_array($license)) {
                    $license = $license['_content'];
                }
                $e = package::updateInfo($pacid,
                        array(
                            'summary'     => $info->getSummary(),
                            'description' => $info->getDescription(),
                            'license'     => $license,
                        ));
                if (PEAR::isError($e)) {
                    $errors[] = $e->getMessage();
                    break;
                }
                $users = array();
                foreach ($info->getMaintainers() as $user) {
                    $users[strtolower($user['handle'])] = array(
                                                            'role'   => $user['role'],
                                                            'active' => $user['active'] == 'yes',
                                                          );
                }
                $e = maintainer::updateAll($pacid, $users);
                if (PEAR::isError($e)) {
                    $errors[] = $e->getMessage();
                    break;
                }
                $file = release::upload($info->getPackage(), $info->getVersion(),
                                        $info->getState(), $info->getNotes(),
                                        $distfile, md5_file($distfile));
            }
        } else {
    
            $pacid = package::info($info['package'], 'id');
            if (PEAR::isError($pacid)) {
                $errors[] = $pacid->getMessage();
                break;
            }
            if (!user::isAdmin($_COOKIE['PEAR_USER']) &&
                !user::maintains($_COOKIE['PEAR_USER'], $pacid, 'lead')) {
                $errors[] = 'You don\'t have permissions to upload this release.';
                break;
            }
    
            $e = package::updateInfo($pacid,
                    array(
                        'summary'     => $info['summary'],
                        'description' => $info['description'],
                        'license'     => $info['release_license'],
                    ));
            if (PEAR::isError($e)) {
                $errors[] = $e->getMessage();
                break;
            }
    
            $users = array();
            foreach ($info['maintainers'] as $user) {
                $users[strtolower($user['handle'])] = array(
                                                        'role'   => $user['role'],
                                                        'active' => 1,
                                                      );
            }
    
            $e = maintainer::updateAll($pacid, $users);
            if (PEAR::isError($e)) {
                $errors[] = $e->getMessage();
                break;
            }
            $file = release::upload($info['package'], $info['version'],
                                    $info['release_state'], $info['release_notes'],
                                    $distfile, md5_file($distfile));
        }
        if (PEAR::isError($file)) {
            $ui = $file->getUserInfo();
            $errors[] = 'Error while uploading package: ' .
                         $file->getMessage() . ($ui ? " ($ui)" : '');
            break;
        }
        @unlink($distfile);

        PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'report_warning');
        if (is_a($info, 'PEAR_PackageFile_v1') || is_a($info, 'PEAR_PackageFile_v2')) {
            release::promote_v2($info, $file);
        } else {
            release::promote($info, $file);
        }
        PEAR::popErrorHandling();

        $success              = true;
        $display_form         = true;
        $display_verification = false;

    } elseif (isset($cancel)) {
        // Cancel Button

        $distfile = PEAR_UPLOAD_TMPDIR . '/' . basename($distfile);
        if (@is_file($distfile)) {
            @unlink($distfile);
        }

        $display_form         = true;
        $display_verification = false;
    }
} while (false);

PEAR::popErrorHandling();


if ($display_form) {
    $title = 'Upload New Release';
    response_header($title);

    // Remove that code when release-upload also create new packages
    if (!checkUser($auth_user->handle)) {
        $errors[] = 'You are not registered as lead developer for any packages.';
    }

    echo '<h1>' . $title . "</h1>\n";

    if ($success) {
        if (is_array($info)) {
            report_success('Version ' . $info['version'] . ' of '
                           . $info['package'] . ' has been successfully released, '
                           . 'and its promotion cycle has started.');
            print '<p>';
        } else {
            report_success('Version ' . $info->getVersion() . ' of '
                           . $info->getPackage() . ' has been successfully released, '
                           . 'and its promotion cycle has started.');
        }
        print '</p>';
        print '</div>';
    } else {
        report_error($errors);
    }

    print <<<MSG
<p>
Upload a new package distribution file built using &quot;<code>pear
package</code>&quot; here.  The information from your package.xml file will
be displayed on the next screen for verification. The maximum file size
is 16 MB.
</p>

<p>
<strong>IMPORTANT:</strong> If you have not created a package.xml version 2.0 using
the PEAR 1.4.0a2 command &quot;pear convert&quot; and packaged with &quot;pear
package package.xml package2.xml&quot; your release will not properly upload at pecl.php.net.
</p>

<p>However, you can still upload this release through pear.php.net's upload release box if you do
not wish to try out the new package.xml format right now.  Note that 100% BC is maintained if you follow
these instructions.
</p>

<p>
Uploading new releases is restricted to each package's lead developer(s).
</p>
MSG;

    $form =& new HTML_Form($_SERVER['PHP_SELF'], 'post', '', '',
            'multipart/form-data');
    $form->addFile('distfile',
            '<label for="f" accesskey="i">D<span class="accesskey">i</span>'
            . 'stribution File</label>',
            HTML_FORM_MAX_FILE_SIZE, 40, '', 'id="f"');
    $form->addSubmit('upload', 'Upload!');
    $form->display('class="form-holder" cellspacing="1"',
            'Upload', 'class="form-caption"');
}


if ($display_verification) {
    include_once 'PEAR/Common.php';

    response_header('Upload New Release :: Verify');

    $util =& new PEAR_Common;

    // XXX this will leave files in PEAR_UPLOAD_TMPDIR if users don't
    // complete the next screen.  Janitor cron job recommended!
    $info = $util->infoFromTgzFile(PEAR_UPLOAD_TMPDIR . '/' . $tmpfile);
    if (class_exists('PEAR_PackageFile')) {
        unset($util); // for memory reasons;
        $config = &PEAR_Config::singleton();
        $pkg = &new PEAR_PackageFile($config);
        $info = &$pkg->fromTgzFile(PEAR_UPLOAD_TMPDIR . '/' . $tmpfile, PEAR_VALIDATE_NORMAL);
        $errors = $warnings = array();
        if (PEAR::isError($info)) {
            if (is_array($info->getUserInfo())) {
                foreach ($info->getUserInfo() as $err) {
                    if ($err['level'] == 'error') {
                        $errors[] = $err['message'];
                    } else {
                        $warnings[] = $err['message'];
                    }
                }
            }
            $errors[] = $info->getMessage();
        }
        if ($info->getChannel() != 'pecl.php.net') {
            $errors[] = 'Only channel pecl.php.net packages may be released at pecl.php.net';
        }
        switch ($info->getPackageType()) {
            case 'extsrc' :
                $type = 'Extension Source package';
            break;
            case 'extbin' :
                $type = 'Extension Binary package';
            break;
            case 'php' :
                $type = 'PHP package';
            default :
                $errors[] = 'Release type ' . $info->getPackageType() . ' is not ' .
                    'supported at pecl.php.net, only Extension releases are supported.  ' .
                    'pear.php.net supports php packages';
        }
        report_error($errors, 'errors','ERRORS:<br />'
                     . 'You must correct your package.xml file:');
        report_error($warnings, 'warnings', 'RECOMMENDATIONS:<br />'
                     . 'You may want to correct your package.xml file:');
        $form =& new HTML_Form($_SERVER['PHP_SELF'], 'post');
        $form->addPlaintext('Package:', $info->getPackage());
        $form->addPlaintext('Version:', $info->getVersion());
        $form->addPlaintext('Summary:', htmlspecialchars($info->getSummary()));
        $form->addPlaintext('Description:', nl2br(htmlspecialchars($info->getDescription())));
        $form->addPlaintext('Release State:', $info->getState());
        $form->addPlaintext('Release Date:', $info->getDate());
        $form->addPlaintext('Release Notes:', nl2br(htmlspecialchars($info->getNotes())));
        $form->addPlaintext('Package Type:', $type);
    } else {
    
        // packge.xml conformance
        $errors   = array();
        $warnings = array();
    
        $util->validatePackageInfo($info, $errors, $warnings);
    
        // XXX ADD MASSIVE SANITY CHECKS HERE
        
        report_error($errors, 'errors','ERRORS:<br />'
                     . 'You must correct your package.xml file:');
        report_error($warnings, 'warnings', 'RECOMMENDATIONS:<br />'
                     . 'You may want to correct your package.xml file:');
    
        $check = array(
            'summary',
            'description',
            'release_state',
            'release_date',
            'releases_notes',
        );
        foreach ($check as $key) {
            if (!isset($info[$key])) {
                $info[$key] = 'n/a';
            }
        }
    
        $form =& new HTML_Form($_SERVER['PHP_SELF'], 'post');
        $form->addPlaintext('Package:', $info['package']);
        $form->addPlaintext('Version:', $info['version']);
        $form->addPlaintext('Summary:', htmlspecialchars($info['summary']));
        $form->addPlaintext('Description:', nl2br(htmlspecialchars($info['description'])));
        $form->addPlaintext('Release State:', $info['release_state']);
        $form->addPlaintext('Release Date:', $info['release_date']);
        $form->addPlaintext('Release Notes:', nl2br(htmlspecialchars($info['release_notes'])));
    }

    // Don't show the next step button when errors found
    if (!count($errors)) {
        $form->addSubmit('verify', 'Verify Release');
    }

    $form->addSubmit('cancel', 'Cancel');
    $form->addHidden('distfile', $tmpfile);
    $form->display('class="form-holder" cellspacing="1"',
            'Please verify that the following release information is correct:',
            'class="form-caption"');
}

response_footer();


function checkUser($user, $pacid = null)
{
    global $dbh;
    $add = ($pacid) ? 'AND p.id = ' . $dbh->quoteSmart($pacid) : '';
    // It's a lead or user of the package
    $query = "SELECT m.handle
              FROM packages p, maintains m
              WHERE
                 m.handle = ? AND
                 p.id = m.package $add AND
                 (m.role IN ('lead', 'developer'))";
    $res = $dbh->getOne($query, array($user));
    if ($res !== null) {
        return true;
    }
    // Try to see if the user is an admin
    $res = user::isAdmin($user);
    return ($res === true);
}

?>
