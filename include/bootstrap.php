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
use App\Database\Adapter;
use Symfony\Component\Dotenv\Dotenv;
use \DB as DB;

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
require_once 'DB.php';
require_once 'DB/storage.php';
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
// TODO: This is in the process of migration from the deprecated PEAR DB package
// to a PDO handler.
if (
    isset($_SERVER['SCRIPT_FILENAME'])
    && in_array(str_replace(realpath(__DIR__.'/..'), '', realpath($_SERVER["SCRIPT_FILENAME"])), [
        '/bin/cron/update-win-pkg-cache.php',
        '/bin/cleanup-user.php',
        '/bin/drop-unused-tables.php',
        '/bin/export.php',
        '/bin/generate-rest.php',
        '/bin/update-karma.php',
        '/bin/update-vcs-link.php',
        '/public_html/account-info.php',
        '/public_html/account-mail.php',
        '/public_html/json.php',
        '/public_html/news/pdo.php',
        '/public_html/package-new.php',
        '/public_html/package-stats.php',
        '/public_html/wishlist.php',
    ])
) {
    $pdoDsn = 'mysql:host='.$config->get('db_host').';dbname='.$config->get('db_name').';charset=utf8';

    $databaseAdapter = new Adapter();
    $databaseAdapter->setDsn($pdoDsn);
    $databaseAdapter->setUsername($config->get('db_username'));
    $databaseAdapter->setPassword($config->get('db_password'));

    $database = new Database($databaseAdapter->getInstance());
}

// Connect to database also using PEAR DB for the rest of the site endpoints.
$dsn = $config->get('db_scheme');
$dsn .= '://'.$config->get('db_username');
$dsn .= ':'.$config->get('db_password');
$dsn .= '@'.$config->get('db_host');
$dsn .= '/'.$config->get('db_name');

$options = [
    'persistent' => false,
    'portability' => DB_PORTABILITY_ALL,
];

$dbh = DB::connect($dsn, $options);
$dbh->query('SET NAMES utf8');
