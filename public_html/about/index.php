<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
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
which are listed on the <?php echo make_link("/credits.php", "credits page"); ?>.
If you would like to contact them, you can write to
<?php echo make_mailto_link("php-webmaster@lists.php.net"); ?>.</p>

<p>It has been built with <?php echo make_link("http://httpd.apache.org/", "Apache"); ?>,
<?php echo make_link("http://php.net/", "PHP"); ?>,
<?php echo make_link("http://www.mysql.com/", "MySQL"); ?> and some
(as you might have guessed) PEAR packages. Additionally we have started
to use a set of utility classes. We call it
<?php echo make_link("damblan.php", "Damblan"); ?>. The source code of
the website is
<?php echo make_link("http://git.php.net/?p=web/pecl.git", "available via git"); ?>.
</p>

<p>Read the <?php echo make_link("privacy.php", "privacy policy"); ?>.</p>

<?php
response_footer();
