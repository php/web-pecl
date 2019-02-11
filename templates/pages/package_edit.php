<?php $this->extend('layout.php', ['title' => 'Edit package']) ?>

<?php $this->start('content') ?>

<script>
function confirmed_goto(url, message) {
    if (confirm(message)) {
        location = url;
    }
}
</script>

<h1>Edit package</h1>

<p><b><?= $this->e($content) ?></b></p>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th>Edit package information</th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>

                    <form action="<?= $this->e($_SERVER['PHP_SELF']) ?>?id=<?= $this->e((int) $_GET['id']) ?>" method="post">
<table border="0">
<tr>
    <td>Package name:</td>
    <td valign="middle">
        <input type="text" name="name" value="<?= $this->e($row['name']) ?>" size="30">
    </td>
</tr>
<tr>
    <td>License:</td>
    <td valign="middle">
        <input type="text" name="license" value="<?= $this->e($row['license']) ?>" size="30">
    </td>
</tr>
<tr>
    <td valign="top">Summary:</td>
    <td>
        <textarea name="summary" cols="40" rows="3" maxlength="255"><?= $this->e($row['summary']) ?></textarea>
    </td>
</tr>
<tr>
    <td valign="top">Description:</td>
    <td>
        <textarea name="description" cols="40" rows="5"><?= $this->e($row['description']) ?></textarea>
    </td>
</tr>
<tr>
    <td>Category:</td>
    <td>
    <select name="category" size="1">
        <?php foreach ($categories as $cat_row): ?>
        <option value="<?= $this->e($cat_row['id']) ?>" <?= $this->e((int) $row['categoryid'] == $cat_row['id'] ? ' selected' : '') ?>>
            <?= $this->e($cat_row['name']) ?>
        </option>
        <?php endforeach ?>
    </select>
    </td>
</tr>
<tr>
    <td>Homepage:</td>
    <td valign="middle">
    <input type="text" name="homepage" value="<?= $this->e($row['homepage']) ?>" size="30">
    </td>
</tr>
<tr>
    <td>Documentation:</td>
    <td valign="middle">
    <input type="text" name="doc_link" value="<?= $this->e($row['doc_link']) ?>" size="30">
    </td>
</tr>
<tr>
    <td>Web CVS Url:</td>
    <td valign="middle">
    <input type="text" name="cvs_link" value="<?= $this->e($row['cvs_link']) ?>" size="30">
    </td>
</tr>
<tr>
    <td>Bug tracker Url:</td>
    <td valign="middle">
    <input type="text" name="bug_link" value="<?= $this->e($row['bug_link']) ?>" size="30">
    </td>
</tr>
<tr>
    <td>Unmaintained package?</td>
    <td valign="middle">
        <input type="checkbox" name="unmaintained" <?= $row['unmaintained'] == 1 ? 'checked' : ''; ?>>
    </td>
</tr>
<tr>
    <td>Superseded by:</td>
    <td valign="middle">
        <select name="new_package" size="1">
        <option value="" <?= $row['new_package'] == '' ? 'selected' : ''; ?>>Select package</option>
        <?php foreach ($packages as $package): ?>
            <option value="<?= $this->e($package['name']) ?>" <?= $row['new_package'] == $package['name'] ? 'selected' : '';?>>
                <?= $this->e($package['name']) ?>
            </option>
        <?php endforeach ?>
        </select>
    </td>
</tr>
<tr>
    <td>New Home Link (if moved out of PECL):</td>
    <td valign="middle">
    <input type="text" name="new_channel" value="<?= $this->e($row['new_channel']) ?>" size="30">
    </td>
</tr>

<tr>
    <td>&nbsp;</td>
    <td><input type="submit" name="submit" value="Save changes" />&nbsp;
    <input type="reset" name="cancel" value="Cancel" onClick="javascript:window.location.href='/package-info.php?package=<?= $this->e($_GET['id']) ?>'; return false">
    </td>
</tr>
</table>
</form>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br><br>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th>Manage releases</th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>
                    <table border="0">
                        <tr>
                            <th>Version</th>
                            <th>Releasedate</th>
                            <th>Actions</th>
                        </tr>

<?php foreach ($row['releases'] as $version => $item): ?>
    <tr>
        <td><?= $this->e($version) ?></td>
        <td><?= $this->e($item['releasedate']) ?></td>
        <td>

        <a href="javascript:confirmed_goto('<?= $this->e($_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&release='.$item['id'].'&action=release_remove') ?>', 'Are you sure that you want to delete the release?')">
            <img src="/img/delete.gif" alt="Delete">
        </a>

        </td>
    </tr>
<?php endforeach ?>

</table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?php $this->end('content') ?>
