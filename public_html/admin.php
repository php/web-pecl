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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require(true);

if (isset($_GET['phpinfo'])) {
    print_image("box-0.gif");
    print_link($_SERVER['PHP_SELF'], "Back to administration page");
    phpinfo();
    exit();
}

$SIDEBAR_DATA='
This is the PEAR administration page.<br />
<noscript><p>
<!-- be annoying! -->
<b><blink>You must enable Javascript to use this page!</blink></b>
</p></noscript>
';

response_header("PEAR Administration");

// {{{ adding and deleting notes

if (@$cmd == "Add note" && !empty($note) && !empty($key) && !empty($id)) {
    note::add($key, $id, $note);
    unset($cmd);
}

elseif (@$cmd == "Delete note" && !empty($id)) {
    note::remove($id);
}

// }}}

// {{{ open account

elseif (@$cmd == "Open Account" && !empty($uid)) {
    // another hack to remove the temporary "purpose" field
    // from the user's "userinfo"
    if (user::activate($uid)) {
        print "<p>Opened account $uid...</p>\n";
    }
}

// }}}
// {{{ reject account request

elseif (@$cmd == "Reject Request" && !empty($uid)) {
    if (user::rejectRequest($uid, $reason)) {
        print "<p>Rejected account request for $uid...</p>\n";
    }
}

// }}}
// {{{ delete account request

elseif (@$cmd == "Delete Request" && !empty($uid)) {
    if (user::remove($uid)) {
        print "<p>Deleted account request for \"$uid\"...</p>";
    }
}

// }}}

// {{{ javascript functions

?>
<script language="javascript">
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
    $notes = $dbh->getAssoc("SELECT id,nby,ntime,note FROM notes ".
                "WHERE uid = ? ORDER BY ntime", true,
                array($requser->handle));
    $i = "      ";
    if (is_array($notes) && sizeof($notes) > 0) {
        print "$i<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
        foreach ($notes as $nid => $data) {
            list($nby, $ntime, $note) = $data;
            print "$i <tr>\n";
            print "$i  <td>\n";
            print "$i   <b>$nby $ntime:</b>";
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

        $bb = new BorderBox("Account Requests", "50%", "", 4, true);
        $requests = $dbh->getAssoc("SELECT u.handle,u.name,n.note FROM users u ".
                                   "LEFT JOIN notes n ON n.uid = u.handle ".
                                   "WHERE u.registered = 0");
        if (is_array($requests) && sizeof($requests) > 0) {
            $bb->headRow("Name", "Handle", "Status", "&nbsp;");

            foreach ($requests as $handle => $data) {
                list($name, $note) = $data;
                $rejected = (preg_match("/^Account rejected:/", $note));
                if ($rejected) {
                    continue;
                }
                $bb->plainRow($name,
                              $handle,
                              ($rejected ? "rejected" : "<font color=\"#FF0000\">open</font>"),
                              "<a href=\"" . $_SERVER['PHP_SELF'] . "?acreq=$handle\">" . make_image("edit.gif") . "</a>"
                              );
            }

        } else {
            print "No account requests.";
        }
        $bb->end();

        echo "<br/><br/>";

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
    }

    // }}}

} while (false);


response_footer();
?>
