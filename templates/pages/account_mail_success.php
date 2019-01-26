<?php $this->extend('layout.php', ['title' => 'Contact email successfully sent']) ?>

<?php $this->start('content') ?>

<h1>Contact <?= $this->e($name) ?></h1>

<p>Your message has been sent successfully.</p>

<?php $this->end('content') ?>
