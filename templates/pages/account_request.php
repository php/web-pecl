<?php $this->extend('layout.php', ['title' => 'Account Request']) ?>

<?php $this->start('content') ?>

<?php if (!isset($_POST['submit']) || count($errors) > 0): ?>

    <h1>Publishing in PECL</h1>

    <p>A few reasons why you might apply for a PECL account:</p>

    <ul>
        <li>You have written a PHP extension and would like it listed within the
        PECL directory</li>
        <li>You would like to use php.net for version control and hosting</li>
        <li>You would like to help maintain a current PECL extension</li>
    </ul>

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
        <li>Introduce your new extension or the extension you would like to help
        maintain</li>
        <li>Link to the code, if applicable</li>
    </ul>

    <p>Also, here is a list of suggestions:</p>

    <ul>
        <li>
            We strongly encourage contributors to choose the
            <a href="https://php.net/license/3_01.txt">PHP License 3.01</a>
            for their extensions, in order to avoid possible troubles for
            end-users of the extension. Other solid options are BSD and Apache
            type licenses.
        </li>
        <li>
            We strongly encourage you to use the
            <a href="https://github.com/php/php-src/raw/master/CODING_STANDARDS.md">PHP Coding Standards</a>
            for your code, as it will help the QA team (and others) help maintain
            the extension.
        </li>
        <li>
            We strongly encourage you to commit documentation for the extension,
            as it will make the extension more visible (in the official PHP manual)
            and also teach users how to use it. See the
            <a href="https://wiki.php.net/doc/howto/pecldocs">PECL Docs Howto</a>
            for more information. Submitted documentation will always be under the
            <a href="https://php.net/manual/en/cc.license.php">Creative Commons Attribution License</a>.
        </li>
        <li>
            Note: wrappers for GPL (all versions) or LGPLv3 libraries will not
            be accepted. Wrappers for libraries licensed under LGPLv2 are however
            allowed while being discouraged.
        </li>
        <li>
            Note: Wrappers for libraries with license fees or closed sources
            libraries without licenses fees are allowed.
        </li>
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

                alert('Reminder: please only request a PECL account if you will maintain a PECL extension, and have followed the guidelines above.');
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
                    I have already discussed the topic of maintaining and/or
                    adding a PECL extension on the pecl-dev@lists.php.net mailing
                    list, and we determined it's time for me to have a PECL
                    account.
                </label>
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
