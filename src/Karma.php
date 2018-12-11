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
  | Authors: Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App;

use App\Database;

/**
 * Class to manage the user permissions system
 *
 * This system makes it not only possible to provide a fully developed
 * permission system, but it also allows us to set up a php.net-wide
 * single-sign-on system some time in the future.
 */
class Karma
{
    private $database;

    /**
     * Class constructor.
     *
     * @param object Instance of Database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Determine if the given user has karma for the given $level
     *
     * The given level is either a concrete karma level or an alias that will be
     * mapped to a karma group in this method.
     *
     * TODO: The karma levels were once set to be renamed from pear|pecl.* to *.
     *       The migration script is located in bin/update-karma.php
     *       Current PECL site uses only two karma levels of users: administrator
     *       and registered user (maintainer). Extension maintainer roles are
     *       defined in a separate database table "maintains". Todo: check if
     *       this will be ever utilized again, or this should be migrated to a
     *       common php.net accounts procedure using the master.php.net.
     *
     * @param  string Username
     * @param  string Level
     * @return boolean
     */
    public function has($user, $level)
    {
        switch ($level) {
            case 'pear.pepr':
                $levels = ['pear.pepr', 'pear.user', 'pear.dev', 'pear.admin', 'pear.group'];
            break;

            case 'pear.pepr.admin':
                $levels = ['pear.admin', 'pear.group', 'pear.pepr.admin'];
            break;

            case 'pear.user':
                $levels = ['pear.user', 'pear.pepr', 'pear.dev', 'pear.admin', 'pear.group'];
            break;

            case 'pear.dev':
                $levels = ['pear.dev', 'pear.admin', 'pear.group'];
            break;

            case 'pear.admin':
                $levels = ['pear.admin', 'pear.group'];
            break;

            case 'pear.group':
                $levels = ['pear.group'];
            break;

            case 'global.karma.manager':
                $levels = ['pear.group'];
            break;

            case 'doc.chm-upload':
                $levels = ['pear.doc.chm-upload', 'pear.group'];
            break;

            default:
                $levels = [$level];
            break;
        }

        $placeholders = [];
        $arguments = [$user];

        foreach ($levels as $level) {
            $placeholders[] = '?';
            $arguments[] = $level;
        }

        $sql = 'SELECT *
                FROM karma
                WHERE user = ?
                AND level IN ('.implode(',', $placeholders).')
        ';

        $results = $statement = $this->database->run($sql, $arguments)->fetchAll();

        return (count($results) > 0);
    }
}
