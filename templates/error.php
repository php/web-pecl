<?php $this->extend('layout.php', ['title' => isset($title) ? $title : 'Error']) ?>

<?php $this->start('content') ?>

<div class="error">
    <h1>ERROR:</h1>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?= $this->e($error) ?></li>
        <?php endforeach ?>
    </ul>
</div>

<?= isset($content) ? $content : '' ?>

<?php $this->end('content') ?>
