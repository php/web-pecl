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
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Config;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

/**
 * Configuration installer for running it as a Composer script.
 */
class Installer
{
    /**
     * Create a default configuration settings for development environment.
     */
    public static function installConfig(Event $event)
    {
        $distEnvFile = __DIR__.'/../../.env.dist';
        $targetEnvFile = __DIR__.'/../../.env';

        if ($event->isDevMode() && !file_exists($targetEnvFile)) {
            copy($distEnvFile, $targetEnvFile);
        }
    }
}
