<?php $this->extend('layout.php', ['title' => 'Support']) ?>

<?php $this->start('content') ?>

<h2>Support</h2>

<b>Table of Contents</b>

<ul>
    <li><a href="#lists">Mailing Lists</a></li>
    <li><a href="#subscribe">Subscribing and Unsubscribing</a></li>
    <li><a href="#resources">Resources</a></li>
    <li><a href="#icons">PECL Icons</a></li>
</ul>

<a name="lists"></a><h3>Mailing Lists</h3>

<p>
There are <?= count($lists) ?> PECL-related mailing lists available. Both of
them have archives available, and they are also available as newsgroups on our
<a href="news://news.php.net">news server</a>. The archives are searchable.
</p>

<table cellpadding="5" cellspacing="1">
    <tr bgcolor="#cccccc">
        <th>PECL mailinglists</th>
        <th>Moderated</th>
        <th>Archive</th>
        <th>Newsgroup</th>
        <th>Normal</th>
        <th>Digest</th>
    </tr>

    <?php foreach ($lists as $list): ?>
        <tr align="center" bgcolor="#e0e0e0">
            <td align="left">
                <b><?= $this->e($list['title']) ?></b><br>
                <small><?= $this->e($list['description']) ?></small>
            </td>
            <td><?= $this->e($list['moderated']) ?></td>
            <td>
                <?php if($list['archive']): ?>
                    <a href="http://marc.info/?l=<?= $this->noHtml($list['handle']) ?>">yes</a>
                <?php else: ?>
                    n/a
                <?php endif ?>
            </td>
            <td>
                <a href="news://news.php.net/<?= $this->noHtml($list['newsgroup']) ?>">yes</a>
                <a href="http://news.php.net/group.php?group=<?= $this->noHtml($list['newsgroup']) ?>">http</a>
            </td>
            <td><?= $this->e($list['handle']) ?></td>
            <td><?= $this->e($list['digest']) ?></td>
        </tr>
    <?php endforeach ?>
</table>

<a name="subscribe"></a><h3>Subscribing and Unsubscribing</h3>

<p>
There are a variety of commands you can use to modify your subscription. Either
send a message to pecl-<tt>whatever</tt>@lists.php.net (as in,
pecl-dev@lists.php.net) or you can view the commands for ezmlm
<a href="http://untroubled.org/ezmlm/ezman/ezman1.html">here</a>.
</p>

<p>
For example, to subscribe to pecl-dev, send an email to
pecl-dev-subscribe@lists.php.net and you will be sent a confirmation mail that
explains how to proceed with the subscription process. And to instead receive
digested (daily) pecl-dev email, use pecl-dev-digest-subscribe@lists.php.net.
Similarly, use unsubscribe instead of subscribe to do the exact opposite.
</p>

<p>
If you have questions concerning this website, you can contact
<a href="mailto:php-webmaster@lists.php.net">php-webmaster@lists.php.net</a>.
</p>

<p>
Of course don't forget to visit the <i>#php.pecl</i> IRC channel at the
<a href="http://www.efnet.org">Eris Free Net</a>.
</p>

<div class="listing">
    <a name="resources"></a><h3>PECL resources</h3>

    <ul>
        <li><a href="https://git.php.net/?p=php-src.git;a=blob_plain;f=CODING_STANDARDS;hb=HEAD">PECL/PHP Coding Standards</a></li>
        <li><a href="https://wiki.php.net/internals/review_comments">Common issues in the proposed pecl packages</a></li>
        <li><a href="https://php.net/internals2">PHP Internals Documentation</a></li>
        <li><a href="https://wiki.php.net/internals/references">A list of externals references about maintaining and extending PHP</a></li>
        <li><a href="https://git.php.net/?p=php-src.git;a=blob_plain;f=README.PARAMETER_PARSING_API;hb=HEAD">Parameter Parsing API</a></li>
        <li><a href="https://wiki.php.net/internals/engine">Different information about PHP internals not yet added to the documentation</a></li>
        <li><a href="https://wiki.php.net/internals/windows">Windows specific instructions</a></li>
    </ul>
</div>

<a name="icons"></a><h3>Powered By PECL</h3>

<p>
What programming tool would be complete without a set of icons to put on your
webpage, telling the world what makes your site tick?
</p>

<table cellpadding="5" cellspacing="1">

<?php foreach ($icons as $icon): ?>
    <tr bgcolor="e0e0e0">
        <td>
            <img src="/img/<?= $this->e($icon['file']) ?>" alt="<?= $this->e($icon['description']) ?>">
        </td>
        <td>
            <?= $this->e($icon['description']) ?><br>
            <small>
                <?= $this->e($icon['dimensions']) ?><br>
                <?= $this->e($icon['size']) ?><br>
            </small>
        </td>
    </tr>
<?php endforeach ?>

</table>

<p><b>Note:</b> Please do not just include these icons directly but download
them and save them locally in order to keep HTTP traffic low.</p>

<?php $this->end('content') ?>
