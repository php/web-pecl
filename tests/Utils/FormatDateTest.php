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
use App\Utils\FormatDate;

class FormatDateTest extends TestCase
{
    /**
     * @dataProvider dateProvider
     */
    public function testUtc($date, $expected, $format)
    {
        $formatDate = new FormatDate();

        $this->assertEquals($expected, $formatDate->utc($date, $format));
    }

    public function dateProvider()
    {
        return [
            ['2018-10-10 10:10:10', '2018-10-10 10:10 UTC', null],
            ['2017-05-02 10:02:10', '2017-05-02', 'Y-m-d'],
            ['0000-00-00 00:00:00', date('Y-m-d'), 'Y-m-d'],
            [null, date('Y-m-d'), 'Y-m-d'],
            ['', date('Y-m-d'), 'Y-m-d'],
            [0, date('Y-m-d'), 'Y-m-d'],
        ];
    }
}
