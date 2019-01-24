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

namespace App;

use App\Database\Adapter;

/**
 * Database handler.
 */
class Database
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * Class constructor.
     */
    public function __construct(\PDO $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Runs given SQL query using prepared statements or query method.
     */
    public function run($sql, array $arguments = [])
    {
        if (!$arguments) {
             return $this->adapter->query($sql);
        }

        $statement = $this->adapter->prepare($sql);
        $statement->execute($arguments);

        return $statement;
    }

    /**
     * A proxy to call native PDO methods if needed.
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->adapter, $method], $args);
    }
}
