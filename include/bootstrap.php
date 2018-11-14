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

/**
 * Application bootstrap with loading classes and configuration initialization.
 */

use App\Config;
use Symfony\Component\Dotenv\Dotenv;

// TODO: Refactor these constants in a global scope to a configuration level
require_once __DIR__.'/pear-config.php';

// Autoloading
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
}

require_once 'PEAR.php';
require_once 'DB.php';
require_once 'DB/storage.php';
require_once 'PEAR/Common.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/PackageFile/Parser/v2.php';
require_once 'Archive/Tar.php';
require_once 'PEAR/PackageFile.php';
require_once 'Net/URL2.php';
require_once __DIR__.'/../src/BorderBox.php';
require_once __DIR__.'/../src/Config.php';
require_once __DIR__.'/../src/Category.php';
require_once __DIR__.'/../src/Entity/User.php';
require_once __DIR__.'/../src/Karma.php';
require_once __DIR__.'/../src/Maintainer.php';
require_once __DIR__.'/../src/Note.php';
require_once __DIR__.'/../src/Package.php';
require_once __DIR__.'/../src/PackageDll.php';
require_once __DIR__.'/../src/Release.php';
require_once __DIR__.'/../src/Repository/PackageStats.php';
require_once __DIR__.'/../src/Repository/Release.php';
require_once __DIR__.'/../src/Rest.php';
require_once __DIR__.'/../src/User.php';
require_once __DIR__.'/../src/Utils/Filesystem.php';
require_once __DIR__.'/../src/Utils/FormatDate.php';
require_once __DIR__.'/../src/Utils/ImageSize.php';
require_once __DIR__.'/../src/Utils/Licenser.php';
require_once __DIR__.'/../src/Utils/PhpMasterClient.php';

// Configuration
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

// Set application default time zone to UTC for all dates.
date_default_timezone_set('UTC');

// Database connection

/**
 * Convert 'driver://user:password@hostname/database' DSN string to array of
 * elements.
 */
function getDsn()
{
    $array = [
        'dsn' => '',
        'driver' => '',
        'user' => '',
        'password' => '',
        'host' => '',
        'name' => '',
    ];

    if (isset($_SERVER['PEAR_DATABASE_DSN'])) {
        $array['dsn'] = $_SERVER['PEAR_DATABASE_DSN'];
        $items = preg_split('/\:\/\//', $array['dsn'], -1, PREG_SPLIT_NO_EMPTY);
        $array['driver'] = $items[0];
        $subItems = preg_split('/\:/', $items[1], -1, PREG_SPLIT_NO_EMPTY);
        $array['user'] = $subItems[0];
        $subSubItems = preg_split('/\@/', $subItems[1], -1, PREG_SPLIT_NO_EMPTY);
        $array['password'] = $subSubItems[0];
        $subSubSubItems = preg_split('/\//', $subSubItems[1], -1, PREG_SPLIT_NO_EMPTY);
        $array['host'] = $subSubSubItems[0];
        $array['name'] = $subSubSubItems[1];
    }

    return $array;
}

if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/news/mysql.php') {
    $options = [
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    ];
    $GLOBALS['_NODB'] = true;
    $dsn = getDsn();

    $dbh = DB::connect($dsn['driver'].'://'.$dsn['user'].':'.$dsn['password'].'@'.$dsn['host'].'/'.$dsn['name'], $options);

    if (DB::isError($dbh)) {
        die(DB::errorMessage($dbh));
    }

    $dbh->query('SET NAMES utf8');
    $GLOBALS['_NODB'] = false;
} elseif (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/news/mysqli.php') {
    $options = [
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    ];
    $GLOBALS['_NODB'] = true;
    $dsn = getDsn();

    $dbh = DB::connect('mysqli://'.$dsn['user'].':'.$dsn['password'].'@'.$dsn['host'].'/'.$dsn['name'], $options);

    if (DB::isError($dbh)) {
        die(DB::errorMessage($dbh));
    }

    $dbh->query('SET NAMES utf8');
    $GLOBALS['_NODB'] = false;
} elseif (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/news/pdo.php') {
    $dsn = getDsn();

    // PDO connection
    $dbhPdo = new \PDO(
        'mysql:host='.$dsn['host'].';dbname='.$dsn['name'].';charset=utf8',
        $dsn['user'],
        $dsn['password'],
        [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}
