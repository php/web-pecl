<?php $this->extend('layout.php', ['title' => 'New package']) ?>

<?php $this->start('content') ?>

<?php if (!isset($_POST['submit']) || 0 < count($errors)): ?>
    <h1>New package</h1>

    <?php $this->insert('includes/pie.php'); ?>

    <p>Use this form to register a new PECL package.</p>

    <p><b>Before proceeding</b>, make sure you pick the right name for your
    package. This is usually done through <i>community consensus</i>, which
    means posting a suggestion to the
    <a href="mailto:pecl-dev@lists.php.net">pecl-dev@lists.php.net</a> mailing
    list and have people agree with you.
    </p>

    <?php if (0 < count($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div style="color:#cc0000; font-weight: bold;"><?= $this->e($error) ?></div>
        <?php endforeach ?>
    <?php endif; ?>

    <form method="post" action="<?= $this->e($_SERVER['PHP_SELF']) ?>" class="pecl-form">
        <h2>Register a new PECL package</h2>

        <div>
            <label for="name">Package name <abbr title="required">*</abbr></label>
            <input type="text" name="name" value="<?= $this->noHtml((isset($_POST['name']) ? $_POST['name'] : '')) ?>" size="20" maxlength="80" required>
        </div>
        <div>
            <label for="license">License <abbr title="required">*</abbr></label>
            <input type="text" name="license" value="<?= $this->noHtml((isset($_POST['license']) ? $_POST['license'] : '')) ?>" size="20" maxlength="50" required>
        </div>
        <div>
            <label>Category <abbr title="required">*</abbr></label>
            <select name="category" size="1" required>
                <option value="">--Select category--</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $this->noHtml($category['id']) ?>">
                        <?= $this->e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Summary <abbr title="required">*</abbr></label>
            <input type="text" name="summary" value="<?= $this->e((isset($_POST['summary']) ? $_POST['summary'] : '')) ?>" size="60" placeholder="Enter a one-liner description" required>
        </div>
        <div>
            <label>Full description <abbr title="required">*</abbr></label>
            <textarea name="desc" cols="60" rows="3" required><?= $this->e((isset($_POST['desc']) ? $_POST['desc'] : '')) ?></textarea>
        </div>
        <div>
            <label>Additional project homepage</label>
            <input type="text" name="homepage" value="<?= $this->e((isset($_POST['homepage']) ? $_POST['homepage'] : '')) ?>" size="40" maxlength="255" placeholder="https://example.com">
        </div>
        <div>
            <label>Browse source URL</label>
            <input type="text" name="cvs_link" value="<?= $this->e((isset($_POST['cvs_link']) ? $_POST['cvs_link'] : '')) ?>" size="40" maxlength="255" placeholder="https://git.php.net/?p=pecl/php/operator.git">
        </div>
        <div>
            <label>&nbsp;</label>
            <input type="submit" name="submit" value="Submit request">
        </div>
    </form>

    <?php if (0 < count($errors) && $jumpTo): ?>
    <script>
        document.forms[1].<?= $this->noHtml($jumpTo) ?>.focus();
    </script>
    <?php endif ?>

<?php else: ?>
    <p>The package
        <a href="/package/<?= $this->noHtml($_POST['name']) ?>">
            <?= $this->noHtml($_POST['name']) ?>
        </a>
    has been registered in PECL.<br>
    You have been assigned as lead developer.</p>
<?php endif ?>

<?php $this->end('content') ?>
