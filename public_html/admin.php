<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Richard Heyes <richard@php.net> + others                    |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require(true);

$SIDEBAR_DATA='
This is the PEAR administration page.<br />
<noscript><p>
<!-- be annoying! -->
<b><blink>You must enable Javascript to use this page!</blink></b>
</p></noscript>
';

response_header("PEAR Administration");

// {{{ adding and deleting notes

	if (!empty($_REQUEST['cmd'])) {
		if ($_REQUEST['cmd'] == "Add note" && !empty($_REQUEST['note']) && !empty($_REQUEST['key']) && !empty($_REQUEST['id'])) {
		    note::add($_REQUEST['key'], $_REQUEST['id'], $_REQUEST['note']);
		    unset($_REQUEST['cmd']);

		/**
        * Delete note
        */
		}elseif ($_REQUEST['cmd'] == "Delete note" && !empty($_REQUEST['id'])) {
		    note::remove($_REQUEST['id']);

		/**
        * Open account
        */
		}elseif ($_REQUEST['cmd'] == "Open Account" && !empty($_REQUEST['uid'])) {
		    // another hack to remove the temporary "purpose" field
		    // from the user's "userinfo"
		    if (user::activate($_REQUEST['uid'])) {
		        print "<p>Opened account $uid...</p>\n";
		    }
		
		/**
        * Reject account request
        */
		}elseif ($_REQUEST['cmd'] == "Reject Request" && !empty($_REQUEST['uid'])) {
			if (is_array($_REQUEST['uid'])) {
				foreach ($_REQUEST['uid'] as $uid) {
					user::rejectRequest($uid, $_REQUEST['reason']);
					echo 'Account rejected: ' . $uid . '<br />';
				}

			} elseif (user::rejectRequest($_REQUEST['uid'], $_REQUEST['reason'])) {
		        print "<p>Rejected account request for $uid...</p>\n";
		    }

		/**
        * Delete account request
        */
		}elseif ($_REQUEST['cmd'] == "Delete Request" && !empty($_REQUEST['uid'])) {
			if (is_array($_REQUEST['uid'])) {
				foreach ($_REQUEST['uid'] as $uid) {
					user::remove($uid);
					echo 'Account request deleted: ' . $uid . '<br />';
				}
				
			
			} elseif (user::remove($_REQUEST['uid'])) {
		        print "<p>Deleted account request for \"$uid\"...</p>";
		    }
		}
	}

// }}}

// {{{ javascript functions

?>
<script language="javascript" type="text/javascript">
<!--

function confirmed_goto(url, message) {
    if (confirm(message)) {
        location = url;
    }
}

function confirmed_submit(button, action, required, errormsg) {
    if (required && required.value == '') {
        alert(errormsg);
        return;
    }
    if (confirm('Are you sure you want to ' + action + '?')) {
        button.form.cmd.value = button.value;
        button.form.submit();
    }
}

function updateRejectReason(selectObj) {
    if (selectObj.selectedIndex != 0) {
        document.forms['account_form'].reason.value = selectObj.options[selectObj.selectedIndex].value;
    }
    selectObj.selectedIndex = 0;
}
// -->
</script>
<?php

// }}}

do {

    // {{{ "approve account request" form

    if (!empty($acreq)) {
        $requser =& new PEAR_User($dbh, $acreq);
        if (empty($requser->name)) {
            break;
        }
        list($purpose, $moreinfo) = @unserialize($requser->userinfo);

        $bb = new BorderBox("Account request from $requser->name &lt;$requser->email&gt;", "100%", "", 2, true);
        $bb->horizHeadRow("Requested username:", $requser->handle);
        $bb->horizHeadRow("Realname:", $requser->name);
        $bb->horizHeadRow("Email address:", "<a href=\"mailto:" . $requser->email . "\">" . $requser->email . "</a>");
        $bb->horizHeadRow("MD5-encrypted password:", $requser->password);
        $bb->horizHeadRow("Purpose of account:", $purpose);
        $bb->horizHeadRow("More information:", $moreinfo);
        $bb->end();

	    print "<br />\n";
	    $bb = new BorderBox("Notes for user $requser->handle");
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
	            if ($nby == $_COOKIE['PEAR_USER']) {
	                $url = $_SERVER['PHP_SELF'] . "?acreq=$acreq&cmd=Delete+note&id=$nid";
	                $msg = "Are you sure you want to delete this note?";
	                print "[<a href=\"javascript:confirmed_goto('$url', '$msg')\">delete your note</a>]";
	            }
	            print "<br />\n";
	            print "$i   ".htmlspecialchars($note)."\n";
	            print "$i  </td>\n";
	            print "$i </tr>\n";
	            print "$i <tr><td>&nbsp;</td></tr>\n";
	        }
	        print "$i</table>\n";
	    } else {
	        print "No notes.";
	    }
	    print "$i<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"POST\">\n";
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

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" name="account_form">
<input type="hidden" name="cmd" value="" />
<input type="hidden" name="uid" value="<?= $requser->handle ?>" />
<table cellpadding="3" cellspacing="0" border="0" width="90%">
 <tr>
  <td align="center"><input type="button" value="Open Account" onclick="confirmed_submit(this, 'open this account')" /></td>
  <td align="center"><input type="button" value="Reject Request" onclick="confirmed_submit(this, 'reject this request', this.form.reason, 'You must give a reason for rejecting the request.')" /></td>
  <td align="center"><input type="button" value="Delete Request" onclick="confirmed_submit(this, 'delete this request')" /></td>
 </tr>
 <tr>
  <td colspan="3">
   If dismissing an account request, enter the reason here
   (will be emailed to <?= $requser->email ?>):<br />
   <textarea rows="3" cols="60" name="reason"></textarea><br />

    <select onchange="return updateRejectReason(this)">
   		<option>Select reason...</option>
   		<option value="You don't need a PEAR account to use PEAR or PEAR packages">You don't need a PEAR account to use PEAR or PEAR packages</option>
		<option value="Please propose all new classes to the mailing list pear-dev@lists.php.net first">Please propose all new classes to the mailing list pear-dev@lists.php.net first</option>
		<option value="Please send all bug fixes to the mailing list pear-dev@lists.php.net">Please send all bug fixes to the mailing list pear-dev@lists.php.net</option>
		<option value="Please supply valid credentials, including your full name and a descriptive reason for an account">Please supply valid credentials, including your full name and a descriptive reason for an account</option>
   </select>

  </td>
</table>
</form>

<?php
    // }}}
    // {{{ admin menu
    } else {
		?>
		<script language="JavaScript" type="text/javascript">
        <!--
			/**
            * This code is *nasty* (nastyCode™)
            */

        	function highlightAccountRow(spanObj)
			{
				var highlightColor = '#cfffb7';
				
				if (typeof(arguments[1]) == 'undefined') {
					action = (spanObj.parentNode.parentNode.childNodes[0].style.backgroundColor == highlightColor);
				} else {
					action = !arguments[1];
				}

				if (document.getElementById) {
					for (var i=0; i<spanObj.parentNode.parentNode.childNodes.length; i++) {
						if (action) {
							spanObj.parentNode.parentNode.childNodes[i].style.backgroundColor = '#ffffff';
							spanObj.parentNode.parentNode.childNodes[0].childNodes[0].checked = false;
						} else {
							spanObj.parentNode.parentNode.childNodes[i].style.backgroundColor = highlightColor;
							spanObj.parentNode.parentNode.childNodes[0].childNodes[0].checked = true;
						}
					}
				}
			}
			
			allSelected = false;
			
			function toggleSelectAll(linkElement)
			{
				tableBodyElement = linkElement.parentNode.parentNode.parentNode.parentNode;
				
				for (var i=0; i<tableBodyElement.childNodes.length; i++) {
					if (tableBodyElement.childNodes[i].childNodes[0].childNodes[0].tagName == 'INPUT') {
						highlightAccountRow(tableBodyElement.childNodes[i].childNodes[1].childNodes[0], !allSelected);
					}
				}
				
				allSelected = !allSelected;
			}
			
			function setCmdInput(mode)
			{
				switch (mode) {
					case 'reject':
						if (document.forms['mass_reject_form'].reason.selectedIndex == 0) {
							alert('Please select a reason to reject the accounts!');

						} else if (confirm('Are you sure you want to reject these account requests ?')) {
							document.forms['mass_reject_form'].cmd.value = 'Reject Request';
							return true;
						}
						break;

					case 'delete':
						if (confirm('Are you sure you want to delete these account requests ?')) {
							document.forms['mass_reject_form'].cmd.value = 'Delete Request';
							return true;
						}
						break;
				}
				
				return false;
			}
        //-->
        </script>
		<form action="admin.php" name="mass_reject_form" method="post">
		<input type="hidden" value="" name="cmd"/>
		<?php
        $bb = new BorderBox("Account Requests", "100%", "", 6, true);
        $requests = $dbh->getAssoc("SELECT u.handle,u.name,n.note,u.userinfo FROM users u ".
                                   "LEFT JOIN notes n ON n.uid = u.handle ".
                                   "WHERE u.registered = 0");
        if (is_array($requests) && sizeof($requests) > 0) {
            $bb->headRow("<font face=\"Marlett\"><a href=\"#\" onclick=\"toggleSelectAll(this)\">6</a></font>", "Name", "Handle", "Account Purpose", "Status", "&nbsp;");

            foreach ($requests as $handle => $data) {
                list($name, $note, $userinfo) = $data;

				// Grab userinfo/request purpose
				if (@unserialize($userinfo)) {
					$userinfo = @unserialize($userinfo);
					$account_purpose = $userinfo[0];
				} else {
					$account_purpose = $userinfo;
				}

                $rejected = (preg_match("/^Account rejected:/", $note));
                if ($rejected) {
                    continue;
                }
                $bb->plainRow('<input type="checkbox" value="' . $handle . '" name="uid[]" onmousedown="highlightAccountRow(this)" onclick="return false"/>',
							  sprintf('<span style="cursor: hand" onmousedown="highlightAccountRow(this)">%s</span>', $name),
                              sprintf('<span style="cursor: hand" onmousedown="highlightAccountRow(this)">%s</span>', $handle),
							  sprintf('<span style="cursor: hand" onmousedown="highlightAccountRow(this)">%s</span>', $account_purpose),
                              sprintf('<span style="cursor: hand" onmousedown="highlightAccountRow(this)">%s</span>', ($rejected ? "rejected" : "<font color=\"#c00000\"><strong>Outstanding</strong></font>")),
                              sprintf('<span style="cursor: hand" onmousedown="highlightAccountRow(this)">%s</span>', "<a onmousedown=\"event.cancelBubble = true\" href=\"" . $_SERVER['PHP_SELF'] . "?acreq=$handle\">" . make_image("edit.gif") . "</a>")
                              );
            }

        } else {
            print "No account requests.";
        }
        $bb->end();

		?>
		<br />
		<table align="center">
		<tr>
			<td>
				<select name="reason">
					<option value="">Select rejection reason...</option>
					<option value="Account not needed">Account not needed</option>
				</select>
			</td>
			<td><input type="submit" value="Reject selected accounts" onclick="return setCmdInput('reject')" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Delete selected accounts" onclick="return setCmdInput('delete')" /></td>
		</tr>
		</table>

		</form>
		<br /><br />
		<?php
/*
        $bb = new BorderBox("System information", "50%");

        echo "<ul>\n";
        echo "<li>Uptime: " . uptime() . "</li>\n";
        echo "<li>Disk space: " . round((disk_total_space("/")/(1000*1000*1000)),2) . " GB (available: " . round((diskfreespace("/")/(1000*1000*1000)),2) . " GB)</li>\n";
        echo "<li>" . make_link($_SERVER['PHP_SELF'] . "?phpinfo=1", "Output of phpinfo()") . "</li>\n";
        echo "<li>Server name: " . $_SERVER['SERVER_NAME'] . "</li>\n";
        echo "<li>System date: " . date("Y-m-d H:i:s") . "</li>\n";
        echo "</ul>\n";
        $bb->end();

        echo "<br /><br />\n";
*/
    }

    // }}}

} while (false);


response_footer();
?>
