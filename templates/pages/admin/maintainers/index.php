<?php $this->extend('layout.php', ['title' => 'Administration - Package maintainers']) ?>

<?php $this->start('content') ?>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th>Select package</th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>

                        <form action="/admin/package-maintainers.php" method="get" class="pecl-form">
                            <div>
                                <label>Package:</label>
                                <select name="pid" size="1">
                                    <?php foreach ($packages as $key => $option): ?>
                                    <option value="<?= $this->noHtml($key) ?>">
                                        <?= $this->noHtml($option) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label>&nbsp;</label>
                                <input type="submit" name="submit" value="Submit Changes">
                            </div>

                            <input type="hidden" name="_fields" value="pid:submit">
                        </form>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?php $this->end('content') ?>
