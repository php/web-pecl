<?php $this->extend('layout.php', ['title' => 'Account Request']) ?>

<?php $this->start('content') ?>

<?php if (!isset($_POST['submit']) || count($errors) > 0): ?>

    <h1>Publishing in PECL</h1>

    <?php $this->insert('includes/pie.php'); ?>

    <p>A single suggested reason why you might apply for a PECL account:</p>

    <ul>
        <li>You would like to help maintain a current PECL extension</li>
    </ul>

    <div class="explain">
        <p style="font-weight: normal;"><strong>No accounts will be accepted
            for new PECL packages</strong>. Accounts will only be accepted for
            new maintainers for <em>existing</em> PECL packages.</p>
        <p style="font-weight: normal;">If you are developing a new extension, you cannot add it to PECL, as it is deprecated.</p>
        <p style="font-weight: normal;">Please follow the instructions to add your extension to the &#x1F967; PIE ecosystem: <a href="https://github.com/php/pie/blob/main/docs/extension-maintainers.md">PIE for Extension Maintainers</a>.</p>
    </div>

    <p>
        You do <b>not</b> need an account if you want to download, install and/or
        use PECL packages.
    </p>

    <p>
        Before filling out this form, you must write the public
        <i>pecl-dev@lists.php.net</i> mailing list and:
    </p>

    <ul>
        <li>Introduce yourself</li>
        <li>Indicate the extension you would like to help maintain</li>
    </ul>

    <p>And after submitting the form:</p>
    <ul>
        <li>
            If approved, you will also need to
            <a href="https://php.net/git-php.php">apply for a php.net account</a>
            in order to commit the code to the php.net SVN repository. Select
            'PECL Group' within that form when applying.
        </li>
    </ul>

    <p>
        <strong>Please confirm the reason for this PECL account request:</strong>
    </p>

    <script defer="defer">
        function reasonClick(option)
        {
            if (option == 'pkg') {
                enableForm(true);

                // Lose border
                if (document.getElementById) {
                    document.getElementById('reason_table').style.border = '2px dashed green';
                }
            } else {
                // Gain border
                if (document.getElementById) {
                    document.getElementById('reason_table').style.border = '2px dashed red';
                }

                alert('Reminder: please only request a PECL account if you will maintain an existing PECL extension, and have followed the guidelines above.');
                enableForm(false);
            }
        }

        function enableForm(disabled)
        {
            for (var i=0; i<document.forms['request_form'].elements.length; i++) {
                document.forms['request_form'].elements[i].disabled = !disabled;
            }
        }

        enableForm(false);
    </script>

    <table border="0" style="border: 2px #ff0000 dashed; padding: 0px" id="reason_table">
        <tr>
            <td valign="top"><input type="radio" name="reason" value="pkg" id="reason_pkg" onclick="reasonClick('pkg')"></td>
            <td>
                <label for="reason_pkg">
                    I have already discussed the topic of maintaining an
                    existing PECL extension on the pecl-dev@lists.php.net
                    mailing list, and we determined it's time for me to have a
                    PECL account.
                </label>
            </td>
        </tr>

        <tr>
            <td valign="top"><input type="radio" name="reason" value="new" id="reason_new" onclick="reasonClick('new')"></td>
            <td>
                <label for="reason_new">I would like to submit a new extension to PECL.</label>
            </td>
        </tr>

        <tr>
            <td valign="top"><input type="radio" name="reason" value="other" id="reason_other" onclick="reasonClick('other')"></td>
            <td>
                <label for="reason_other">I desire this PECL account for another reason.</label>
            </td>
        </tr>
    </table>

    <?php if (count($errors) > 0): ?>
        <ul>
        <?php foreach ($errors as $error): ?>
            <li style="color: #cc0000"><?= $this->e($error) ?></li>
        <?php endforeach ?>
        </ul>
    <?php endif ?>

    <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td style="background-color: #cccccc;">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;"><th colspan="2">Request a PECL account</th></tr>
                <tr>
                <td>

                <form action="<?= $this->e($_SERVER['PHP_SELF']) ?>" method="post" name="request_form" class="pecl-form">

                <div>
                    <label>Username:</label>
                    <input type="text" name="handle" value="<?= $this->e($handle) ?>" size="12" required maxlength="<?= $container->get('max_username_length') ?>">
                </div>

                <div>
                    <label>First Name:</label>
                    <input type="text" name="firstname" value="<?= $this->e($firstname) ?>" size="20" required>
                </div>

                <div>
                    <label>Last Name:</label>
                    <input type="text" name="lastname" value="<?= $this->e($lastname) ?>" size="20" required>
                </div>

                <div>
                    <label>Password:</label>
                    <input type="password" name="password" value="" size="10" required> <strong>Again:</strong> <input type="password" name="password2" value="" size="10" required>
                </div>

                <div>
                    <label>Email address:</label>
                    <input type="text" name="email" value="<?= $this->e($email) ?>" size="20" required>
                </div>

                <div>
                    <label>Show email address?</label>
                    <input type="checkbox" name="showemail" <?= $showemail ? 'checked' : '' ?>>
                </div>

                <div>
                    <label>Homepage:</label>
                    <input type="text" name="homepage" value="<?= $this->e($homepage) ?>" size="20">
                </div>

                <div>
                    <label>Purpose of your PECL account<br>(No account is needed for using PECL or PECL packages):</label>
                    <textarea name="purpose" cols="40" rows="5" required><?= $this->e($purpose) ?></textarea>
                </div>

                <div>
                    <label>Sponsoring users<br>(Current php.net users who suggested you request an account and reviewed your extension/patch)</label>
                    <textarea name="sponsor" cols="40" rows="5" required><?= $this->e($sponsor) ?></textarea>
                </div>

                <div>
                    <label>More relevant information<br>about you (optional):</label>
                    <textarea name="moreinfo" cols="40" rows="5"><?= $this->e($moreinfo) ?></textarea>
                </div>

                <div>
                    <label>Which programming language is developed at php.net (spam protection):</label>
                    <input type="text" name="language" value="" size="20" required>
                </div>

                <div>
                    <label>Requested from IP address:</label>
                    <div class="info"><?= $this->e($_SERVER['REMOTE_ADDR']) ?></div>
                </div>

                <div>
                    <label></label>
                    <input type="submit" name="submit" value="Submit">
                </div>
            </table>
        </td>
    </tr>
    </table>
    </form>

    <?php if ($jumpTo): ?>
        <script>
        if (!document.forms[1].<?= $this->e($jumpTo) ?>.disabled) document.forms[1].<?= $this->e($jumpTo) ?>.focus();
        </script>
    <?php endif ?>

<?php else: ?>
    <?php if ($requestError): ?>
        <p><?= $this->e($requestError) ?></p>
    <?php endif ?>

    <?php if ($mailSent): ?>
        <h2>Account Request Submitted</h2>

        <p>Your account request has been submitted, it will be reviewed by a
        human shortly. This may take from two minutes to several days, depending
        on how much time people have. You will get an email when your account is
        open, or if your request was rejected for some reason.</p>
    <?php else: ?>
        <h2>Possible Problem!</h2>

        <p>Your account request has been submitted, but there were problems
        mailing one or more administrators. If you don't hear anything about
        your account in a few days, please drop a mail about it to the
        <i>pecl-dev</i> mailing list.</p>
    <?php endif ?>

    <p>Click the top-left PECL logo to go back to the front page.</p>
<?php endif ?>

<?php $this->end('content') ?>
