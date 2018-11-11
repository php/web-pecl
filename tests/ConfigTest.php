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

use PHPUnit\Framework\TestCase;
use App\Config;

class ConfigTest extends TestCase
{
    /**
     * @dataProvider configurationProvider
     */
    public function testGet($file, $key, $expected)
    {
        $values = require $file;
        $config = new Config($values);

        $this->assertEquals($expected, $config->get($key));
    }

    public function configurationProvider()
    {
        return [
            [__DIR__.'/fixtures/config.php', 'some_string', 'foo'],
            [__DIR__.'/fixtures/config.php', 'array', ['value_1' => 1]],
        ];
    }
}
