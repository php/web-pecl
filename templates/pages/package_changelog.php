<?php $this->extend('layout.php', ['title' => $package['name'].' Changelog']) ?>

<?php $this->start('content') ?>

<p><a href="/<?= $this->e($package['name']) ?>">Return</a></p>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th colspan="2">Changelog for <?= $this->e($package['name']) ?></th>
                </tr>

                <?php if (0 === count($package['releases'])): ?>
                    <tr>
                        <td bgcolor="#e8e8e8" colspan="2">
                            There are no releases for <?= $this->e($package['name']) ?> yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <th valign="top" bgcolor="#ffffff">Release</th>
                        <th valign="top" bgcolor="#ffffff">What has changed?</th>
                    </tr>

                    <?php foreach ($package['releases'] as $version => $release): ?>
                        <?php $link = '<a href="package-info.php?package='.$this->noHtml($package['name']).'&amp;version='.urlencode($version).'">'.$this->noHtml($version).'</a>'; ?>

                        <?php if (!empty($_GET['release']) && $version === $_GET['release']): ?>
                            <tr>
                                <th valign="top" bgcolor="#cccccc"><?= $link ?></th>
                                <td valign="top" bgcolor="#e8e8e8">
                                    <?= nl2br($this->e($release['releasenotes'])) ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td valign="top" bgcolor="#ffffff"><?= $link ?></td>
                                <td valign="top" bgcolor="#ffffff">
                                    <?= nl2br($this->e($release['releasenotes'])) ?>
                                </td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach ?>
                <?php endif ?>

            </table>
        </td>
    </tr>
</table>

<p><a href="/<?= $this->e($package['name']) ?>">Return</a></p>

<?php $this->end('content') ?>
