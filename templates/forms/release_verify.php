<?php
foreach ($vars as $key => $var) {
    $vars[$key] = is_string($var) ? htmlspecialchars($var, ENT_QUOTES) : $var;
}

?>

<form action="<?= $this->e($_SERVER['SCRIPT_NAME']) ?>" method="post" class="pecl-form">
    <h2>Please verify that the following release information is correct:</h2>

    <div>
        <label>Package:</label>
        <div class="info"><?= $vars['package']; ?></div>
    </div>

    <div>
        <label>Version:</label>
        <div class="info"><?= $vars['version']; ?></div>
    </div>

    <div>
        <label>Summary:</label>
        <div class="info"><?= $vars['summary']; ?></div>
    </div>

    <div>
        <label>Description:</label>
        <div class="info"><?= nl2br($vars['description']); ?></div>
    </div>

    <div>
        <label>Release State:</label>
        <div class="info"><?= $vars['state']; ?></div>
    </div>

    <div>
        <label>Release Date:</label>
        <div class="info"><?= $vars['date']; ?></div>
    </div>

    <div>
        <label>Release Notes:</label>
        <div class="info"><?= nl2br($vars['notes']); ?></div>
    </div>

    <div>
        <label>Package Type:</label>
        <div class="info"><?= $vars['type']; ?></div>
    </div>

    <div>
        <label>&nbsp;</label>

        <?php // Don't show the next step button when errors found ?>
        <?php if (!count($vars['errors'])): ?>
            <input type="submit" name="verify" value="Verify and Release">
        <?php endif; ?>

        <input type="submit" name="cancel" value="Cancel">
    </div>

    <input type="hidden" name="distfile" value="<?= $vars['tmp_file']; ?>">
    <input type="hidden" name="_fields" value="verify:cancel:distfile">
</form>
