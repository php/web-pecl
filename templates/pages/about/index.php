<?php $this->extend('layout.php', ['title' => 'About this site']) ?>

<?php $this->start('content') ?>

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

<p>Read the <a href="/about/privacy.php">privacy policy</a>.</p>

<?php $this->end('content') ?>
