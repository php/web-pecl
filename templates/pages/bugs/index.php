<?php $this->extend('layout.php', ['title' => 'Bugs']) ?>

<?php $this->start('content') ?>

<h1>PECL Bug Tracking System</h1>

<p>
If you need support or you don't really know if the problem you found is a bug,
please use our <a href="/support.php">support channels</a>.
</p>

<p>
Before submitting a bug, please make sure nobody has already reported it by
<a href="https://bugs.php.net/search.php">searching the existing bugs</a>.
Also, read the tips on how to
<a href="https://bugs.php.net/how-to-report.php">report a bug that someone will
want to help fix</a>.
</p>

<p>Now that you are ready to proceed:</p>

<ul>
    <li>
        If you want to report a bug for a <strong>specific package</strong>,
        please go to the package home page using the
        <a href="/packages.php">browse packages</a> tool
        or the <a href="/package-search.php">package search</a> system.
    </li>

    <li>
        If the bug you found does not relate to a PECL package, one of the
        following categories should be appropriate:

        <ul>
            <li>
                <a href="https://bugs.php.net/report.php?package=Website%20problem">
                    <strong>Website problem</strong>
                </a>
            </li>
            <li>
                <a href="https://bugs.php.net/report.php?package=Documentation%20problem">
                    <strong>Documentation problem</strong>
                </a>
            </li>
        </ul>
  </li>
</ul>

<p>
You may find the <a href="https://bugs.php.net/stats.php">bug statistics</a>
page interesting.
</p>

<?php $this->end('content') ?>
