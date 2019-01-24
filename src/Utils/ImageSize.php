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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

namespace App\Utils;

/**
 * Helper class to determine image size.
 */
class ImageSize
{
    private $documentRoot;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->documentRoot = realpath(__DIR__.'/../../public_html');
    }

    /**
     * Returns image size attributes (width="..." height="...").
     */
    public function getSize($file)
    {
        $path = $this->documentRoot.$file;

        if (file_exists($path) && ($size = @getimagesize($path))) {
            return $size[3];
        }

        return '';
    }
}
