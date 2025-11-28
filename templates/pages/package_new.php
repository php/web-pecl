<?php $this->extend('layout.php', ['title' => 'New package']) ?>

<?php $this->start('content') ?>

    <h1>New package</h1>

    <?php $this->insert('includes/pie.php'); ?>

    <p>PECL has been deprecated. <strong>No new packages will be accepted onto PECL</strong>.</p>

    <p>Please follow the instructions to add your extension to the PIE ecosystem: <a href="https://github.com/php/pie/blob/main/docs/extension-maintainers.md">PIE for Extension Maintainers</a>.</p>

<?php $this->end('content') ?>
