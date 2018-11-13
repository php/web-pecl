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
  |          Tomas V.V.Cox <cox@php.net>                                 |
  |          Martin Jansen <mj@php.net>                                  |
  |          Gregory Beaver <cellog@php.net>                             |
  |          Richard Heyes <richard@php.net>                             |
  +----------------------------------------------------------------------+
*/

namespace App\Entity;

use \DB_storage as DB_storage;

/**
 * User entity class.
 */
class User extends DB_storage
{
    /**
     * Class constructor.
     */
    public function __construct(&$dbh, $user)
    {
        parent::__construct("users", "handle", $dbh);

        $this->pushErrorHandling(PEAR_ERROR_RETURN);
        $this->setup($user);
        $this->popErrorHandling();
    }

    /**
     * Check if user's username matches.
     */
    public function is($handle)
    {
        return (strtolower($handle) == strtolower($this->handle));
    }

    public function isAdmin()
    {
        return ($this->admin == 1);
    }
}
