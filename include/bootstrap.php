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
  | Authors: Stig S. Bakken <ssb@fast.no>                                |
  |          Martin Jansen <mj@php.net>                                  |
  |          Tomas V.V.Cox <cox@php.net>                                 |
  |          Richard Heyes <richard@php.net>                             |
  |          Ferenc Kovacs <tyrael@php.net>                              |
  |          Greg Beaver <cellog@php.net>                                |
  |          Pierre Joye <pierre@php.net>                                |
  |          Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

/**
 * Application bootstrap file. On this level the classes are autoloaded,
 * configuration is initialized and database connection is established.
 */

use App\Autoloader;
use App\Config;
use App\Database;
use App\Rest;
use App\Database\Adapter;
use App\Entity\Package;
use App\Repository\CategoryRepository;
use App\Repository\PackageRepository;
use App\Repository\UserRepository;
use App\Utils\Filesystem;
use App\Utils\FormatDate;
use App\Utils\ImageSize;
use Symfony\Component\Dotenv\Dotenv;

// Dual autoloader until PSR-4 and Composer's autoloader are fully supported.
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
} else {
    require_once __DIR__.'/../src/Autoloader.php';

    $loader = new Autoloader();

    $loader->addNamespace('App\\', __DIR__.'/../src/');

    $loader->addClassmap('Graph', __DIR__.'/jpgraph/jpgraph.php');
    $loader->addClassmap('BarPlot', __DIR__.'/jpgraph/jpgraph_bar.php');
    $loader->addClassmap('GroupBarPlot', __DIR__.'/jpgraph/jpgraph_bar.php');
}

// Configuration initialization
if (class_exists(Dotenv::class) && file_exists(__DIR__.'/../.env')) {
    $dotenv = new Dotenv();
    $dotenv->load(__DIR__.'/../.env');
    $configurations = require __DIR__.'/../config/app.php';
} else {
    $configurations = array_merge(
        require __DIR__.'/../config/app.php',
        require __DIR__.'/../config/app_prod.php'
    );
}

$config = new Config($configurations);

// Set how PHP errors are reported
if ($config->get('env') === 'dev') {
    // Report all errors in development environment
    error_reporting(E_ALL);

    // Display errors also to screen
    ini_set('display_errors', 1);
} else {
    // Report errors set by default php.ini without E_NOTICE
    error_reporting(error_reporting() & ~E_NOTICE);

    // Don't display errors, errors should be logged in a file via php.ini
    ini_set('display_errors', 0);
}

// TODO: check if something better can be done.
// Some of these classes define constants in global scope and need to be included
// separately before requiring other classes.
require_once 'PEAR.php';
require_once 'PEAR/Common.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/PackageFile/Parser/v2.php';
require_once 'Archive/Tar.php';
require_once 'PEAR/PackageFile.php';
require_once 'Net/URL2.php';
require_once 'Pager/Pager.php';
require_once 'HTTP/Upload.php';

// Set application default time zone to UTC for all dates.
date_default_timezone_set('UTC');

// Database access with PDO enabled endpoints
$pdoDsn = 'mysql:host='.$config->get('db_host').';dbname='.$config->get('db_name').';charset=utf8';
$databaseAdapter = new Adapter();
$databaseAdapter->setDsn($pdoDsn);
$databaseAdapter->setUsername($config->get('db_username'));
$databaseAdapter->setPassword($config->get('db_password'));
$database = new Database($databaseAdapter->getInstance());

// Initialization of some services
$filesystem = new Filesystem();
$formatDate = new FormatDate();
$imageSize = new ImageSize();

$rest = new Rest();
$rest->setDatabase($database);
$rest->setFilesystem($filesystem);
$rest->setDirectory($config->get('rest_dir'));
$rest->setScheme($config->get('scheme'));
$rest->setHost($config->get('host'));
$categoryRepository = new CategoryRepository($database);
$rest->setCategoryRepository($categoryRepository);
$packageRepository = new PackageRepository($database);
$rest->setPackageRepository($packageRepository);
$userRepository = new UserRepository($database);
$rest->setUserRepository($userRepository);

$packageEntity = new Package();
$packageEntity->setDatabase($database);
$packageEntity->setRest($rest);

// Inject package entity dependency to REST generator
$rest->setPackage($packageEntity);
