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
  | Authors: Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

response_header("About this site");
?>

<h1>About this site</h1>

<p>This site has been created and is maintained by a number of people,
which are listed on the <a href="/credits.php">credits page</a>. If you would
like to contact them, you can write to
<a href="mailto:php-webmaster@lists.php.net">php-webmaster@lists.php.net</a>.
</p>

<p>It has been built with <a href="https://httpd.apache.org">Apache</a>,
<a href="https://php.net">PHP</a>, and <a href="https://www.mysql.com">MySQL</a>.
The source code of the website is
<a href="https://git.php.net/?p=web/pecl.git">available via git</a>.
</p>

<p>Read the <a href="privacy.php">privacy policy</a>.</p>

<?php
response_footer();
