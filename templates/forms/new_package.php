<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

/**
 * Template snippet form for registering new package in the user interface.
 */

?>

<h1>New package</h1>

<p>Use this form to register a new PECL package.</p>

<p><b>Before proceeding</b>, make sure you pick the right name for your package.
This is usually done through <i>community consensus</i>, which means posting a
suggestion to the <a href="mailto:pecl-dev@lists.php.net">pecl-dev@lists.php.net</a>
mailing list and have people agree with you.
</p>

<?php if (isset($errorMsg)): ?>
<b><?= $errorMsg; ?></b>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>" class="pecl-form">
    <h2>Register a new PECL package</h2>

    <div>
        <label for="name">Package name <abbr title="required">*</abbr></label>
        <input type="text" name="name" value="<?= htmlspecialchars((isset($_POST['name']) ? $_POST['name'] : ''), ENT_QUOTES); ?>" size="20" maxlength="80" required>
    </div>
    <div>
        <label for="license">License <abbr title="required">*</abbr></label>
        <input type="text" name="license" value="<?= htmlspecialchars((isset($_POST['license']) ? $_POST['license'] : ''), ENT_QUOTES); ?>" size="20" maxlength="50" required>
    </div>
    <div>
        <label>Category <abbr title="required">*</abbr></label>
        <select name="category" size="1" required>
            <option value="">--Select Category--</option>
            <?php foreach ($categories as $key=>$option): ?>
            <option value="<?= htmlspecialchars($key, ENT_QUOTES); ?>"><?= htmlspecialchars($option, ENT_QUOTES); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label>Summary <abbr title="required">*</abbr></label>
        <input type="text" name="summary" value="<?= htmlspecialchars((isset($_POST['summary']) ? $_POST['summary'] : ''), ENT_QUOTES); ?>" size="60" placeholder="Enter a one-liner description" required>
    </div>
    <div>
        <label>Full description <abbr title="required">*</abbr></label>
        <textarea name="desc" cols="60" rows="3" required><?= htmlspecialchars((isset($_POST['desc']) ? $_POST['desc'] : ''), ENT_QUOTES); ?></textarea>
    </div>
    <div>
        <label>Additional project homepage</label>
        <input type="text" name="homepage" value="<?= htmlspecialchars((isset($_POST['homepage']) ? $_POST['homepage'] : ''), ENT_QUOTES); ?>" size="40" maxlength="255" placeholder="https://example.com">
    </div>
    <div>
        <label>Browse Source URL</label>
        <input type="text" name="cvs_link" value="<?= htmlspecialchars((isset($_POST['cvs_link']) ? $_POST['cvs_link'] : ''), ENT_QUOTES); ?>" size="40" maxlength="255" placeholder="https://git.php.net/?p=pecl/php/operator.git">
    </div>
    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit Request">
    </div>

    <script>
        document.forms[1].name.focus();
    </script>
</form>
