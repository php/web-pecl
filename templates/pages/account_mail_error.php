<?php $this->extend('layout.php', ['title' => 'Contact']) ?>

<?php $this->start('content') ?>

<h1>Contact <?= $this->e($name) ?></h1>

<p style="color: #ff0000;">An error has occurred:
    <ul style="color: #ff0000;">
        <?php foreach ($errors as $error): ?>
            <li><?= $this->e($error) ?></li>
        <?php endforeach ?>
    </ul>
</p>

<?php $this->insert('forms/send_email.php', ['data' => $data]) ?>

<?php $this->end('content') ?>
