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
 * Application bootstrap file. On this level the classes are autoloaded,
 * configuration is initialized and database connection is established.
 */

use App\Config;
use Symfony\Component\Dotenv\Dotenv;

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
require_once 'Pager/Pager.php';

if (
    isset($_SERVER['REQUEST_URI'])
    && in_array($_SERVER['REQUEST_URI'], [
        '/news/index.php'
    ])
) {
    spl_autoload_register(function ($class) {
        // Application root namespace for classes in src directory
        $prefix = 'App\\';

        $length = strlen($prefix);

        if (0 !== strncmp($prefix, $class, $length)) {
            // Check if this is JPGraph dependency
            if (in_array($class, ['BarPlot', 'Graph', 'GroupBarPlot'])) {
                require_once __DIR__.'/jpgraph/jpgraph.php';
                require_once __DIR__.'/jpgraph/jpgraph_bar.php';
            }

            return;
        }

        $file = __DIR__ .'/../src/'.str_replace('\\', '/', substr($class, $length)).'.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
} else {
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
    require_once __DIR__.'/../src/Utils/DsnConverter.php';
    require_once __DIR__.'/../src/Utils/Filesystem.php';
    require_once __DIR__.'/../src/Utils/FormatDate.php';
    require_once __DIR__.'/../src/Utils/ImageSize.php';
    require_once __DIR__.'/../src/Utils/Licenser.php';
    require_once __DIR__.'/../src/Utils/PhpMasterClient.php';
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

// Set application default time zone to UTC for all dates.
date_default_timezone_set('UTC');

// Database access with PDO enabled endpoints
if (
    isset($_SERVER['REQUEST_URI'])
    && in_array($_SERVER['REQUEST_URI'], [
        '/news/pdo.php'
    ])
) {
    $pdo = new \PDO(
        'mysql:host='.$config->get('db_host').';dbname='.$config->get('db_name').';charset=utf8',
        $config->get('db_username'),
        $config->get('db_password'),
        [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}

// Connect to database using PEAR DB for the rest of the site endpoints
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
