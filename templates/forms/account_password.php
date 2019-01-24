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
 * Template snippet form for editing user's password.
 */

foreach ($vars as $key => $value) {
    $vars[$key] = htmlspecialchars($value, ENT_QUOTES);
}

?>

<a name="password"></a>
<h2>&raquo; Manage your password</h2>

<form action="/account-edit.php?handle=<?= $vars['handle']; ?>" method="post" class="pecl-form">

    <h2>Change password</h2>

    <div>
        <label><span class="accesskey">O</span>ld Password:</label>
        <input type="password" name="password_old" size="40" value="" accesskey="o">
    </div>

    <div>
        <label>Password:</label>
        <input type="password" name="password" size="10" value=""> repeat: <input type="password" name="password2" size="10" value="">
    </div>

    <div>
        <label>Remember username and password?</label>
        <input type="checkbox" name="PECL_PERSIST">
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit">
    </div>

    <input type="hidden" name="handle" value="<?= $vars['handle']; ?>">
    <input type="hidden" name="command" value="change_password">
    <input type="hidden" name="_fields" value="password:PECL_PERSIST:submit:handle:command">
</form>
