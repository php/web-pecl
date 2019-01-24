<?php $this->extend('layout.php', ['title' => 'Accounts']) ?>

<?php $this->start('content') ?>

<h1>Accounts</h1>

<table border="0" cellspacing="1" cellpadding="5">
    <tr bgcolor="#cccccc">
        <th>

            <?php if ($offset > 0): ?>
                <a href="<?= $this->e($lastLink) ?>">&lt;&lt; Last <?= $this->e($pageSize) ?></a>
            <?php else: ?>
                &nbsp;
            <?php endif ?>

        </th>
        <td colspan="3">
            <table border="0" width="100%">
                <tr>
                    <td>

                        <?php foreach ($letters as $letter): ?>
                            <?php $o = $firstLetterOffsets[$letter]; ?>

                            <?php if ($o >= $offset && $o <= $offset + $pageSize - 1): ?>
                                <b><?= $this->e(strtoupper($letter)) ?></b>
                            <?php else: ?>
                                <a href="<?= $this->e($_SERVER['PHP_SELF']) ?>?letter=<?= $this->e($letter) ?>">
                                    <?= $this->e(strtoupper($letter)) ?>
                                </a>
                            <?php endif ?>
                        <?php endforeach ?>

                    </td>
                    <td rowspan="2" align="right">
                        <form>
                            <input type="button" onclick="u=prompt('Go to account:','');if(u)location.href='<?= $this->e($goUrl) ?>'+u;" value="Go to account.." />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        Displaying accounts
                        <?= $this->e($offset) ?> - <?= min($offset + $pageSize, $usersCount) ?>
                        of <?= $this->e($usersCount) ?>
                    </td>
                </tr>
            </table>
        </td>
        <th>
            <?php if ($offset + $pageSize < $usersCount): ?>
                <a href="<?= $this->e($nextLink) ?>">
                    Next <?= min($pageSize, $usersCount - $offset - $pageSize) ?> &gt;&gt;
                </a>
            <?php else: ?>
                &nbsp;
            <?php endif ?>
        </th>
    </tr>

    <tr bgcolor="#CCCCCC">
        <th>Handle</th>
        <th>Name</th>
        <th>Email</th>
        <th>Homepage</th>
        <th>Commands</th>
    </tr>

    <?php $row = 0; ?>
    <?php foreach ($users as $user): ?>

        <?php if (++$row % 2): ?>
            <tr bgcolor="#e8e8e8">
        <?php else: ?>
            <tr bgcolor="#e0e0e0">
        <?php endif ?>

        <td><a href="/user/<?= $this->e($user['handle']) ?>"><?= $this->e($user['handle']) ?></a></td>
        <td><?= $this->e($user['name']) ?></td>

        <?php if ($user['showemail']): ?>
            <td><a href="mailto:<?= $this->e($user['email']) ?>"><?= $this->e($user['email']) ?></a></td>
        <?php else: ?>
            <td>(not shown)</td>
        <?php endif ?>

        <?php if (!empty($user['homepage'])): ?>
            <td><a href="<?= $this->e($user['homepage']) ?>"><?= $this->e($user['homepage']) ?></a></td>
        <?php else: ?>
            <td>&nbsp;</td>
        <?php endif ?>

        <td><a href="account-edit.php?handle=<?= $this->noHtml($user['handle']) ?>">[Edit]</a></td>
        </tr>
    <?php endforeach ?>

    <tr bgcolor="#cccccc">
        <th>

            <?php if ($offset > 0): ?>
                <a href="<?= $this->e($lastLink) ?>">&lt;&lt; Last <?= $this->e($pageSize) ?></a>
            <?php else: ?>
                &nbsp;
            <?php endif ?>

        </th>
        <td colspan="3">
            <table border="0">
                <tr>
                    <td></td>
                    <td rowspan="2">&nbsp;</td>
                </tr>
            </table>
        </td>
        <th>
            <?php if ($offset + $pageSize < $usersCount): ?>
                <a href="<?= $this->e($nextLink) ?>">
                    Next <?= min($pageSize, $usersCount - $offset - $pageSize) ?> &gt;&gt;
                </a>
            <?php else: ?>
                &nbsp;
            <?php endif ?>
        </th>
    </tr>
</table>

<?php $this->end('content') ?>
