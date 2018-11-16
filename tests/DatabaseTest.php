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

namespace App\Tests;

use App\Database;
use App\Database\Adapter;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private $database;
    private $adapter;

    protected function setUp()
    {
        $this->adapter = new Adapter();
        $this->adapter->setDsn('sqlite::memory:');
        $this->database = new Database($this->adapter->getInstance());
    }

    public function testConnection()
    {
        $sql = "DROP TABLE IF EXISTS some_table;";
        $statement = $this->database->run($sql);

        $this->assertInstanceOf(\PDOStatement::class, $statement);

        $sql = "CREATE TABLE some_table (
                id int(11) NOT NULL,
                name varchar(80) NOT NULL default '',
                summary text);
        ";

        $statement = $this->database->run($sql);

        $this->assertInstanceOf(\PDOStatement::class, $statement);
    }
}
