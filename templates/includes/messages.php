<?php if (count($messages) > 0): ?>
    <div class="<?= isset($type) ? $this->e($type) : 'errors' ?>">
        <div><?= isset($heading) ? $heading : 'ERROR:' ?></div>
        <ul>
            <?php foreach ($messages as $message): ?>
                <li><?= $this->e($message) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>
