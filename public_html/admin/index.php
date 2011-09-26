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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require(true);

if (!empty($_GET['phpinfo'])) {
    phpinfo();
    exit();
}

$acreq = isset($_GET['acreq']) ? strip_tags(htmlspecialchars($_GET['acreq'], ENT_QUOTES)) : null;

$SIDEBAR_DATA='
This is the PEAR administration page.<br />
<noscript><p>
<!-- be annoying! -->
<b><blink>You must enable Javascript to use this page!</blink></b>
</p></noscript>
';

response_header("PEAR Administration");

menu_link("Package maintainers", "package-maintainers.php");
menu_link("Manage categories", "category-manager.php");
echo "<hr/>";

// {{{ adding and deleting notes

if (!empty($_REQUEST['cmd'])) {
    if ($_REQUEST['cmd'] == "Add note" && !empty($_REQUEST['note']) && !empty($_REQUEST['key']) && !empty($_REQUEST['id'])) {
        note::add($_REQUEST['key'], $_REQUEST['id'], $_REQUEST['note']);
        unset($_REQUEST['cmd']);

    } elseif ($_REQUEST['cmd'] == "Delete note" && !empty($_REQUEST['id'])) {
		/**
         * Delete note
         */
        note::remove($_REQUEST['id']);
    }
}

// }}}


?>
<?php
// }}}

do {

    // {{{ "approve account request" form
    /* Disable account request approval for now, will be totally removed later due to central storage for the developers details */
    if (0 && !empty($acreq)) {
        $requser =& new PEAR_User($dbh, $acreq);
        if (empty($requser->name)) {
            break;
        }
        list($purpose, $moreinfo) = @unserialize($requser->userinfo);

        $bb = new BorderBox("Account request from " . htmlspecialchars($requser->name, ENT_QUOTES)
		  	. "&lt;" . htmlspecialchars($requser->email, ENT_QUOTES) . "&gt;", "100%", "", 2, true);
        $bb->horizHeadRow("Requested username:", htmlspecialchars($requser->handle, ENT_QUOTES));
        $bb->horizHeadRow("Realname:", htmlspecialchars($requser->name, ENT_QUOTES));
        $bb->horizHeadRow("Email address:", "<a href=\"mailto:" . htmlspecialchars($requser->email, ENT_QUOTES) . "\">" .
		  		htmlspecialchars($requser->email, ENT_QUOTES) . "</a>");
        $bb->horizHeadRow("Purpose of account:", htmlspecialchars($purpose, ENT_QUOTES));
        $bb->horizHeadRow("More information:", htmlspecialchars($moreinfo, ENT_QUOTES));
        $bb->end();

	    print "<br />\n";
	    $bb = new BorderBox("Notes for user " . htmlspecialchars($requser->handle, ENT_QUOTES));
	    $notes = $dbh->getAssoc("SELECT id,nby,UNIX_TIMESTAMP(ntime) AS ntime,note FROM notes ".
	                "WHERE uid = ? ORDER BY ntime", true,
	                array($requser->handle));
	    $i = "      ";
	    if (is_array($notes) && sizeof($notes) > 0) {
	        print "$i<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
	        foreach ($notes as $nid => $data) {
	            list($nby, $ntime, $note) = $data;
	            print "$i <tr>\n";
	            print "$i  <td>\n";
	            print "$i   <b>$nby " . date('H:i jS F Y', $ntime) . ":</b>";
	            if ($nby == $auth_user->handle) {
	                $url = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "?acreq=$acreq&cmd=Delete+note&id=$nid";
	                $msg = "Are you sure you want to delete this note?";
	                print "[<a href=\"javascript:confirmed_goto('$url', '$msg')\">delete your note</a>]";
	            }
	            print "<br />\n";
	            print "$i   ".htmlspecialchars($note, ENT_QUOTES)."\n";
	            print "$i  </td>\n";
	            print "$i </tr>\n";
	            print "$i <tr><td>&nbsp;</td></tr>\n";
	        }
	        print "$i</table>\n";
	    } else {
	        print "No notes.";
	    }
	    print "$i<form action=\"" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "\" method=\"POST\">\n";
	    print "$i<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
	    print "$i <tr>\n";
	    print "$i  <td>\n";
	    print "$i   To add a note, enter it here:<br />\n";
	    print "$i    <textarea rows=\"3\" cols=\"55\" name=\"note\"></textarea><br />\n";
	    print "$i   <input type=\"submit\" value=\"Add note\" name=\"cmd\" />\n";
	    print "$i   <input type=\"hidden\" name=\"key\" value=\"uid\" />\n";
	    print "$i   <input type=\"hidden\" name=\"id\" value=\"$requser->handle\" />\n";
	    print "$i   <input type=\"hidden\" name=\"acreq\" value=\"$acreq\" />\n";
	    print "$i  </td>\n";
	    print "$i </tr>\n";
	    print "$i</table>\n";
	    print "$i</form>\n";
	
	    $bb->end();
?>

<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>" method="POST" name="account_form">
<input type="hidden" name="cmd" value="" />
<input type="hidden" name="uid" value="<?php echo $requser->handle; ?>" />
<table cellpadding="3" cellspacing="0" border="0" width="90%">
 <tr>
  <td align="center"><input type="button" value="Open Account" onclick="confirmed_submit(this, 'open this account')" /></td>
  <td align="center"><input type="button" value="Reject Request" onclick="confirmed_submit(this, 'reject this request', this.form.reason, 'You must give a reason for rejecting the request.')" /></td>
  <td align="center"><input type="button" value="Delete Request" onclick="confirmed_submit(this, 'delete this request')" /></td>
 </tr>
 <tr>
  <td colspan="3">
   If dismissing an account request, enter the reason here
   (will be emailed to <?php echo $requser->email; ?>):<br />
   <textarea rows="3" cols="60" name="reason"></textarea><br />

    <select onchange="return updateRejectReason(this)">
   		<option>Select reason...</option>
   		<option value="You don't need a PECL account to use PECL or PECL packages.">You don't need a PECL account to use PECL or PECL packages.</option>
		<option value="Please propose all new packages to the mailing list pecl-dev@lists.php.net first.">Please propose all new packages to the mailing list pecl-dev@lists.php.net first.</option>
		<option value="Please send all bug fixes to the mailing list pecl-dev@lists.php.net and post a bug at the pecl.php.net package homepage.">Please send all bug fixes to the mailing list pecl-dev@lists.php.net.</option>
		<option value="Please supply valid credentials, including your full name and a descriptive reason for an account.">Please supply valid credentials, including your full name and a descriptive reason for an account.</option>
   </select>

  </td>
</table>
</form>

<?php
    // }}}
    // {{{ admin menu
    } 
    // }}}

} while (false);

response_footer();
?>
