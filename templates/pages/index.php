<?php $this->extend('layout.php') ?>

<?php $this->start('content') ?>

<h3>What is PECL?</h3>

<?php $this->insert('includes/pie.php'); ?>

<p>
<acronym title="PHP Extension Community Library">PECL</acronym> is a repository
for PHP Extensions, providing a directory of all known extensions and hosting
facilities for downloading and development of PHP extensions.
</p>

<p>
The packaging and distribution system used by PECL is shared with its sister,
<acronym title="PHP Extension and Application Repository">PEAR</acronym>.
</p>

<h3><a href="/news/">News</a></h3>
<h3>Documentation</h3>

<div class="indent">
    <a href="/doc/index.php" class="item">PECL specific docs</a><br>
    <a href="/support.php" class="item">Mailing Lists &amp; Support Resources</a><br>
</div>

<h3>Downloads</h3>

<div class="indent">
    <a href="/packages.php" class="item">Browse All Packages</a><br>
    <a href="/package-search.php" class="item">Search Packages</a><br>
    <a href="/package-stats.php" class="item">Download Statistics</a><br>
</div>

<?php if (!empty($authUser)): ?>
    <h3>Developers</h3>

    <div class="indent">
        <a href="/release-upload.php" class="item">Upload Release</a><br>
        <a href="/package-new.php" class="item">New Package</a><br>
    </div>

    <?php if ($authUser->isAdmin()): ?>
        <h3>Administrators</h3>

        <div class="indent">
            <a href="/admin" class="item">Overview</a><br>
            <a href="/admin/package-maintainers.php" class="item">Maintainers</a><br>
            <a href="/admin/category-manager.php" class="item">Categories</a><br>
        </div>
    <?php endif ?>
<?php endif ?>

<a href="/account-request.php" class="item">I want to publish my PHP Extension in PECL</a><br>

<?php $this->end('content') ?>

<?php $this->start('sidebar') ?>

<?php if (count($recent) > 0): ?>
    <strong>Recent&nbsp;Releases:</strong>

    <table class="sidebar-releases">
        <?php foreach ($recent as $release): ?>
            <tr>
                <td valign="top">
                    <a href="/package/<?= $this->noHtml($release['name']) ?>/">
                        <?= $this->e($release['name'].' '.$release['version']) ?>
                    </a><br>
                    <i><?= $this->formatDateToUtc($release['releasedate'], 'Y-m-d') ?>:</i>
                    <?= $this->noHtml(substr($release['releasenotes'], 0, 40)) ?><?= strlen($release['releasenotes']) > 40 ? '...' : '' ?>
                </td>
            </tr>
        <?php endforeach ?>

        <tr><td>&nbsp;</td></tr>
        <tr><td align="right"><a href="/feeds/">Syndicate this</a></td></tr>
    </table>
<?php endif ?>

<?php $this->end('sidebar') ?>
