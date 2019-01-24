<?php $this->extend('layout.php', ['title' => $user['name']]) ?>

<?php $this->start('content') ?>

<h1><?= $this->e($user['name']) ?></h1>

<table border="0" cellspacing="4" cellpadding="0">
    <tr><td valign="top">
        <table cellpadding="0" cellspacing="1" style="width: 100%; border: 0px;">
            <tr>
                <td bgcolor="#000000">
                    <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                        <tr style="background-color: #CCCCCC;">
                            <th colspan="2">Account Details</th>
                        </tr>
                        <tr>
                            <th valign="top" bgcolor="#cccccc">Handle:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= $this->e($user['handle']) ?>
                            </td>
                        </tr>
                        <tr>
                            <th valign="top" bgcolor="#cccccc">Name:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= $this->e($user['name']) ?>
                            </td>
                        </tr>

                        <?php if (1 === $user['showemail']): ?>
                        <tr>
                            <th valign="top" bgcolor="#cccccc">Email:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <a href="/account-mail.php?handle=<?= $this->e($user['handle']) ?>">
                                <?= $this->e(str_replace(["@", "."], [" at ", " dot "], $user['email'])) ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif ?>

                        <?php if (!empty($user['homepage'])): ?>
                        <tr>
                            <th valign="top" bgcolor="#cccccc">Homepage:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <a href="<?= $this->e($user['homepage']) ?>">
                                <?= $this->e($user['homepage']) ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif ?>

                        <?php if (!empty($user['userinfo'])): ?>
                        <tr>
                            <th valign="top" bgcolor="#cccccc">Additional information:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= nl2br($this->e($user['userinfo'])) ?>
                            </td>
                        </tr>
                        <?php endif ?>

                        <tr>
                            <th valign="top" bgcolor="#cccccc">VCS Access:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= nl2br($this->e(implode("\n", $access))) ?>
                            </td>
                        </tr>

                        <?php if (!empty($user['wishlist'])): ?>
                        <tr>
                            <th valign="top" bgcolor="#cccccc">Wishlist:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <a href="/wishlist.php/<?= $this->e($user['handle']) ?>">
                                    Click here to be redirected.
                                </a>
                            </td>
                        </tr>
                        <?php endif ?>

                        <?php if (1 === (int) $user['admin']): ?>
                        <tr>
                            <td bgcolor="#e8e8e8" colspan="2">
                                <?= $this->noHtml($user['name']) ?> is a PECL administrator.
                            </td>
                        </tr>
                        <?php endif ?>

                    </table>
                </td>
            </tr>
        </table>

    </td>
    <td valign="top">
        <table cellpadding="0" cellspacing="1" style="width: 100%; border: 0px;">
            <tr>
                <td bgcolor="#000000">
                    <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                        <tr style="background-color: #CCCCCC;">
                            <th colspan="2">Maintaining These Packages:</th>
                        </tr>

                        <?php if (isset($packages) && count($packages) > 0): ?>
                            <tr>
                                <th valign="top" bgcolor="#ffffff">Package Name</th>
                                <th valign="top" bgcolor="#ffffff">Role</th>
                            </tr>

                            <?php foreach ($packages as $package): ?>
                                <tr>
                                    <td valign="top" bgcolor="#ffffff">
                                        <a href="/package/<?= $this->noHtml($package['name']) ?>">
                                            <?= $this->noHtml($package['name']) ?>
                                        </a>
                                    </td>
                                    <td valign="top" bgcolor="#ffffff">
                                        <?= $this->noHtml($package['role']) ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>

                    </table>
                </td>
            </tr>
        </table>

        <br>

        <table cellpadding="0" cellspacing="1" style="width: 100%; border: 0px;">
            <tr>
                <td bgcolor="#000000">
                    <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                        <tr style="background-color: #CCCCCC;">
                            <th>Notes for user <?= $this->noHtml($user['handle']) ?></th>
                        </tr>
                        <tr bgcolor="#ffffff">
                            <td>

                                <?php if (!empty($notes)): ?>
                                    <table cellpadding="2" cellspacing="0" border="0">
                                    <?php foreach ($notes as $note): ?>
                                        <tr>
                                            <td>
                                                <b><?= $this->e($note['nby'].' '.$note['ntime']) ?>:</b>
                                                <br>
                                                <?= $this->e($note['note']) ?>
                                            </td>
                                        </tr>
                                        <tr><td>&nbsp;</td></tr>
                                    <?php endforeach ?>
                                    </table>
                                <?php else: ?>
                                    No notes.
                                <?php endif ?>

                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <br>
        <a href="/account-edit.php?handle=<?= $this->noHtml($user['handle']) ?>">
            <img src="/img/edit.gif" alt="Edit">
        </a>

    </td></tr>
</table>

<?php $this->end('content') ?>
