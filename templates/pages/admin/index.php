<?php $this->extend('layout.php', ['title' => 'Administration']) ?>

<?php $this->start('content') ?>

<noscript><p><b>You must enable JavaScript to use this page!</b></p></noscript>

<h1>PECL Administration</h1>

<p>This is the PECL administration page.</p>
<a href="/admin/package-maintainers.php" class="item">Package maintainers</a>
<br>
<a href="/admin/category-manager.php" class="item">Manage categories</a>
<hr>

<?= $content ?>

<?php if ($display && !empty($acreq)): ?>
    <table cellpadding="0" cellspacing="1" style="width: 100%; border: 0px;">
        <tr>
            <td bgcolor="#000000">
                <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                    <tr style="background-color: #CCCCCC;">
                        <th colspan="2">Account request from <?= $this->e($requser->get('name'))
              . "&lt;" . $this->e($requser->get('email')) . "&gt;" ?></th>
                        </tr>

                        <tr>
                            <th valign="top" bgcolor="#cccccc">Requested username:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= $this->e($requser->get('handle')) ?>
                            </td>
                        </tr>

                        <tr>
                            <th valign="top" bgcolor="#cccccc">Realname:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= $this->e($requser->get('name')) ?>
                            </td>
                        </tr>

                        <tr>
                            <th valign="top" bgcolor="#cccccc">Email address:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <a href="mailto:<?= $this->e($requser->get('email')) ?>">
                                    <?= $this->e($requser->get('email')) ?>
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <th valign="top" bgcolor="#cccccc">Purpose of account:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= nl2br($this->e($purpose)) ?>
                            </td>
                        </tr>

                        <tr>
                            <th valign="top" bgcolor="#cccccc">More information:</th>
                            <td valign="top" bgcolor="#e8e8e8">
                                <?= $this->e($moreinfo) ?>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>

        <br>

        <table cellpadding="0" cellspacing="1" style="width: 100%; border: 0px;">
            <tr>
                <td bgcolor="#000000">
                    <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                        <tr style="background-color: #CCCCCC;">
                            <th>Notes for user <?= $this->e($requser->handle) ?></th>
                        </tr>
                        <tr>
                            <td style="background: #ffffff">

        <?php if (is_array($notes) && count($notes) > 0): ?>
            <table cellpadding="2" cellspacing="0" border="0">

            <?php foreach ($notes as $data): ?>
                <tr>
                <td>
                <b><?= $data['nby'].' '.$data['ntime'] ?>:</b>

                <?php if ($data['nby'] == $authUser->handle): ?>
                    <?php $url = $this->e($_SERVER['PHP_SELF']).'?acreq='.$acreq.'&cmd=Delete+note&id='.$data['id'] ?>

                    [<a href="javascript:confirmed_goto('<?= $url ?>', 'Are you sure you want to delete this note?')">delete your note</a>]
                <?php endif ?>

                <br>
                <?= $this->e($data['note']) ?>
                </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
            <?php endforeach ?>

            </table>
        <?php else: ?>
            No notes.
        <?php endif ?>

        <form action="<?= $this->e($_SERVER['PHP_SELF']) ?>" method="POST">
        <table cellpadding="2" cellspacing="0" border="0">
        <tr>
        <td>
        To add a note, enter it here:<br>
        <textarea rows="3" cols="55" name="note"></textarea><br>
        <input type="submit" value="Add note\" name="cmd">
        <input type="hidden" name="key" value="uid">
        <input type="hidden" name="id" value="<?= $requser->handle ?>">
        <input type="hidden" name="acreq" value="<?= $acreq ?>">
        </td>
        </tr>
        </table>
        </form>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

<form action="<?= $this->e($_SERVER['PHP_SELF']) ?>" method="post" name="account_form">
<input type="hidden" name="cmd" value="">
<input type="hidden" name="uid" value="<?= $requser->handle ?>">
<table cellpadding="3" cellspacing="0" border="0" width="90%">
 <tr>
  <td align="center"><input type="button" value="Open Account" onclick="confirmed_submit(this, 'open this account')"></td>
  <td align="center"><input type="button" value="Reject Request" onclick="confirmed_submit(this, 'reject this request', this.form.reason, 'You must give a reason for rejecting the request.')"></td>
  <td align="center"><input type="button" value="Delete Request" onclick="confirmed_submit(this, 'delete this request')"></td>
 </tr>
 <tr>
  <td colspan="3">
   If dismissing an account request, enter the reason here
   (will be emailed to <?php echo $requser->get('email'); ?>):<br>
   <textarea rows="3" cols="60" name="reason"></textarea><br>

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

    <?php else: ?>
        <script>
            // This code is *nasty* (nastyCode™)
            function highlightAccountRow(spanObj)
            {
                return true;
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
        </script>
        <form action="<?= $this->e($_SERVER['PHP_SELF']) ?>" name="mass_reject_form" method="post">
        <input type="hidden" value="" name="cmd">
        <table cellpadding="0" cellspacing="1" style="width: 100%; border: 0px;">
            <tr>
                <td bgcolor="#000000">
                    <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                        <tr style="background-color: #CCCCCC;">
                            <th colspan="7">Account Requests</th>
                        </tr>

        <?php if (is_array($requests) && count($requests) > 0): ?>
                <tr>
                    <th valign="top" bgcolor="#ffffff">
                        <a href="#" onclick="toggleSelectAll(this)">&#9660;</a>
                    </th>
                    <th valign="top" bgcolor="#ffffff">
                        Name
                    </th>
                    <th valign="top" bgcolor="#ffffff">
                        Handle
                    </th>
                    <th valign="top" bgcolor="#ffffff">
                        Account Purpose
                    </th>
                    <th valign="top" bgcolor="#ffffff">
                        Status
                    </th>
                    <th valign="top" bgcolor="#ffffff">
                        Created at
                    </th>
                    <th valign="top" bgcolor="#ffffff">
                        &nbsp;
                    </th>
                </tr>
            <?php foreach ($requests as $data):
                $userinfo = $data['userinfo'];

                // Grab userinfo/request purpose
                if (@unserialize($userinfo)) {
                    $userinfo = @unserialize($userinfo);
                    $account_purpose = $userinfo[0];
                } else {
                    $account_purpose = $userinfo;
                }

                $rejected = (preg_match("/^Account rejected:/", $data['note']));
                if ($rejected) {
                    continue;
                }
                ?>

                <tr>
                    <td valign="top" bgcolor="#ffffff">
                        <input type="checkbox" value="<?= $data['handle'] ?>" name="uid[]" onmousedown="highlightAccountRow(this)"></td>
                    <td valign="top" bgcolor="#ffffff">
                        <span style="cursor: hand" onmousedown="highlightAccountRow(this)"><?= $data['name'] ?></span>
                    </td>
                    <td valign="top" bgcolor="#ffffff">
                        <span style="cursor: hand" onmousedown="highlightAccountRow(this)"><?= $data['handle'] ?></span>
                    </td>
                    <td valign="top" bgcolor="#ffffff">
                        <span style="cursor: hand" onmousedown="highlightAccountRow(this)"><?= nl2br($account_purpose) ?></span>
                    </td>
                    <td valign="top" bgcolor="#ffffff">
                        <span style="cursor: hand" onmousedown="highlightAccountRow(this)"><?= $rejected ? 'rejected' : '<font color="#c00000"><strong>Outstanding</strong></font>' ?></span>
                    </td>
                    <td valign="top" bgcolor="#ffffff">
                        <span style="cursor: hand" onmousedown="highlightAccountRow(this)"><?= $data['created'] ?></span>
                    </td>
                    <td valign="top" bgcolor="#ffffff">
                        <span style="cursor: hand" onmousedown="highlightAccountRow(this)">
                            <a onmousedown="event.cancelBubble = true" href="<?= $this->e($_SERVER['PHP_SELF']) ?>?acreq=<?= $this->e($data['handle']) ?>">
                                <img src="/img/edit.gif" alt="Edit">
                            </a>
                        </span>
                    </td>
                </tr>
            <?php endforeach ?>

        <?php else: ?>
            <tr><td colspan="7" style="background-color: #ffffff">No account requests.</td></tr>
        <?php endif ?>
                    </table>
                </td>
            </tr>
        </table>
        <br>
        <table align="center">
        <tr>
            <td>
                <select name="reason">
                    <option value="">Select rejection reason...</option>
                    <option value="Account not needed">Account not needed</option>
                </select>
            </td>
            <td><input type="submit" value="Reject selected accounts" onclick="return setCmdInput('reject')"></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" value="Delete selected accounts" onclick="return setCmdInput('delete')"></td>
        </tr>
        </table>

        </form>
<?php endif ?>

<script>
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
</script>

<?php $this->end('content') ?>
