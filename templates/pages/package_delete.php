<?php $this->extend('layout.php', ['title' => 'Delete package']) ?>

<?php $this->start('content') ?>

<h1>Delete package</h1>

<?php if (!isset($_POST['confirm'])): ?>

    <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
        <tr>
            <td bgcolor="#000000">
                <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                    <tr style="background-color: #CCCCCC;">
                        <th>Confirmation</th>
                    </tr>
                    <tr bgcolor="#ffffff">
                        <td>

        <form action="/package-delete.php?id=<?= $this->noHtml($_GET['id']) ?>" method="post">
            Are you sure that you want to delete the package?<br><br>
            <input type="submit" name="confirm" value="yes">
            &nbsp;
            <input type="submit" name="confirm" value="no">
            <br><br><font color="#ff0000"><b>Warning:</b> Deleting
            the package will remove all package information and all
            releases!</font>
        </form>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

<?php elseif ('yes' === $_POST['confirm']): ?>

    <pre><?= $content ?></pre>

    <p>Package <?= $this->e($packageName) ?> has been deleted.</p>

<?php elseif ('no' === $_POST['confirm']): ?>

    <p>The package has not been deleted.</p>
    <p>Go back to the <a href="/package/<?= $this->noHtml($packageName) ?>">package details</a>.</p>

<?php endif ?>

<?php $this->end('content') ?>
