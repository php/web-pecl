<?php $this->extend('layout.php', ['title' => 'News']) ?>

<?php $this->start('content') ?>

<h1>PECL news</h1>

<h2><a name="recent_releases"></a>Recent Releases</h2>

<ul>
    <?php foreach ($recent as $release): ?>
        <li>
            <a href="/package/<?= $this->e($release['name']) ?>/">
                <?= $this->e($release['name']) ?> <?= $this->e($release['version']) ?> (<?= $this->e($release['state']) ?>)
            </a>

            <i><?= $this->formatDateToUtc($release['releasedate'], 'Y-m-d') ?></i><br>

            <?= $this->nl2br($this->noHtml(substr($release['releasenotes'], 0, 400))) ?>

            <?php if (strlen($release['releasenotes']) > 400): ?>
                <a href="/package/<?= $this->e($release['name']) ?>/<?= $this->e($release['version']) ?>">...</a>
            <?php endif ?>
        </li>
    <?php endforeach ?>
</ul>

<a href="/feeds/">Syndicate this</a>

<h2><a name="2003"></a>Year 2003</h2>

<ul>
    <li><a href="https://news.php.net/article.php?group=php.pecl.dev&article=5">Call for PHP Extension authors</a> (September)</li>
</ul>

<?php $this->end('content') ?>
