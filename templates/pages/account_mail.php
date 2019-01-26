<?php $this->extend('layout.php', ['title' => 'Contact']) ?>

<?php $this->start('content') ?>

<h1>Contact <?= $this->e($name) ?></h1>

<p>If you want to get in contact with one of the PECL contributors, you can do
this by filling out the following form.</p>

<?php $this->insert('forms/send_email.php', ['data' => $data]) ?>

<?php $this->end('content') ?>
