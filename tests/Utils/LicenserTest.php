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

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\Licenser;

class LicenserTest extends TestCase
{
    private $licenser;

    public function setUp()
    {
        $this->licenser = new Licenser();
    }

    /**
     * @dataProvider dateProvider
     */
    public function testGetLink($license, $expected)
    {
        $this->assertEquals($expected, $this->licenser->getHtml($license));
    }

    public function dateProvider()
    {
        return [
            ['PHP', '<a href="https://php.net/license/3_01.txt">PHP</a>'],
            ['PHP License', '<a href="https://php.net/license/3_01.txt">PHP License</a>'],
            ['PHP 3.0', '<a href="https://php.net/license/3_0.txt">PHP 3.0</a>'],
            ['GNU Lesser General Public License', '<a href="https://www.gnu.org/licenses/lgpl.html">GNU Lesser General Public License</a>'],
            ['MIT', 'MIT'],
            ['', ''],
        ];
    }
}
