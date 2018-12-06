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

namespace App\Utils;

/**
 * Extractor utility to get contents of a file from a tar gzip archive.
 */
class Extractor
{
    /**
     * Tar gzip (.tgz or tar.gz) archive file.
     */
    private $archive;

    /**
     * Class constructor.
     */
    public function __construct($archive)
    {
        $this->archive = $archive;
    }

    /**
     * Get contents of file in archive.
     */
    public function getFileContents($file)
    {
        $stream = 'phar://'.$this->archive.'/'.$file;

        if (!file_exists($stream)) {
            return false;
        }

        return file_get_contents($stream);
    }
}
