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

response_header("Syndication feeds");
?>

<h1>Syndication feeds</h1>

<h2>RSS</h2>

<p>We have a number of <a href="http://web.resource.org/rss/1.0/">RSS</a> feeds
available for your viewing pleasure:</p>

<ul>
    <li><a href="/feeds/latest.rss">/feeds/latest.rss</a>: The latest 10 releases</li>
    <li>Feeds per category:
        <ul>
            <li><a href="/feeds/cat_authentication.rss">/feeds/cat_authentication.rss</a>: Authentication</li>
            <li><a href="/feeds/cat_benchmarking.rss">/feeds/cat_benchmarking.rss</a>: Benchmarking</li>
            <li>For all other categories, the same scheme as shown above applies</li>
        </ul>
    </li>
    <li>Feeds per package:
        <ul>
            <li><a href="/feeds/pkg_apc.rss">/feeds/pkg_apc.rss</a>: APC</li>
            <li><a href="/feeds/pkg_apc.rss">/feeds/pkg_apc.rss</a>: APC</li>
            <li><a href="/feeds/pkg_phar.rss">/feeds/pkg_phar.rss</a>: phar</li>
            <li>For all other packages, the same scheme as shown above applies</li>
        </ul>
    </li>
</ul>

<p>If you have questions or suggestions about the RSS service, please contact
the <a href="mailto:php-webmaster@lists.php.net">webmasters</a>.</p>

<?php
response_footer();
