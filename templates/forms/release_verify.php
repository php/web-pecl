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
  | Authors: Stig S. Bakken <ssb@fast.no>                                |
  |          Colin Viebrock <cmv@php.net>                                |
  |          Tomas V.V.Cox <cox@php.net>                                 |
  |          Martin Jansen <mj@php.net>                                  |
  |          Richard Heyes <richard@php.net>                             |
  |          Ferenc Kovacs <tyrael@php.net>                              |
  |          Pierre Joye <pierre@php.net>                                |
  |          Wez Furlong <wez@php.net>                                   |
  |          Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

/**
 * Template snippet form for verifying uploaded release.
 */

foreach ($vars as $key => $var) {
    $vars[$key] = is_string($var) ? htmlspecialchars($var, ENT_QUOTES) : $var;
}

?>

<form action="<?= htmlspecialchars($_SERVER['SCRIPT_NAME'], ENT_QUOTES); ?>" method="post" class="pecl-form">
    <h2>Please verify that the following release information is correct:</h2>

    <div>
        <label>Package:</label>
        <div class="info"><?= $vars['package']; ?></div>
    </div>

    <div>
        <label>Version:</label>
        <div class="info"><?= $vars['version']; ?></div>
    </div>

    <div>
        <label>Summary:</label>
        <div class="info"><?= $vars['summary']; ?></div>
    </div>

    <div>
        <label>Description:</label>
        <div class="info"><?= nl2br($vars['description']); ?></div>
    </div>

    <div>
        <label>Release State:</label>
        <div class="info"><?= $vars['state']; ?></div>
    </div>

    <div>
        <label>Release Date:</label>
        <div class="info"><?= $vars['date']; ?></div>
    </div>

    <div>
        <label>Release Notes:</label>
        <div class="info"><?= nl2br($vars['notes']); ?></div>
    </div>

    <div>
        <label>Package Type:</label>
        <div class="info"><?= $vars['type']; ?></div>
    </div>

    <div>
        <label>&nbsp;</label>

        <?php // Don't show the next step button when errors found ?>
        <?php if (!count($vars['errors'])): ?>
            <input type="submit" name="verify" value="Verify Release">
        <?php endif; ?>

        <input type="submit" name="cancel" value="Cancel">
    </div>

    <input type="hidden" name="distfile" value="<?= $vars['tmpfile']; ?>">
    <input type="hidden" name="_fields" value="verify:cancel:distfile">
</form>
