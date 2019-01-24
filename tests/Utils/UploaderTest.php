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
use App\Utils\Uploader;

class UploaderTest extends TestCase
{
    private $fixturesDirectory = __DIR__.'/../fixtures/files';

    /**
     * @dataProvider filesProvider
     */
    public function testUpload($validExtension, $file)
    {
        $_FILES = [];
        $_FILES['uploaded'] = $file;

        $uploader = $this->getMockBuilder(Uploader::class)
            ->setMethods(['isUploadedFile', 'moveUploadedFile'])
            ->getMock();

        $uploader->expects($this->once())
                 ->method('isUploadedFile')
                 ->will($this->returnValue(true));

        $uploader->expects($this->once())
                 ->method('moveUploadedFile')
                 ->will($this->returnValue(true));

        $uploader->setMaxFileSize(16 * 1024 * 1024);
        $uploader->setValidExtension($validExtension);
        $uploader->setDir(__DIR__.'/../../var/uploads');
        $tmpFile = $uploader->upload('uploaded');

        $this->assertNotNull($tmpFile);
    }

    public function filesProvider()
    {
        return [
            [
                'txt',
                [
                    'name' => 'foobar.txt',
                    'tmp_name' => $this->fixturesDirectory.'/foobar.txt',
                    'size' => filesize($this->fixturesDirectory.'/foobar.txt'),
                    'error' => UPLOAD_ERR_OK,
                ]
            ],
            [
                'tgz',
                [
                    'name' => 'hello.tgz',
                    'tmp_name' => $this->fixturesDirectory.'/hello.tgz',
                    'size' => filesize($this->fixturesDirectory.'/hello.tgz'),
                    'error' => UPLOAD_ERR_OK,
                ]
            ]
        ];
    }
}
