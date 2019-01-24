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

foreach ($vars as $key => $value) {
    $vars[$key] = htmlspecialchars($value, ENT_QUOTES);
}

?>

<form action="/account-edit.php?handle=<?= $vars['handle']; ?>" method="post" class="pecl-form">

    <h2>Edit Your Information</h2>

    <div>
        <label>Name:</label>
        <input type="text" name="name" size="40" value="<?= $vars['name']; ?>" placeholder="Enter your name" required>
    </div>

    <div>
        <label>Email:</label>
        <input type="text" name="email" size="40" value="<?= $vars['email']; ?>" placeholder="Enter your email" required>
    </div>

    <div>
        <label>Show email address?</label>
        <input type="checkbox" name="showemail" <?= $vars['showemail'] ? 'checked' : ''; ?>>
    </div>

    <div>
        <label>Homepage:</label>
        <input type="text" name="homepage" size="40" value="<?= $vars['homepage']; ?>" placeholder="https://example.com">
    </div>

    <div>
        <label>Wishlist URI:</label>
        <input type="text" name="wishlist" size="40" value="<?= $vars['wishlist']; ?>" placeholder="https://example.com">
    </div>

    <div>
        <label>PGP Key ID:<p class="cell_note">(Without leading 0x)</p></label>
        <input type="text" name="pgpkeyid" size="40" value="<?= $vars['pgpkeyid']; ?>" maxlength="20">
    </div>

    <div>
        <label>Additional User Information:<p class="cell_note">(limited to 255 chars)</p></label>
        <textarea name="userinfo" cols="40" rows="5"><?= $vars['userinfo']; ?></textarea>
    </div>

    <div>
        <label>SVN Access:</label>
        <textarea name="cvs_acl" cols="40" rows="5"><?= $vars['cvs_acl']; ?></textarea>
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit">
    </div>

    <input type="hidden" name="handle" value="<?= $vars['handle']; ?>">
    <input type="hidden" name="command" value="update">
    <input type="hidden" name="_fields" value="name:email:showemail:homepage:wishlist:pgpkeyid:userinfo:cvs_acl:submit:handle:command">
</form>
