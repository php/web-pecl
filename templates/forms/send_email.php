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
 * Template snippet form for sending emails.
 */

foreach ($vars as $key => $var) {
    $vars[$key] = htmlspecialchars($var, ENT_QUOTES);
}

?>

<form action="/account-mail.php?handle=<?= $vars['handle']; ?>" method="post" name="contact" class="pecl-form">
    <div>
        <label>Your name:</label>
        <input type="text" name="name" size="30" value="<?= $vars['name']; ?>" placeholder="Enter your name" required>
    </div>

    <div>
        <label>Your email:</label>
        <input type="email" name="email" size="30" value="<?= $vars['email']; ?>" placeholder="Enter your email" required>
    </div>

    <div>
        <label>Subject:</label>
        <input type="text" name="subject" size="30" value="<?= $vars['subject']; ?>">
    </div>

    <div>
        <label>Text:</label>
        <textarea name="text" cols="35" rows="10" required placeholder="Enter your message"><?= $vars['text']; ?></textarea>
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit">
    </div>

    <input type="hidden" name="_fields" value="name:email:subject:text:submit">
</form>
