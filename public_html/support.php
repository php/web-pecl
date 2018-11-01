<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

$SIDEBAR_DATA='';

response_header("Support");
?>

<h2>Support</h2>

<b>Table of Contents</b>
<ul>
  <li><a href="#lists">Mailing Lists</a></li>
  <li><a href="#subscribe">Subscribing and Unsubscribing</a></li>
  <li><a href="#resources">Resources</a></li>
  <li><a href="#icons">PECL Icons</a></li>
</ul>

<a name="lists"></a><h3>Mailing Lists</h3>

<?php

// array of lists (list, name, short desc., moderated, archive, digest, newsgroup)
$mailing_lists = [
    'PECL mailinglists',

    [
      'pecl-dev', 'PECL developers list',
      'A list for developers of PECL',
      false, true, true, "php.pecl.dev"
    ],

    [
      'pecl-cvs', 'PECL SVN list',
      'All the commits of the svn PECL code repository are posted to this list automatically',
      false, true, false, "php.pecl.cvs"
    ],
];
?>
<p>
There are <?php echo count($mailing_lists)-1; ?> PECL-related mailing
lists available. Both of them have archives available, and they are
also available as newsgroups on our
<a href="news://news.php.net">news server</a>. The archives are
searchable.
</p>

<table cellpadding="5" cellspacing="1">
<?php

foreach ($mailing_lists as $listinfo) {
    if (!is_array($listinfo)) {

        echo '<tr bgcolor="#cccccc">';
        echo '<th>' . $listinfo . '</th>';
        echo '<th>Moderated</th>';
        echo '<th>Archive</th>';
        echo '<th>Newsgroup</th>';
        echo '<th>Normal</th>';
        echo '<th>Digest</th>';
        echo '</tr>' . "\n";

    } else {

        echo '<tr align="center" bgcolor="#e0e0e0">';
        echo '<td align="left"><b>' . $listinfo[1] . '</b><br /><small>'. $listinfo[2] . '</small></td>';
        echo '<td>' . ($listinfo[3] ? 'yes' : 'no') . '</td>';
        echo '<td>' . ($listinfo[4] ? '<a href="http://marc.info/?l='.$listinfo[0].'">yes</a>' : 'n/a') . '</td>';
        echo '<td>' . ($listinfo[6] ? ('<a href="news://news.php.net/'.$listinfo[6].'">yes</a> '
                                       .'<a href="http://news.php.net/group.php?group='.$listinfo[6].'">http</a>')
                                       : 'n/a') . '</td>';
        echo '<td>' . $listinfo[0] . '</td>';
        echo '<td>' . ($listinfo[5] ? 'available' : 'n/a' ) . '</td>';
        echo '</tr>' . "\n";
    }
}

?>
</table>
</p>

<a name="subscribe"></a><h3>Subscribing and Unsubscribing</h3>

<p>
There are a variety of commands you can use to modify your subscription.
Either send a message to pecl-<tt>whatever</tt>@lists.php.net (as in,
pecl-dev@lists.php.net) or you can view the commands for
ezmlm <a href="http://untroubled.org/ezmlm/ezman/ezman1.html">here</a>.
</p>

<p>
For example, to subscribe to pecl-dev, send an email to pecl-dev-subscribe@lists.php.net and
you will be sent a confirmation mail that explains how to proceed with the subscription
process.  And to instead receive digested (daily) pecl-dev email, use pecl-dev-digest-subscribe@lists.php.net.
Similarly, use unsubscribe instead of subscribe to do the exact opposite.
</p>

<p>
If you have questions concerning this website, you can contact
<a href="mailto:php-webmaster@lists.php.net">php-webmaster@lists.php.net</a>.
</p>

<p>
Of course don't forget to visit the <i>#php.pecl</i> IRC channel at the <a href="http://www.efnet.org">
Eris Free Net</a>.
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
    What programming tool would be complete without a set of
    icons to put on your webpage, telling the world what makes
    your site tick?
</p>

<?php

$icons = [
    'pecl-power.gif' => 'Powered by PECL, GIF format',
    'pecl-power.png' => 'Powered by PECL, PNG format',
    'pecl-icon.gif'  => '32x32 PECL icon, GIF format',
    'pecl-icon.png'  => '32x32 PECL icon, PNG format',
];

echo '<table cellpadding="5" cellspacing="1">';

foreach ($icons as $file => $desc) {
    echo '<tr bgcolor="e0e0e0">';
    echo '<td>' . make_image($file,$desc) . '<br></td>';
    echo '<td>' . $desc . '<br><small>';
    $size = @getimagesize($_SERVER['DOCUMENT_ROOT'].'/gifs/'.$file);
    if ($size) {
        echo $size[0] . ' x ' . $size[1] . ' pixels<br>';
    }
    $size = @filesize($_SERVER['DOCUMENT_ROOT'].'/gifs/'.$file);
    if ($size) {
        echo $size . ' bytes<br>';
    }
    echo '</small>';
    echo '</td></tr>';
}

echo '</table>';

echo '<p><b>Note:</b> Please do not just include these icons directly but
        download them and save them locally in order to keep HTTP traffic
        low.</p>';

response_footer();
