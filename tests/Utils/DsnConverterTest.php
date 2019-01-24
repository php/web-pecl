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

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\DsnConverter;

class DsnConverterTest extends TestCase
{
    private $dsnConverter;

    public function setUp()
    {
        $this->dsnConverter = new DsnConverter();
    }

    /**
     * @dataProvider dsnProvider
     */
    public function testToArray($dsn, $expected)
    {
        $this->assertEquals($expected, $this->dsnConverter->toArray($dsn));
    }

    public function dsnProvider()
    {
        return [
            ['mysql://user:password@localhost/pecl', [
                'scheme'   => 'mysql',
                'username' => 'user',
                'password' => 'password',
                'host'     => 'localhost',
                'database' => 'pecl'
            ]],
            ['mysqli://nobody:secretPassW0rd@192.168.0.0.1/dbname', [
                'scheme'   => 'mysqli',
                'username' => 'nobody',
                'password' => 'secretPassW0rd',
                'host'     => '192.168.0.0.1',
                'database' => 'dbname'
            ]],
        ];
    }
}
