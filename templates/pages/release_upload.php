<?php $this->extend('layout.php', ['title' => $title]) ?>

<?php $this->start('content') ?>

<?php if ($displayForm): ?>
    <h1>Upload New Release</h1>

    <?php $this->insert('includes/pie.php'); ?>

    <?php if ($success): ?>
        <div class="success">
            Version
            <?= $this->e($info->getVersion()) ?>
            of
            <?= $this->e($info->getPackage()) ?>
            has been successfully released, and its promotion cycle has started.
        </div>
    <?php else: ?>
        <?php $this->insert('includes/messages.php', ['messages' => $errors]); ?>
    <?php endif ?>

    <p>
    Upload a new package distribution file built using &quot;<code>pear
    package</code>&quot; here. The information from your package.xml file will
    be displayed on the next screen for verification. The maximum file size is
    <?= $this->e(round($maxFileUploadSize/1024/1024)) ?> MB.
    </p>

    <p>Uploading new releases is restricted to each package's lead developer(s).</p>

    <?php $this->insert('forms/release_upload.php', ['maxFileUploadSize' => $maxFileUploadSize]) ?>
<?php endif ?>

<?php if ($displayVerification): ?>

    <?php
        $this->insert('includes/messages.php', [
            'messages' => $errors,
            'heading' => 'ERRORS:<br>You must correct your package.xml file:',
        ]);
    ?>

    <?php
        $this->insert('includes/messages.php', [
            'messages' => $warnings,
            'heading' => 'RECOMMENDATIONS:<br>You may want to correct your package.xml file:',
            'type' => 'warnings',
        ]);
    ?>

    <?php $vars = [
        'package' => $info->getPackage(),
        'version' => $info->getVersion(),
        'summary' => $info->getSummary(),
        'description' => $info->getDescription(),
        'state' => $info->getState(),
        'date' => $info->getDate(),
        'notes' => $info->getNotes(),
        'type' => $type,
        'errors' => $errors,
        'tmp_file' => basename($tmpFile),
    ];
    ?>

    <?php $this->insert('forms/release_verify.php', ['vars' => $vars]) ?>
<?php endif ?>

<?php $this->end('content') ?>
