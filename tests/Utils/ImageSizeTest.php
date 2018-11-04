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
use App\Utils\ImageSize;

class ImageSizeTest extends TestCase
{
    private $imageSize;

    public function setUp()
    {
        $this->imageSize = new ImageSize();
    }

    /**
     * @dataProvider dateProvider
     */
    public function testGetSize($image, $expected)
    {
        $this->assertEquals($expected, $this->imageSize->getSize($image));
    }

    public function dateProvider()
    {
        return [
            ['/gifs/peclsmall.gif', 'width="106" height="55"'],
            ['/nonexisting/nonexisting.gif', ''],
            ['/notimage/../composer.json', ''],
        ];
    }
}
