<?php
$SIDEBAR_DATA='
<h3>Documentation</h3>
<p>
The manual section for PEAR can be found
<a href="http://php.net/manual/en/pear.php">here</a>.</p>

<h3>Tutorials</h3>
<p>
A list with PEAR-related tutorials can be found
<a href="http://php.weblogs.com/php_pear_tutorials">
here</a>.
</p>
';

response_header("Support");
?>

<h2>Support</h2>
<h2>Mailing Lists</h2>

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
				"This was a request generated from the form at http://pear.php.net/support.php.", "From: $email\r\n");
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

?>
<p>
There are three PEAR-related mailing lists available. All of them
have archives available, and they are also available as newsgroups
on our <a href="news://news.php.net">news server</a>. The archives
are searchable.
</p>
<?php

  // array of lists (list, name, short desc., moderated, archive, digest, newsgroup)
  $mailing_lists = Array(

    'PEAR mailinglists',
    
    Array (
      'pear-general', 'PEAR general list',
      'A list for users of PEAR',
      false, true, true, "php.pear.general"
    ),

    Array (
      'pear-dev', 'PEAR developers list',
      'A list for developers of PEAR',
      false, true, true, "php.pear.dev"
    ),

    Array (
      'pear-cvs', 'PEAR CVS list',
      'All the commits of the cvs PEAR code repository are posted to this list automatically',
      false, true, true, "php.pear.cvs"
    )
  
  );
?>
<form method="POST" action="http://pear.php.net/support.php">
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
		echo '<td align="left"><b>' . $listinfo[1] . '</b><br><small>'. $listinfo[2] . '</small></td>';
		echo '<td>' . ($listinfo[3] ? 'yes' : 'no') . '</td>';
		echo '<td>' . ($listinfo[4] ? make_link("http://marc.theaimsgroup.com/?l=".$listinfo[0], 'yes') : 'n/a') . '</td>';
		echo '<td>' . ($listinfo[6] ? make_link("news://news.php.net/".$listinfo[6], 'yes') : 'n/a') . '</td>';
		echo '<td><input name="maillist" type="radio" value="' . $listinfo[0] . '"></td>';
		echo '<td>' . ($listinfo[5] ? '<input name="maillist" type="radio" value="'.$listinfo[0].'-digest">' : 'n/a' ) . '</td>';
		echo '</tr>' . "\n";

	}
}

?>
</table>
</p>

<p align="center">
<b>Email:</b>
<input type=text name="email" width=40 value="user@example.com">
<input type=submit name="action" value="Subscribe">
<input type=submit name="action" value="Unsubscribe">
</p>

</form>

<p>
You will be sent a confirmation mail at the address you wish to
be subscribed or unsubscribed, and only added to the list after
following the directions in that mail.
</p>

<p>
There are a variety of commands you can use to modify your subscription.
Either send a message to pear-whatever-help@lists.php.net (as in,
pear-general-help@lists.php.net) or you can view the commands for
ezmlm <a href="http://www.ezmlm.org/ezman-0.32/ezman1.html">here.</a>
</p>

<?php
}
response_footer();
?>
