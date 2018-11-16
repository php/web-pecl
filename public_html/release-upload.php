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

use App\Maintainer;
use App\Package;
use App\Release;
use App\User;
use \HTTP_Upload as HTTP_Upload;
use \PEAR_PackageFile as PEAR_PackageFile;
use \PEAR as PEAR;
use \PEAR_Config as PEAR_Config;
use \PEAR_Common as PEAR_Common;

auth_require('pear.dev');

$release = new Release();
$release->setDbh($dbh);
$release->setAuthUser($auth_user);
$release->setRest($rest);
$release->setPackagesDir($config->get('packages_dir'));

$display_form         = true;
$display_verification = false;
$success              = false;
$errors               = [];

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

if (!file_exists($config->get('tmp_uploads_dir'))) {
    mkdir($config->get('tmp_uploads_dir'), 0777, true);
    chmod($config->get('tmp_uploads_dir'), 0777);
}

do {
    if (isset($_POST['upload'])) {
        // Upload Button
        $upload_obj = new HTTP_Upload('en');
        $file = $upload_obj->getFiles('distfile');

        if (PEAR::isError($file)) {
            $errors[] = $file->getMessage();

            break;
        }

        if ($file->isValid()) {
            $file->setName('uniq', 'pear-');
            $file->setValidExtensions('tgz', 'accept');
            $tmpfile = $file->moveTo($config->get('tmp_uploads_dir'));

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

    $pearConfig = PEAR_Config::singleton();
    $pkg = new PEAR_PackageFile($pearConfig);
    $info = $pkg->fromTgzFile($config->get('tmp_uploads_dir').'/'.$tmpfile, PEAR_VALIDATE_NORMAL);
    $errors = $warnings = [];

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
        break;
    }

    if (version_compare($info->getPackageXmlVersion(), '2.0', '<')) {
        $errors[] = 'package.xml v1 format is not supported anymore, please update your package.xml to 2.0. ';
        break;
    }

    $pkg_version_ok = true;
    $pkg_version_macros_found = false;
    $pkg_xml_ext_version = $info->getVersion();
    $pkg_name = $info->getName();
    $pkg_extname = $info->getProvidesExtension();
    foreach ($info->getFileList() as $file_name => $file_data) {
        // The file we're looking for is usually named like php_myextname.h, but
        // lets check any .h file in the pkg root.
        if ("src" != $file_data["role"] ||
            false !== strstr($file_data["name"], "/") ||
            ".h" != substr($file_data["name"], -2)) {

            continue;
        }

        $file_contents = $info->getFileContents($file_data["name"]);

        $pat = ',define\s+PHP_(' . $pkg_name . '|' . $pkg_extname . ')_VERSION\s+"(.*)",i';
        if (preg_match($pat, $file_contents, $m)) {
            $pkg_version_macros_found = true;
            $pkg_found_ext_name = $m[1];
            $pkg_found_ext_version = $m[2];
        } else {
            unset($file_contents);

            continue;
        }

        if ($pkg_xml_ext_version == $pkg_found_ext_version) {
            $pkg_version_ok = true;

            break;
        } else {
            $pkg_version_ok = false;
        }
    }

    if (!$pkg_version_ok) {
        $name_to_show = $pkg_version_macros_found ? $pkg_found_ext_name : "MYEXTNAME";

        if ($pkg_version_macros_found) {
            $errors[] = "Extension version mismatch between the package.xml ($pkg_xml_ext_version) "
                . "and the source code ($pkg_found_ext_version). ";
            $errors[] = "Both version strings have to match. ";
            break;
        } else {
            $warnings[] = "The compliance between the package version in package.xml and extension source code "
                . "couldn't be reliably determined. This check fixes the (unintended) "
                . "version mismatch in phpinfo() and the PECL website. ";
            $warnings[] = "To pass please "
                . "#define PHP_" . strtoupper($name_to_show) . "_VERSION \"$pkg_xml_ext_version\" "
                . "in your php_" . strtolower($name_to_show) . ".h or any other header file "
                . "and use it for zend_module_entry definition. ";
            $warnings[] = "Both version strings have to match. ";
        }
    }

    $display_form = false;
    $display_verification = true;

    } elseif (isset($_POST['verify'])) {
        // Verify Button

        $distfile = $config->get('tmp_uploads_dir').'/'.basename($_POST['distfile']);
        if (!@is_file($distfile)) {
            $errors[] = 'No verified file found.';
            break;
        }

        $util = new PEAR_Common;
        $info = $util->infoFromTgzFile($distfile);
        if (class_exists('PEAR_PackageFile')) {
            $pearConfig = PEAR_Config::singleton();
            $pkg = new PEAR_PackageFile($pearConfig);
            $info = $pkg->fromTgzFile($distfile, PEAR_VALIDATE_NORMAL);
            if (PEAR::isError($info)) {
                if (is_array($info->getUserInfo())) {
                    foreach ($info->getUserInfo() as $err) {
                        $errors[] = $err['message'];
                    }

                    $errors[] = $info->getMessage();
                }

                break;
            } else {
                $pacid = Package::info($info->getPackage(), 'id');

                if (PEAR::isError($pacid)) {
                    $errors[] = $pacid->getMessage();

                    break;
                }

                if (!$auth_user->isAdmin() &&
                    !User::maintains($auth_user->handle, $pacid, 'lead')) {
                    $errors[] = 'You don\'t have permissions to upload this release.';
                    break;
                }

                $license = $info->getLicense();

                if (is_array($license)) {
                    $license = $license['_content'];
                }

                $e = Package::updateInfo($pacid, [
                    'summary'     => $info->getSummary(),
                    'description' => $info->getDescription(),
                    'license'     => $license,
                ]);

                if (PEAR::isError($e)) {
                    $errors[] = $e->getMessage();
                    break;
                }

                $users = [];

                foreach ($info->getMaintainers() as $user) {
                    $users[strtolower($user['handle'])] = [
                        'role'   => $user['role'],
                        'active' => !isset($user['active']) || $user['active'] == 'yes',
                    ];
                }

                $e = Maintainer::updateAll($pacid, $users);

                if (PEAR::isError($e)) {
                    $errors[] = $e->getMessage();

                    break;
                }

                $rest->savePackageMaintainer($info->getPackage());
                $file = $release->upload(
                    $info->getPackage(),
                    $info->getVersion(),
                    $info->getState(),
                    $info->getNotes(),
                    $distfile,
                    md5_file($distfile)
                );
            }
        } else {
            $pacid = Package::info($info['package'], 'id');

            if (PEAR::isError($pacid)) {
                $errors[] = $pacid->getMessage();

                break;
            }

            if (!$auth_user->isAdmin() &&
                !User::maintains($auth_user->handle, $pacid, 'lead')) {
                $errors[] = 'You don\'t have permissions to upload this release.';

                break;
            }

            $e = Package::updateInfo($pacid, [
                'summary'     => $info['summary'],
                'description' => $info['description'],
                'license'     => $info['release_license'],
            ]);

            if (PEAR::isError($e)) {
                $errors[] = $e->getMessage();

                break;
            }

            $users = [];

            foreach ($info['maintainers'] as $user) {
                $users[strtolower($user['handle'])] = [
                    'role'   => $user['role'],
                    'active' => 1,
                ];
            }

            $e = Maintainer::updateAll($pacid, $users);

            if (PEAR::isError($e)) {
                $errors[] = $e->getMessage();

                break;
            }

            $file = $release->upload(
                $info['package'],
                $info['version'],
                $info['release_state'],
                $info['release_notes'],
                $distfile,
                md5_file($distfile)
            );
        }

        if (PEAR::isError($file)) {
            $ui = $file->getUserInfo();
            $errors[] = 'Error while uploading package: '.$file->getMessage().($ui ? " ($ui)" : '');

            break;
        }

        @unlink($distfile);

        PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'report_warning');

        if (is_a($info, 'PEAR_PackageFile_v1') || is_a($info, 'PEAR_PackageFile_v2')) {
            $release->promote_v2($info, $file);
        } else {
            $release->promote($info, $file);
        }

        PEAR::popErrorHandling();

        $success              = true;
        $display_form         = true;
        $display_verification = false;
    } elseif (isset($cancel)) {
        // Cancel Button
        $distfile = $config->get('tmp_uploads_dir').'/'.basename($distfile);

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
        echo '<div class="success">';
        if (is_array($info)) {
            echo 'Version '
                . htmlspecialchars($info['version'], ENT_QUOTES)
                . ' of '
                . htmlspecialchars($info['package'], ENT_QUITES)
                . ' has been successfully released, and its promotion cycle has started.';
        } else {
            echo 'Version '
                . htmlspecialchars($info->getVersion(), ENT_QUOTES)
                . ' of '
                . htmlspecialchars($info->getPackage(), ENT_QUOTES)
                . ' has been successfully released, and its promotion cycle has started.';
        }
        echo '</div>';
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
Uploading new releases is restricted to each package's lead developer(s).
</p>
MSG;

    include __DIR__.'/../templates/forms/release_upload.php';
}

if ($display_verification) {
    response_header('Upload New Release :: Verify');
    $pearConfig = PEAR_Config::singleton();
    $pkg = new PEAR_PackageFile($pearConfig);
    $info = $pkg->fromTgzFile($config->get('tmp_uploads_dir').'/'.$tmpfile, PEAR_VALIDATE_NORMAL);
    $errors = $warnings = [];

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
        $warnings[] = 'Your package uses package.xml 1.0.  With the release of PEAR 1.4.0 stable, '
            . 'PECL packages will require package.xml 2.0 and channel name "pecl.php.net"';
    }

    // this next switch may never be used, but is here in case it turns out to be a good move
    switch ($info->getPackageType()) {
        case 'extsrc' :
            $type = 'Extension Source package';
        break;
        case 'zendextsrc' :
            $type = 'Zend Extension Source package';
        break;
        case 'extbin' :
            $type = 'Extension Binary package';
        break;
        case 'zendextbin' :
            $type = 'Zend Extension Binary package';
        break;
        case 'php' :
            $type = 'PHP package';

            if ($info->getPackagexmlVersion() == '1.0') {
                $warnings[] = 'package.xml 1.0 cannot distinguish between different release types';
            }
        break;
        default :
            $errors[] = 'Release type ' . $info->getPackageType() . ' is not ' .
                'supported at pecl.php.net, only Extension releases are supported.  ' .
                'pear.php.net supports php packages';
    }

    $license_found = false;

    foreach ($info->getFileList() as $file_name => $file_data) {
        if ("doc" != $file_data["role"]) {
            continue;
        }

        // Don't compare with basename($file_data['name']), the license has to
        // be in the package root.
        $lic_fnames = [
                "LICENSE", "license",
                "LICENSE.md", "license.md",
                "COPYING", "copying",
                "COPYING.md", "copying.md",
                "LICENSE.txt", "license.txt",
                "COPYING.txt", "copying.txt"
        ];

        if (in_array($file_data["name"], $lic_fnames)) {
            $license_found = true;
            break;
        }
    }

    if (!$license_found) {
        $warnings[] = "No LICENSE or COPYING file was found in the root of the package. ";
    }

    report_error($errors, 'errors','ERRORS:<br>You must correct your package.xml file:');
    report_error($warnings, 'warnings', 'RECOMMENDATIONS:<br>You may want to correct your package.xml file:');

    $vars = [
        'package' => $info->getPackage(),
        'version' => $info->getVersion(),
        'summary' => $info->getSummary(),
        'description' => $info->getDescription(),
        'state' => $info->getState(),
        'date' => $info->getDate(),
        'notes' => $info->getNotes(),
        'type' => $type,
        'errors' => $errors,
        'tmpfile' => $tmpfile,
    ];

    include __DIR__.'/../templates/forms/release_verify.php';
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
    $res = $dbh->getOne($query, [$user]);

    if ($res !== null) {
        return true;
    }

    // Try to see if the user is an admin
    $res = User::isAdmin($user);

    return ($res === true);
}
