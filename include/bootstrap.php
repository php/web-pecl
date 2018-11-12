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
 * Application bootstrap with autoloader and configuration initialization.
 */

use App\Config;
use Symfony\Component\Dotenv\Dotenv;

// Dual autoloader until PSR-4 and Composer's autoloader are fully supported.
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
} else {
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
}

// Include PEAR dependencies
require_once 'Archive/Tar.php';
require_once 'DB.php';
require_once 'DB/storage.php';
require_once 'HTML/TreeMenu.php';
require_once 'HTTP/Upload.php';
require_once 'Net/URL2.php';
require_once 'Pager/Pager.php';
require_once 'PEAR.php';
require_once 'PEAR/Common.php';
require_once 'PEAR/Config.php';
require_once 'PEAR/PackageFile.php';
require_once 'PEAR/PackageFile/Parser/v2.php';

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
