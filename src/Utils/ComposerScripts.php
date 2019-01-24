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
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Utils;

use App\Config;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Service class for running composer scripts when installing application.
 */
class ComposerScripts
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

    /**
     * Create application temporary and upload directories which are not tracked
     * in Git.
     */
    public static function createDirectories(Event $event)
    {
        if (!$event->isDevMode()) {
            return;
        }

        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');

        require_once $vendorDir.'/autoload.php';

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
        $configurations = require __DIR__.'/../../config/app.php';
        $config = new Config($configurations);

        if (!file_exists($config->get('tmp_dir'))) {
            mkdir($config->get('tmp_dir'), 0777, true);
            chmod($config->get('tmp_dir'), 0777);
        }

        if (!file_exists($config->get('tmp_uploads_dir'))) {
            mkdir($config->get('tmp_uploads_dir'), 0777, true);
            chmod($config->get('tmp_uploads_dir'), 0777);
        }

        if (!file_exists($config->get('packages_dir'))) {
            mkdir($config->get('packages_dir'), 0777, true);
            chmod($config->get('packages_dir'), 0777);
        }

        if (!file_exists($config->get('rest_dir'))) {
            mkdir($config->get('rest_dir'), 0777, true);
            chmod($config->get('rest_dir'), 0777);
        }
    }
}
