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

use App\Auth;
use App\Entity\Maintainer;
use App\Entity\Package;
use App\Release;
use App\Repository\PackageRepository;
use App\Rest;
use App\User;
use App\Utils\Uploader;
use \PEAR as PEAR;
use \PEAR_Config as PEAR_Config;
use \PEAR_PackageFile as PEAR_PackageFile;

require_once __DIR__.'/../include/pear-prepend.php';

$container->get(Auth::class)->secure();

$release = $container->get(Release::class);
$packageEntity = $container->get(Package::class);
$authUser = $container->get('auth_user');

$maintainer = new Maintainer();
$maintainer->setDatabase($database);
$maintainer->setRest($container->get(Rest::class));
$maintainer->setAuthUser($authUser);
$maintainer->setPackage($packageEntity);

$displayForm = true;
$displayVerification = false;
$success = false;
$errors = [];
$warnings = [];

PEAR::pushErrorHandling(PEAR_ERROR_RETURN);

if (!file_exists($container->get('tmp_uploads_dir'))) {
    mkdir($container->get('tmp_uploads_dir'), 0777, true);
    chmod($container->get('tmp_uploads_dir'), 0777);
}

$uploader = new Uploader();
$uploader->setMaxFileSize($container->get('max_file_size'));
$uploader->setValidExtension('tgz');
$uploader->setDir($container->get('tmp_uploads_dir'));

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

        $validPackageVersion = true;
        $packageVersionMacrosFound = false;
        $packageXmlExtensionVersion = $info->getVersion();
        $packageName = $info->getName();
        $packageExtensionName = $info->getProvidesExtension();

        foreach ($info->getFileList() as $data) {
            // The file we're looking for is usually named like php_myextname.h,
            // but lets check any .h file in the pkg root.
            if ('src' != $data['role'] ||
                false !== strstr($data['name'], '/') ||
                '.h' != substr($data['name'], -2)) {

                continue;
            }

            $fileContents = $info->getFileContents($data['name']);

            $pat = ',define\s+PHP_(' . $packageName . '|' . $packageExtensionName . ')_VERSION\s+"(.*)",i';
            if (preg_match($pat, $fileContents, $m)) {
                $packageVersionMacrosFound = true;
                $packageFoundExtensionName = $m[1];
                $packageFoundExtensionVersion = $m[2];
            } else {
                unset($fileContents);

                continue;
            }

            if ($packageXmlExtensionVersion == $packageFoundExtensionVersion) {
                $validPackageVersion = true;

                break;
            } else {
                $validPackageVersion = false;
            }
        }

        if (!$validPackageVersion) {
            $nameToShow = $packageVersionMacrosFound ? $packageFoundExtensionName : 'MYEXTNAME';

            if ($packageVersionMacrosFound) {
                $errors[] = "Extension version mismatch between the package.xml ($packageXmlExtensionVersion) "
                    . "and the source code ($packageFoundExtensionVersion). ";
                $errors[] = 'Both version strings have to match. ';

                break;
            } else {
                $warnings[] = 'The compliance between the package version in package.xml and extension source code '
                    . "couldn't be reliably determined. This check fixes the (unintended) "
                    . "version mismatch in phpinfo() and the PECL website. ";
                $warnings[] = "To pass please "
                    . "#define PHP_" . strtoupper($nameToShow) . "_VERSION \"$packageXmlExtensionVersion\" "
                    . "in your php_" . strtolower($nameToShow) . ".h or any other header file "
                    . 'and use it for zend_module_entry definition. ';
                $warnings[] = 'Both version strings have to match. ';
            }
        }

        $displayForm = false;
        $displayVerification = true;

    } elseif (isset($_POST['verify'])) {
        // Verify Button

        $distfile = $container->get('tmp_uploads_dir').'/'.basename($_POST['distfile']);
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
                $pacid = $container->get(PackageRepository::class)->find($info->getPackage(), 'id');
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();

                break;
            }

            if (!$authUser->isAdmin() &&
                !User::maintains($authUser->handle, $pacid, 'lead')) {
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

            $container->get(Rest::class)->savePackageMaintainer($info->getPackage());
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
        $displayForm         = true;
        $displayVerification = false;
    } elseif (isset($_POST['cancel'])) {
        // Cancel button
        $distfile = $container->get('tmp_uploads_dir').'/'.basename($_POST['distfile']);

        if (@is_file($distfile)) {
            @unlink($distfile);
        }

        $displayForm         = true;
        $displayVerification = false;
    }
} while (false);

PEAR::popErrorHandling();

if ($displayForm) {
    $title = 'Upload New Release';

    // Remove that code when release-upload also create new packages
    if (!checkUser($authUser->handle)) {
        $errors[] = 'You are not registered as lead developer for any packages.';
    }
}

if ($displayVerification) {
    $title = 'Upload New Release :: Verify';
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

    $licenseFound = false;

    foreach ($info->getFileList() as $data) {
        if ('doc' != $data['role']) {
            continue;
        }

        // Don't compare with basename($data['name']), the license must be in
        // the package root.
        $validLicenseFiles = [
            'LICENSE', 'license',
            'LICENSE.md', 'license.md',
            'COPYING', 'copying',
            'COPYING.md', 'copying.md',
            'LICENSE.txt', 'license.txt',
            'COPYING.txt', 'copying.txt'
        ];

        if (in_array($data['name'], $validLicenseFiles)) {
            $licenseFound = true;
            break;
        }
    }

    if (!$licenseFound) {
        $warnings[] = 'No LICENSE or COPYING file was found in the root of the package. ';
    }
}

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

echo $template->render('pages/release_upload.php', [
    'title' => $title,
    'displayForm' => $displayForm,
    'success' => $success,
    'maxFileUploadSize' => $container->get('max_file_size'),
    'info' => isset($info) ? $info : null,
    'displayVerification' => $displayVerification,
    'type' => isset($type) ? $type : null,
    'errors' => $errors,
    'warnings' => $warnings,
    'tmpFile' => isset($tmpFile) ? $tmpFile : null,
]);
