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
use App\Utils\PhpMasterClient;
use App\Tests\Server;

class PhpMasterClientTest extends TestCase
{
    private $server;
    private $client;

    public function setUp()
    {
        $this->server = new Server();
    }

    /**
     * @dataProvider postDataProvider
     */
    public function testPost($data, $expected)
    {
        $host = $this->server->start();
        $host .= '/router.php';
        $this->client = new PhpMasterClient($host);

        $this->assertEquals($expected, $this->client->post($data));

        $this->server->stop();
    }

    public function postDataProvider()
    {
        return [
            [[
                'username' => 'teADInTR',
                'name'     => 'John',
                'email'    => 'john@example.com',
                'passwd'   => 'secret',
                'note'     => 'Lorem ipsum',
                'group'    => 'PHP',
                'yesno'    => 'yes',
            ], 'username=teADInTR&name=John&email=john@example.com&passwd=secret&note=Lorem ipsum&group=PHP&yesno=yes&'],
            [[
                'name' => 'John'
            ], 'name=John&']
        ];
    }
}
