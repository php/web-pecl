<!DOCTYPE html>
<html lang="en">
<head>
    <title>PECL :: <?= isset($title) ? $this->e($title) : 'The PHP Extension Community Library' ?></title>
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="alternate" type="application/rss+xml" title="RSS feed" href="<?= $scheme ?>://<?= $this->e($host) ?>/feeds/latest.rss">
    <link rel="stylesheet" href="/css/style.css">
    <?= $this->block('head') ?>
</head>

<body <?= !empty($onloadInlineJavaScript) ? 'onload="'.$this->e($onloadInlineJavascript).'"' : '' ?>>

<div><a id="TOP"></a></div>

<table class="head" cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td class="head-logo">
            <a href="/"><img src="/img/peclsmall.gif" alt="PECL :: The PHP Extension Community Library" <?= $this->getImageSize('/img/peclsmall.gif'); ?> style="margin: 5px;"></a><br>
        </td>

        <td class="head-menu">

            <?php if (empty($authUser)): ?>
                <a href="/login.php" class="menuBlack">Login</a>
            <?php else: ?>
                <small class="menuWhite">
                Logged in as <?= strtoupper($authUser->handle) ?> (
                <a class="menuWhite" href="/user/<?= $this->e($authUser->handle) ?>">Info</a> |
                <a class="menuWhite" href="/account-edit.php?handle=<?= $this->e($authUser->handle) ?>">Profile</a> |
                <a class="menuWhite" href="https://bugs.php.net/search.php?cmd=display&amp;status=Open&amp;assign=<?= $this->e($authUser->handle) ?>">Bugs</a>
                )
                </small><br>
                <a href="?logout=1" class="menuBlack">Logout</a>
            <?php endif ?>

            &nbsp;|&nbsp;
            <a href="/packages.php" class="menuBlack">Packages</a>
            &nbsp;|&nbsp;
            <a href="/support.php" class="menuBlack">Support</a>
            &nbsp;|&nbsp;
            <a href="/bugs/" class="menuBlack">Bugs</a>

        </td>
    </tr>

    <tr>
        <td class="head-search" colspan="2">
            <form method="post" action="/search.php">
                <p class="head-search"><span class="accesskey">S</span>earch for
                    <input class="small" type="text" name="search_string" value="" size="20" accesskey="s">
                    in the
                    <select name="search_in" class="small">
                        <option value="packages">Packages</option>
                        <option value="site">This site (using Google)</option>
                        <option value="developers">Developers</option>
                        <option value="pecl-dev">Developer mailing list</option>
                        <option value="pecl-cvs">SVN commits mailing list</option>
                    </select>
                    <input type="image" src="/img/small_submit_white.gif" alt="search" style="vertical-align: middle;">&nbsp;<br>
                </p>
            </form>
        </td>
    </tr>
</table>

<table class="middle" cellspacing="0" cellpadding="0">
    <tr>
        <td class="sidebar_left">
            <?php $this->include('menus/main_menu.php') ?>

            <?php $this->include('menus/documentation_menu.php') ?>

            <?php $this->include('menus/downloads_menu.php') ?>

            <?php if ($auth->isLoggedIn()): ?>
                <?php $this->include('menus/developers_menu.php') ?>
            <?php endif ?>

            <?php if ($auth->isLoggedIn() && $authUser->isAdmin()): ?>
                <?php $this->include('menus/admin_menu.php') ?>
            <?php endif ?>
        </td>

        <td class="content">
            <?= $this->block('content') ?>
        </td>

        <?php if (isset($sidebar)): ?>
            <td class="sidebar_right">
                <?= $sidebar ?>
            </td>
        <?php endif ?>

    </tr>
</table>

<table class="foot" cellspacing="0" cellpadding="0">
    <tr>
        <td class="foot-bar" colspan="2">
            <a href="/about/privacy.php" class="menuBlack">PRIVACY POLICY</a>
            &nbsp;|&nbsp;
            <a href="/credits.php" class="menuBlack">CREDITS</a>
            <br>
        </td>
    </tr>

    <tr>
        <td class="foot-copy">
            <small>
                <a href="/copyright.php">Copyright &copy; 2001-<?= date('Y') ?> The PHP Group</a><br>
                All rights reserved.<br>
            </small>
        </td>
        <td class="foot-source">
            <small>
                Last updated: <?= $lastUpdated ?><br>
                Bandwidth and hardware provided by: <a href="https://www.pair.com/">pair Networks</a>
            </small>
        </td>
    </tr>
</table>

</body>
</html>
