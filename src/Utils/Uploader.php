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

namespace App\Utils;

/**
 * A basic upload service class for uploading files via HTML forms.
 */
class Uploader
{
    /**
     * Maximum allowed file size in bytes.
     */
    private $maxFileSize = 2 * 1024 * 1024;

    /**
     * Valid file extension.
     */
    private $validExtension;

    /**
     * Destination directory.
     */
    private $dir;

    /**
     * Set the maximum allowed file size in bytes.
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
    }

    /**
     * Set allowed file extension without leading dot. For example, 'tgz'.
     */
    public function setValidExtension($validExtension)
    {
        $this->validExtension = $validExtension;
    }

    /**
     * Set destination directory.
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Upload file.
     */
    public function upload($key)
    {
        $files = isset($_FILES[$key]) ? $_FILES[$key] : [];

        // Check if uploaded file size exceeds the ini post_max_size directive.
        if(
            empty($_FILES)
            && empty($_POST)
            && isset($_SERVER['REQUEST_METHOD'])
            && strtolower($_SERVER['REQUEST_METHOD']) === 'post'
        ) {
            $max = ini_get('post_max_size');
            throw new \Exception('Error on upload: Exceeded POST content length server limit of '.$max);
        }

        // Some other upload error happened
        if (empty($files) || $files['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Error on upload: Something went wrong. Error code: '.$files['error']);
        }

        // Be sure we're dealing with an upload
        if ($this->isUploadedFile($files['tmp_name']) === false) {
            throw new \Exception('Error on upload: Invalid file definition');
        }

        // Check file extension
        $uploadedName = $files['name'];
        $ext = $this->getFileExtension($uploadedName);
        if (isset($this->validExtension) && $ext !== $this->validExtension) {
            throw new \Exception('Error on upload: Invalid file extension. Should be .'.$this->validExtension);
        }

        // Check file size
        if ($files['size'] > $this->maxFileSize) {
            throw new \Exception('Error on upload: Exceeded file size limit '.$this->maxFileSize.' bytes');
        }

        // Rename the uploaded file
        $destination = $this->dir.'/'.$this->renameFile($uploadedName);

        // Move uploaded file to final destination
        if (!$this->moveUploadedFile($files['tmp_name'], $destination)) {
            throw new \Exception('Error on upload: Something went wrong');
        }

        return $destination;
    }

    /**
     * Checks if given file has been uploaded via POST method. This is wrapped
     * into a separate method for convenience of testing it via phpunit and using
     * a mock.
     */
    protected function isUploadedFile($file)
    {
        return is_uploaded_file($file);
    }

    /**
     * Move uploaded file to destination. This method is wrapping PHP function
     * to allow testing with PHPUnit and creating a mock object.
     */
    protected function moveUploadedFile($source, $destination)
    {
        return move_uploaded_file($source, $destination);
    }

    /**
     * Rename file to a unique name.
     */
    protected function renameFile($filename)
    {
        $ext = $this->getFileExtension($filename);

        $rand = 'pecl-'.uniqid(rand());

        $i = 0;
        while (true) {
            $newName = $rand.$i.'.'.$ext;

            if (!file_exists($this->dir.'/'.$newName)) {
                return $newName;
            }

            $i++;
        }
    }

    /**
     * Returns file extension without a leading dot.
     */
    protected function getFileExtension($filename)
    {
        return strtolower(substr($filename, strripos($filename, '.') + 1));
    }
}
