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

use App\Entity\Maintainer;
use App\Release;
use App\User;
use App\Utils\Uploader;
use \PEAR_PackageFile as PEAR_PackageFile;
use \PEAR as PEAR;
use \PEAR_Config as PEAR_Config;

$auth->secure();

$release = new Release();
$release->setDatabase($database);
$release->setAuthUser($auth_user);
$release->setRest($rest);
$release->setPackagesDir($config->get('packages_dir'));
$release->setPackage($packageEntity);

$maintainer = new Maintainer();
$maintainer->setDatabase($database);
$maintainer->setRest($rest);
$maintainer->setAuthUser($auth_user);
$maintainer->setPackage($packageEntity);

$display_form         = true;
$display_verification = false;
$success              = false;
$errors               = [];

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

if (!file_exists($config->get('tmp_uploads_dir'))) {
    mkdir($config->get('tmp_uploads_dir'), 0777, true);
    chmod($config->get('tmp_uploads_dir'), 0777);
}

$uploader = new Uploader();
$uploader->setMaxFileSize($config->get('max_file_size'));
$uploader->setValidExtension('tgz');
$uploader->setDir($config->get('tmp_uploads_dir'));

do {
    if (
        isset($_POST['upload'])
        || (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) === 'post')
    ) {
        try {
            $tmpFile = $uploader->upload('distfile');
        } catch (\Exception $e) {
            // Some error occurred.
            $errors[] = $e->getMessage();

            break;
        }

        $pearConfig = PEAR_Config::singleton();
        $pkg = new PEAR_PackageFile($pearConfig);
        $info = $pkg->fromTgzFile($tmpFile, PEAR_VALIDATE_NORMAL);
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
            // The file we're looking for is usually named like php_myextname.h,
            // but lets check any .h file in the pkg root.
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
            try {
                $pacid = $packageEntity->info($info->getPackage(), 'id');
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();

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

            $e = $packageEntity->updateInfo($pacid, [
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

            $e = $maintainer->updateAll($pacid, $users);

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

        if (PEAR::isError($file)) {
            $ui = $file->getUserInfo();
            $errors[] = 'Error while uploading package: '.$file->getMessage().($ui ? " ($ui)" : '');

            break;
        }

        @unlink($distfile);

        PEAR::pushErrorHandling(PEAR_ERROR_CALLBACK, 'report_warning');

        if (is_a($info, 'PEAR_PackageFile_v1') || is_a($info, 'PEAR_PackageFile_v2')) {
            $release->promote_v2($info, $file);
        }

        PEAR::popErrorHandling();

        $success              = true;
        $display_form         = true;
        $display_verification = false;
    } elseif (isset($_POST['cancel'])) {
        // Cancel button
        $distfile = $config->get('tmp_uploads_dir').'/'.basename($_POST['distfile']);

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
        echo 'Version '
            . htmlspecialchars($info->getVersion(), ENT_QUOTES)
            . ' of '
            . htmlspecialchars($info->getPackage(), ENT_QUOTES)
            . ' has been successfully released, and its promotion cycle has started.';
        echo '</div>';
    } else {
        report_error($errors);
    }

    print "
        <p>
        Upload a new package distribution file built using &quot;<code>pear
        package</code>&quot; here. The information from your package.xml file
        will be displayed on the next screen for verification. The maximum file
        size is ".round($config->get('max_file_size')/1024/1024)." MB.
        </p>

        <p>Uploading new releases is restricted to each package's lead developer(s).</p>
    ";

    include __DIR__.'/../templates/forms/release_upload.php';
}

if ($display_verification) {
    response_header('Upload New Release :: Verify');
    $pearConfig = PEAR_Config::singleton();
    $pkg = new PEAR_PackageFile($pearConfig);
    $info = $pkg->fromTgzFile($tmpFile, PEAR_VALIDATE_NORMAL);
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
        'tmp_file' => basename($tmpFile),
    ];

    include __DIR__.'/../templates/forms/release_verify.php';
}

response_footer();

function checkUser($user, $packageId = null)
{
    global $database;

    // It's a lead or user of the package
    $sql = "SELECT m.handle
            FROM packages p, maintains m
            WHERE
                m.handle = :handle AND
                p.id = m.package";

    $arguments = [':handle' => $user];

    if ($packageId) {
        $sql .= ' AND p.id = :package_id';
        $arguments[':package_id'] = $packageId;
    }

    $sql .= " AND (m.role IN ('lead', 'developer'))";

    $res = $database->run($sql, $arguments)->fetch();

    if ($res) {
        return true;
    }

    // Try to see if the user is an admin
    $res = User::isAdmin($user);

    return ($res === true);
}
