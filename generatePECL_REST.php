<?php
/** 
 * Generate static REST files for pecl.php.net from existing data
 * @author Greg Beaver <cellog@php.net>
 * @version $Id$
 */
/**
 * Useful files to have
 */
set_include_path(dirname(__FILE__) . '/include' . PATH_SEPARATOR . get_include_path());
ob_start();
require_once "pear-config.php";
include PECL_INCLUDE_DIR . '/pear-database-category.php';
include PECL_INCLUDE_DIR . '/pear-database-package.php';

if ($_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME) {
    error_reporting(E_ALL);
    define('DEVBOX', true);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
    define('DEVBOX', false);
}

require_once "PEAR.php";

include_once "pear-database.php";
include_once "pear-rest.php";
if (!isset($pear_rest)) {
    if (isset($_SERVER['argv']) && $_SERVER['argv'][1] == 'pecl') {
        $pear_rest = new pear_rest('/var/lib/peclweb/rest');
    } else {
        $pear_rest = new pear_rest(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'public_html' .
            DIRECTORY_SEPARATOR . 'rest');
    }
}

include_once "DB.php";
include_once "DB/storage.php";

if (empty($dbh)) {
    $options = array(
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    );
    $dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
}
ob_end_clean();
PEAR::setErrorHandling(PEAR_ERROR_DIE);
require_once 'System.php';
System::rm(array('-r', $pear_rest->_restdir));
System::mkdir(array('-p', $pear_rest->_restdir));
chmod($pear_rest->_restdir, 0777);
echo "Generating Category REST...\n";
foreach (category::listAll() as $category) {
    echo "  $category[name]...";
    $pear_rest->saveCategoryREST($category['name']);
    echo "done\n";
}
$pear_rest->saveAllCategoriesREST();
echo "Generating Maintainer REST...\n";
$maintainers = $dbh->getAll('SELECT * FROM users', array(), DB_FETCHMODE_ASSOC);
foreach ($maintainers as $maintainer) {
    echo "  $maintainer[handle]...";
    $pear_rest->saveMaintainerREST($maintainer['handle']);
    echo "done\n";
}
echo "Generating All Maintainers REST...\n";
$pear_rest->saveAllMaintainersREST();
echo "done\n";
echo "Generating Package REST...\n";
$pear_rest->saveAllPackagesREST();
require_once 'Archive/Tar.php';
require_once 'PEAR/PackageFile.php';
$config = &PEAR_Config::singleton();
$pkg = new PEAR_PackageFile($config);
foreach (package::listAll(false, false, false) as $package => $info) {
    echo "  $package\n";
    $pear_rest->savePackageREST($package);
    echo "     Maintainers...";
    $pear_rest->savePackageMaintainerREST($package);
    echo "...done\n";
    $releases = package::info($package, 'releases');
    if ($releases) {
        echo "     Processing All Releases...";
        $pear_rest->saveAllReleasesREST($package);
        echo "done\n";
        foreach ($releases as $version => $blah) {
            $fileinfo = $dbh->getOne('SELECT fullpath FROM files WHERE release = ?',
                array($blah['id']));
            if (!$fileinfo) {
                echo "     Skipping INVALID Version $version (corrupt in database!)\n";
                continue;
            }
            $tar = &new Archive_Tar($fileinfo);
            if ($pxml = $tar->extractInString('package2.xml')) {
            } elseif ($pxml = $tar->extractInString('package.xml'));
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN);
            $pf = $pkg->fromAnyFile($fileinfo, PEAR_VALIDATE_NORMAL);
            PEAR::popErrorHandling();
            if (!PEAR::isError($pf)) {
                echo "     Version $version...";
                $pear_rest->saveReleaseREST($fileinfo, $pxml, $pf, $blah['doneby'],
                    $blah['id']);
                echo "done\n";
            } else {
                echo "     Skipping INVALID Version $version\n";
            }
        }
        echo "\n";
    } else {
        echo "  done\n";
    }
}
echo "Generating Category Package REST...\n";
foreach (category::listAll() as $category) {
    echo "  $category[name]...";
    $pear_rest->savePackagesCategoryREST($category['name']);
    echo "done\n";
}
?>