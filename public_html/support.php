<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

$SIDEBAR_DATA='';

response_header("Support");
?>

<h2>Support</h2>

<b>Table of Contents</b>
<ul>
  <li><a href="#lists">Mailing Lists</a></li>
  <li><a href="#tutorials">Tutorials</a></li>
  <li><a href="#resources">Resources</a></li>
  <li><a href="#icons">PECL Icons</a></li>
</ul>

<a name="lists" /><h3>Mailing Lists</h3>

<?php
if (isset($maillist)) {
    # should really grab some email validating routine and use it here.
    if (empty($email) || $email == 'user@example.com') {
        echo "You forgot to specify an email address to be added to the list.  ";
        echo "Go back and try again.";
    } else {
        $request = strtolower($action);
                if ($request != "subscribe" && $request != "unsubscribe") {
                    $request = "subscribe";
                }
        $sub = str_replace("@", "=", $email);
        switch ($maillist) {
            default:
            mail("$maillist-$request-$sub@lists.php.net", "Website Subscription",
                "This was a request generated from the form at http://pecl.php.net/support.php.", "From: $email\r\n");
            break;
        }
?>
<p>
A request has been entered into the mailing list processing queue. You
should receive an email at <?php echo $email;?> shortly describing how to
complete your request.
</p>
<?php
    }
} else {

  // array of lists (list, name, short desc., moderated, archive, digest, newsgroup)
  $mailing_lists = array(

    'PECL mailinglists',

    array (
      'pecl-dev', 'PECL developers list',
      'A list for developers of PECL',
      false, true, true, "php.pecl.dev"
    ),

    array (
      'pecl-cvs', 'PECL CVS list',
      'All the commits of the cvs PECL code repository are posted to this list automatically',
      false, true, false, "php.pecl.cvs"
    ),

  );
?>
<p>
There are <?php echo count($mailing_lists)-1; ?> PECL-related mailing 
lists available. Most of them have archives available, and they are
also available as newsgroups on our 
<a href="news://news.php.net">news server</a>. The archives are
searchable.
</p>

<form method="POST" action="http://pecl.php.net/support.php">
<p>
<table cellpadding="5" cellspacing="1">
<?php

while ( list(, $listinfo) = each($mailing_lists)) {
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
        echo '<td>' . ($listinfo[4] ? make_link("http://marc.theaimsgroup.com/?l=".$listinfo[0], 'yes') : 'n/a') . '</td>';
        echo '<td>' . ($listinfo[6] ? ( make_link("news://news.php.net/".$listinfo[6], 'yes')
                                        . ' ' . make_link("http://news.php.net/group.php?group=" . $listinfo[6], 'http') )
                                       : 'n/a') . '</td>';
        echo '<td><input name="maillist" type="radio" value="' . $listinfo[0] . '" /></td>';
        echo '<td>' . ($listinfo[5] ? '<input name="maillist" type="radio" value="'.$listinfo[0].'-digest" />' : 'n/a' ) . '</td>';
        echo '</tr>' . "\n";

    }
}

?>
</table>
</p>

<p align="center">
<b>Email:</b>
<input type=text name="email" width=40 value="user@example.com" />
<input type=submit name="action" value="Subscribe" />
<input type=submit name="action" value="Unsubscribe" />
</p>

</form>

<p>
You will be sent a confirmation mail at the address you wish to
be subscribed or unsubscribed, and only added to the list after
following the directions in that mail.
</p>

<p>
There are a variety of commands you can use to modify your subscription.
Either send a message to pecl-<tt>whatever</tt>@lists.php.net (as in,
pecl-dev@lists.php.net) or you can view the commands for
ezmlm <a href="http://www.ezmlm.org/ezman-0.32/ezman1.html">here</a>.
</p>

<p>
If you have questions concering this website, you can contact
<a href="mailto:peclweb@php.net">peclweb@php.net</a>.
</p>

<p>
Of course don't forget to visit the <i>#php.pecl</i> IRC channel at the <a href="http://www.efnet.org">
Eris Free Net</a>.
</p>

<div class="listing">

<a name="tutorials" /><h3>PECL Tutorials</h3>
<h4>PECL Tutorials sites</h4>
<ul>
    <li><a href="http://www.devnewz.com/2002/0909.html">
     Developing custom PHP extensions (PHP4)</a> By DevNewz.com</li>

    <li><a href="http://bugs.tutorbuddy.com/phpcpp/phpcpp/">
    Using C++ with PHP (PHP4)</a> By J. Smith</li>

</ul>

<a name="resources" /><h3>PECL resources</h3>
<ul>
    <li><a href="http://cvs.php.net/cvs.php/php-src/CODING_STANDARDS">PECL/PHP Coding Standards</a>
    <li><a href="http://cvs.php.net/cvs.php/ZendEngine2/OBJECTS2_HOWTO">PHP5/ZendEngine 2 Object internals</a>
    <li><a href="http://www.phpconcept.net/articles/article.en.php?id=1">Configure WinCVS for PEAR/PECL</a> by Vincent Blavet</li>
    <li><a href="http://talks.php.net/show/vancouver-ext/">Extention Writing (PHP 4/PHP 5), January 22nd, 2004. Vancouver, Canada</a>
    by Derick Rethans</li>
    <li><a href="http://talks.php.net/show/extending-php-apachecon2003">Writing a PHP Extension (PHP 4), Apachecon 
    November 19, 2003. Las Vegas, NV</a> by Jim Winstead</li>
    <li><a href="http://www.derickrethans.nl/ze-ext/talk.html">Zend and Extending PHP (PHP 4), PHP Forum 2002
    Paris, France - December 10, 2002</a> by Derick Rethans</li>
</ul>

</div>

<a name="icons" /><h3>Powered By PECL</h3>

<p>
    What programming tool would be complete without a set of
    icons to put on your webpage, telling the world what makes
    your site tick?
</p>

<?php

$icons = Array(
	'pecl-power.gif'	=> 'Powered by PECL, GIF format',
	'pecl-power.png'	=> 'Powered by PECL, PNG format',
	'pecl-icon.gif'		=> '32x32 PECL icon, GIF format',
	'pecl-icon.png'		=> '32x32 PECL icon, PNG format',
);

echo '<table cellpadding="5" cellspacing="1">';

foreach ($icons as $file => $desc) {
    echo '<tr bgcolor="e0e0e0">';
    echo '<td>' . make_image($file,$desc) . '<br></td>';
    echo '<td>' . $desc . '<br><small>';
    $size = @getimagesize($HTTP_SERVER_VARS['DOCUMENT_ROOT'].'/gifs/'.$file);
    if ($size) {
        echo $size[0] . ' x ' . $size[1] . ' pixels<br>';
    }
    $size = @filesize($HTTP_SERVER_VARS['DOCUMENT_ROOT'].'/gifs/'.$file);
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
}
response_footer();
?>
