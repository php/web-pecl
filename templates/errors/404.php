<?php $this->extend('layout.php', ['title' => 'Error 404 not found']) ?>

<?php $this->start('content') ?>

<h1>Error 404 not found</h1>

<p>The requested page <i><?= $this->e($_SERVER['REQUEST_URI']) ?></i> was not
found on this server.</p>

<?php if (is_array($packages) && count($packages) > 0): ?>
    Searching the current list of packages for
    <i><?= $this->e(basename($_SERVER['REQUEST_URI'])) ?></i> included the
    following results:

    <ul>
    <?php foreach($packages as $package): ?>
        <li>
            <a href="/packages/<?= $this->e($package['name']) ?>"><?= $this->e($package['name']) ?></a><br>
            <i><?= $this->e($package['summary']) ?></i><br><br>
        </li>
    <?php endforeach ?>
    </ul>

    <?php if($showSearchLink): ?>
        <p style="text-align: center">
            <a href="/package-search.php?pkg_name=<?= $this->e(basename($_SERVER['REQUEST_URI'])) ?>&amp;bool=AND&amp;submit=Search">View full search results...</a>
        </p>
    <?php endif ?>
<?php endif ?>

<p>If you think that this error message is caused by an error in the
configuration of the server, please contact
<a href="mailto:pecl-dev@lists.php.net">pecl-dev@lists.php.net</a>.

<?php $this->end('content') ?>
