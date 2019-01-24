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

/**
 * Template snippet form for editing user account.
 */

?>

<form action="<?= htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES); ?>" method="post" enctype="multipart/form-data" class="pecl-form">
    <h2>Upload</h2>

    <div>
        <label for="f" accesskey="i">D<span class="accesskey">i</span>stribution File</label>
        <?php
            /**
             * The MAX_FILE_SIZE (or max_file_size) hidden POST value is PHP
             * specific and sets additional file size checking on the server
             * side besides the ini directive upload_max_filesize. If
             * max_file_size hidden field is less than the upload_max_filesize
             * ini directive it will stop uploading on the server level once the
             * max_file_size value is reached. It provides additional help to
             * stop too large files. However, the only reliable check is the
             * upload_max_filesize ini directive and checking for filesize in
             * the PHP app code itself using $_FILES['key']['size'] or filesize()
             * of the uploaded file.
             */
        ?>
        <input type="hidden" name="max_file_size" value="<?= $config->get('max_file_size'); ?>">
        <input type="file" name="distfile" id="f" accept=".tgz">
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="upload" value="Upload">
    </div>

    <input type="hidden" name="_fields" value="distfile:upload">
</form>
