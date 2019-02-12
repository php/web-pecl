<?php $this->extend('layout.php', ['title' => 'Administration - Package maintainers']) ?>

<?php $this->start('content') ?>

<table cellpadding="0" cellspacing="1" style="width: 100%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th>Manage maintainers for <?= $this->e($package) ?></th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>
                        <script src="/js/package-maintainers.js"></script>
                        <form onSubmit="beforeSubmit()" name="form" method="get" action="<?= $this->e($self) ?>">
                            <input type="hidden" name="update" value="yes">
                            <input type="hidden" name="pid" value="<?= $this->noHtml($id) ?>">
                            <table border="0" cellpadding="0" cellspacing="4" border="0" width="100%">
                                <tr>
                                    <th>All users:</th>
                                    <th></th>
                                    <th>Package maintainers:</th>
                                </tr>
                                <tr>
                                    <td>
                                        <select onChange="activateAdd();" name="accounts" size="10">

                                        <?php foreach ($users as $user): ?>
                                            <?php if (!empty($user['handle'])): ?>

                                            <option value="<?= $this->e($user['handle']) ?>">
                                                <?= $this->e($user['name']) ?> (<?= $this->e($user['handle']) ?>)</option>
                                            <?php endif ?>
                                        <?php endforeach ?>

                                        </select>
                                    </td>
                                    <td>
                                        <input type="button" onClick="addMaintainer(); return false" name="add" value="Add as">
                                        <select name="role" size="1">
                                            <option value="lead">lead</option>
                                            <option value="developer">developer</option>
                                            <option value="helper">helper</option>
                                        </select>
                                        <br><br>
                                        <input type="button" onClick="removeMaintainer(); return false" name="remove" value="Remove">
                                    </td>
                                    <td>
                                        <select multiple="yes" name="maintainers[]" onChange="activateRemove();" size="10">

                                            <?php foreach ($maintainers as $maintainer): ?>
                                                <option value="<?= $this->e($maintainer['handle']) ?>||<?= $this->e($maintainer['role']) ?>">
                                                    <?= $this->e($maintainer['name']) ?>
                                                    (<?= $this->e($maintainer['handle']) ?>, <?= $this->e($maintainer['role']) ?>)
                                                </option>
                                            <?php endforeach ?>

                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"><input type="submit"></td>
                                </tr>
                            </table>
                        </form>

                        <script>
                            document.form.remove.disabled = true;
                            document.form.add.disabled = true;
                            document.form.role.disabled = true;
                        </script>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php $this->end('content') ?>
