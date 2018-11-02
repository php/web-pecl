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

/**
 * Template snippet form for editing user account.
 */

?>

<form action="<?= htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES); ?>" method="post" enctype="multipart/form-data" class="pecl-form">
    <h2>Upload</h2>

    <div>
        <label for="f" accesskey="i">D<span class="accesskey">i</span>stribution File</label>
        <input type="hidden" name="MAX_FILE_SIZE" value="16777216">
        <input type="file" name="distfile" size="40" id="f">
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="upload" value="Upload">
    </div>

    <input type="hidden" name="_fields" value="distfile:upload">
</form>
