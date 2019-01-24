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

use App\Utils\Pagination;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    /**
     * @dataProvider itemsProvider
     */
    public function testPagination($numberOfItems, $page, $from, $to)
    {
        $pagination = new Pagination();
        $pagination->setNumberOfItems($numberOfItems);
        $pagination->setCurrentPage($page);

        $this->assertEquals($from, $pagination->getFrom());
        $this->assertEquals($to, $pagination->getTo());
    }

    /**
     * numberOfItems, page, from, to
     */
    public function itemsProvider()
    {
        return [
            [101, 1, 1, 15],
            [101, 2, 16, 30],
            [28, 1, 1, 15],
            [28, 2, 16, 28],
        ];
    }
}
