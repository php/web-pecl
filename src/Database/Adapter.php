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

namespace App\Database;

/**
 * Database adapter class.
 */
class Adapter
{
    protected $inhibitor = null;
    protected $instance = null;
    private $dsn;
    private $username;
    private $password;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->inhibitor = \Closure::bind(
            function () {
                return new \PDO(
                    $this->dsn,
                    $this->username,
                    $this->password,
                    [
                        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            },
            $this,
            Adapter::class
        );
    }

    /**
     * Set database DSN.
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * Set database username.
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Set database password.
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get Database adapter instance.
     *
     * @return \PDO
     */
    public function getInstance()
    {
        if ($this->instance instanceof \PDO) {
            return $this->instance;
        }

        return $this->instance = call_user_func($this->inhibitor);
    }
}
