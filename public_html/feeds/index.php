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
   $Id$
*/
response_header("Syndication feeds");
?>

<h1>Syndication feeds</h1>

<h2>RSS</h2>

<p>We have a number of <?php echo make_link("http://web.resource.org/rss/1.0/", "RSS"); ?> 
feeds available for your viewing pleasure:</p>

<ul>
  <li><?php echo make_link("/feeds/latest.rss"); ?>: The latest 10 releases</li>
  <li>Feeds per category:
    <ul>
      <li><?php echo make_link("/feeds/cat_authentication.rss"); ?>: Authentication</li>
      <li><?php echo make_link("/feeds/cat_benchmarking.rss"); ?>: Benchmarking</li>
      <li>For all other categories, the same scheme as shown above applies</li>
    </ul>
  </li>
  <li>Feeds per package:
    <ul>
      <li><?php echo make_link("/feeds/pkg_auth.rss"); ?>: Auth</li>
      <li><?php echo make_link("/feeds/pkg_mail_mime.rss"); ?>: Mail_Mime</li>
      <li>For all other packages, the same scheme as shown above applies</li>
    </ul>
  </li>
</ul>

<p>If you have questions or suggestions about the RSS service, please
contact the <?php echo make_mailto_link("pear-webmaster@php.net", "webmasters"); ?>.</p>

<?php
response_footer();
?>

