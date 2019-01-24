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
 * Template snippet form for selecting packages in administration.
 */

?>

<form action="/admin/package-maintainers.php" method="get" class="pecl-form">
    <div>
        <label>Package:</label>
        <select name="pid" size="1">
            <?php foreach ($values as $key=>$option): ?>
            <option value="<?= htmlspecialchars($key, ENT_QUOTES); ?>"><?= htmlspecialchars($option, ENT_QUOTES); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit Changes">
    </div>

    <input type="hidden" name="_fields" value="pid:submit">
</form>
