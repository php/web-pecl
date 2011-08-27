<?php /* vim: set noet ts=4 sw=4: : */

/**
 * User interface for viewing and editing bug details
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www. php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  peclweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2004 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/*
 * NOTE: another require exists in the code below, so if changing
 * the include path, make sure to change it too.
 */

/**
 * Obtain common includes
 */
require_once './include/prepend.inc';

/**
 * Get user's CVS password
 */
require_once './include/cvs-auth.inc';

/**
 * Obtain a list of the trusted developers
 */
require_once './include/trusted-devs.inc';


error_reporting(E_ALL ^ E_NOTICE);

if (empty($_REQUEST['id']) || !(int)$_REQUEST['id']) {
    localRedirect('search.php');
    exit;
} else {
    $id = (int)$_REQUEST['id'];
}

if (empty($_REQUEST['edit']) || !(int)$_REQUEST['edit']) {
    $edit = 0;
} else {
    $edit = (int)$_REQUEST['edit'];
}

if (!empty($_POST['pw'])) {
    if (empty($_POST['user'])) {
        $user = '';
    } else {
        $user = rinse($_POST['user']);
    }
    $pw = rinse($_POST['pw']);
} elseif (isset($_COOKIE['PEAR_USER']) &&
          isset($_COOKIE['PEAR_PW']) &&
          $edit == 1) {
    $user = rinse($_COOKIE['PEAR_USER']);
    $pw   = rinse($_COOKIE['PEAR_PW']);
} else {
    $user = '';
    $pw   = '';
}


// fetch info about the bug into $bug
$query = 'SELECT b.id, b.package_name, b.bug_type, b.email,
        b.passwd, b.sdesc, b.ldesc, b.php_version, b.php_os,
        b.status, b.ts1, b.ts2, b.assign, UNIX_TIMESTAMP(b.ts1) AS submitted, 
        UNIX_TIMESTAMP(b.ts2) AS modified,
        COUNT(bug=b.id) AS votes,
        SUM(reproduced) AS reproduced,SUM(tried) AS tried,
        SUM(sameos) AS sameos, SUM(samever) AS samever,
        AVG(score)+3 AS average,STD(score) AS deviation,
        users.showemail, users.handle, p.package_type
        FROM bugdb b
        LEFT JOIN bugdb_votes ON b.id = bug 
        LEFT JOIN users ON users.email = b.email
        LEFT JOIN packages p ON b.package_name = p.name
        WHERE b.id = '.(int)$id.'
        GROUP BY bug';

$bug =& $dbh->getRow($query, array(), DB_FETCHMODE_ASSOC);

if (!$bug) {
    response_header('No Such Bug');
    display_bug_error('No such bug #' . $id);
    response_footer();
    exit;
}

// Redirect to PEAR if it's a PEAR bug
if (!empty($bug['package_type']) && $bug['package_type'] != $site) {
   $site == 'pear' ? $redirect = 'pecl' : $redirect = 'pear';
    localRedirect('http://'.$redirect.'.php.net/bugs/bug.php?id='.$id);
    exit();
}

// Delete comment
if ($edit == 1 && isset($_GET['delete_comment'])) {
    $addon = '';
    if (in_array($user, $trusted_developers) && verify_password($user, $pw)) {
        delete_comment($id, $_GET['delete_comment']);
        $addon = '&thanks=1';
    }
    localRedirect(htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$id&edit=1$addon");

}

// handle any updates, displaying errors if there were any
$errors = array();

if ($_POST['in'] && $edit == 3) {
    // Submission of additional comment by others

    if (!validate_captcha()) {
        $errors[] = 'Incorrect CAPTCHA';
    }

    if (!preg_match("/[.\\w+-]+@[.\\w-]+\\.\\w{2,}/i",
                    $_POST['in']['commentemail'])) {
        $errors[] = "You must provide a valid email address.";
    }

    // Don't allow comments by the original report submitter
    if (rinse($_POST['in']['commentemail']) == $bug['email']) {
        localRedirect(htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$id&edit=2");
        exit();
    }

    /* check that they aren't using a php.net mail address without
      being authenticated (oh, the horror!) */
    if (preg_match('/^(.+)@php\.net/i', rinse($_POST['in']['commentemail']), $m)) {
        if ($user != rinse($m[1]) || !verify_password($user, $pass)) {
            $errors[] = 'You have to be logged in as a developer and be'
                      . ' editing the bug via the "Developer" tab in order'
                      . ' to use your php.net email address.';
        }
    }

    $ncomment = trim($_POST['ncomment']);
    if (!$ncomment) {
        $errors[] = "You must provide a comment.";
    }

    if (!$errors) {
        $query = 'INSERT INTO bugdb_comments' .
                 ' (bug, email, ts, comment, visitor_ip) VALUES (' .
                 " $id," .
                 " '" . escapeSQL($_POST['in']['commentemail']) . "'," .
                 ' NOW(),' .
                 " '" . escapeSQL($ncomment) . "'," .
                 " INET_ATON('" . escapeSQL($_SERVER['REMOTE_ADDR']) . "'))";
        $dbh->query($query);
    }
    $from = rinse($_POST['in']['commentemail']);

} elseif ($_POST['in'] && $edit == 2) {
    // Edits submitted by original reporter

    if (!$bug['passwd'] || $bug['passwd'] != $pw) {
        $errors[] = 'The password you supplied was incorrect.';
    }

    $ncomment = trim($_POST['ncomment']);
    if (!$ncomment) {
        $errors[] = 'You must provide a comment.';
    }

    /* check that they aren't being bad and setting a status they
      aren't allowed to (oh, the horrors.) */
    if ($_POST['in']['status'] != $bug['status'] && $state_types[$_POST['in']['status']] != 2) {
        $errors[] = 'You aren\'t allowed to change a bug to that state.';
    }

    /* check that they aren't changing the mail to a php.net address
      (gosh, somebody might be fooled!) */
    if (preg_match('/^(.+)@php\.net/i', $_POST['in']['email'], $m)) {
        if ($user != $m[1] || !verify_password($user, $pass)) {
            $errors[] = 'You have to be logged in as a developer to use your php.net email address.';
            $errors[] = 'Tip: log in via another browser window then resubmit the form in this window.';
        }
    }

    if (!empty($_POST['in']['email']) &&
        $bug['email'] != $_POST['in']['email'])
    {
        $from = $_POST['in']['email'];
    } else {
        $from = $bug['email'];
    }

    if (!$errors && !($errors = incoming_details_are_valid($_POST['in']))) {
        $query = 'UPDATE bugdb SET' .
                 " sdesc='" . escapeSQL($_POST['in']['sdesc']) . "'," .
                 " status='" . escapeSQL($_POST['in']['status']) . "'," .
                 " package_name='" . escapeSQL($_POST['in']['package_name']) . "'," .
                 " bug_type='" . escapeSQL($_POST['in']['bug_type']) . "'," .
                 " php_version='" . escapeSQL($_POST['in']['php_version']) . "'," .
                 " php_os='" . escapeSQL($_POST['in']['php_os']) . "'," .
                 ' ts2=NOW(), ' .
                 " email='" . escapeSQL($from) . "' WHERE id=$id";
        $dbh->query($query);

        if (!empty($ncomment)) {
            $query = 'INSERT INTO bugdb_comments' .
                     ' (bug, email, ts, comment, visitor_ip) VALUES (' .
                     " $id," .
                     " '" . escapeSQL($from) . "'," .
                     ' NOW(),' .
                     " '" . escapeSQL($ncomment) . "'," .
                     " INET_ATON('" . escapeSQL($_SERVER['REMOTE_ADDR']) . "'))";
            $dbh->query($query);
        }
    }

} elseif ($_POST['in'] && $edit == 1) {
    // Edits submitted by developer

    if (!verify_password($user, $pw)) {
        $errors[] = "You have to login first in order to edit the bug report.";
        $errors[] = 'Tip: log in via another browser window then resubmit the form in this window.';
    }

    if (empty($_POST['ncomment'])) {
        $ncomment = '';
    } else {
        $ncomment = trim($_POST['ncomment']);
    }

    if ((($_POST['in']['status'] == 'Bogus' && $bug['status'] != 'Bogus') ||
          $RESOLVE_REASONS[$_POST['in']['resolve']]['status'] == 'Bogus') &&
        strlen($ncomment) == 0)
    {
        $errors[] = "You must provide a comment when marking a bug 'Bogus'";
    } elseif ((($_POST['in']['status'] == 'Spam' && $bug['status'] != 'Spam') ||
          $RESOLVE_REASONS[$_POST['in']['resolve']]['status'] == 'Spam') &&
        strlen($ncomment) == 0)
    {
        $errors[] = "You must provide a comment when marking a bug 'Spam'";
    } elseif ($_POST['in']['resolve']) {
        if (!$trytoforce &&
            $RESOLVE_REASONS[$_POST['in']['resolve']]['status'] == $bug['status'])
        {
            $errors[] = 'The bug is already marked "'.$bug['status'].'". (Submit again to ignore this.)';
        } elseif (!$errors)  {
            if ($_POST['in']['status'] == $bug['status']) {
                $_POST['in']['status'] = $RESOLVE_REASONS[$_POST['in']['resolve']]['status'];
            }
            require './include/resolve.inc';
            $ncomment = $RESOLVE_REASONS[$_POST['in']['resolve']]['message']
                      . "\n\n$ncomment";
        }
    }

    $query = "SELECT email FROM users WHERE handle = '" . escapeSQL($user) . "'";
    $from =& $dbh->getOne($query);
    if (!$from) {
        $from = $user . '@php.net';
    }

    if (!$errors && !($errors = incoming_details_are_valid($_POST['in']))) {
        $query = 'UPDATE bugdb SET';

        if ($bug['email'] != $_POST['in']['email'] &&
            !empty($_POST['in']['email']))
        {
            $query .=  "email='{$_POST['in']['email']}',";
        }

        if (!empty($_POST['in']['assign']) && $_POST['in']['status'] == 'Open') {
            $status = 'Assigned';
        } elseif (empty($_POST['in']['assign']) && $_POST['in']['status'] == 'Assigned') {
            $status = 'Open';
        } else {
            $status = $_POST['in']['status'];
        }

        $query .= " sdesc='" . escapeSQL($_POST['in']['sdesc']) . "'," .
                  " status='" . escapeSQL($status) . "'," .
                  " package_name='" . escapeSQL($_POST['in']['package_name']) . "'," .
                  " bug_type='" . escapeSQL($_POST['in']['bug_type']) . "'," .
                  " assign='" . escapeSQL($_POST['in']['assign']) . "'," .
                  " php_version='" . escapeSQL($_POST['in']['php_version']) . "'," .
                  " php_os='" . escapeSQL($_POST['in']['php_os']) . "'," .
                  " ts2=NOW() WHERE id=$id";
        $dbh->query($query);

        if (!empty($ncomment)) {
            $query = 'INSERT INTO bugdb_comments' .
                     ' (bug, email, ts, comment, visitor_ip) VALUES (' .
                     " $id," .
                     " '" . escapeSQL($from) . "'," .
                     ' NOW(),' .
                     " '" . escapeSQL($ncomment) . "'," .
                     " INET_ATON('" . escapeSQL($_SERVER['REMOTE_ADDR']) . "'))";
            $dbh->query($query);
        }
    }

} elseif ($_POST['in']) {
    $errors[] = 'Invalid edit mode.';
    $ncomment = '';
} else {
    $ncomment = '';
}

if ($_POST['in']) {
    if (!$errors) {
        mail_bug_updates($bug, $_POST['in'], $from, $ncomment, $edit, $id);
        localRedirect(htmlspecialchars($_SERVER['PHP_SELF']) . "?id=$id&thanks=$edit");
        exit;
    }
}

$bug['bug_type'] == 'Bug' ? $bug_type = 'Bug' : $bug_type = 'Request';
response_header("$bug_type #$id :: " . htmlspecialchars($bug['sdesc']));

// Display bug
if ($_GET['thanks'] == 1 || $_GET['thanks'] == 2) {
    display_bug_success('The bug was updated successfully.');

} elseif ($_GET['thanks'] == 3) {
    display_bug_success('Your comment was added to the bug successfully.');

} elseif ($_GET['thanks'] == 4) {
    display_bug_success('Thank you for your help! If the status of the bug'
                        . ' report you submitted changes, you will be'
                        . ' notified. You may return here and check on the'
                        . ' status or update your report at any time. That URL'
                        . ' for your bug report is: <a href="/bugs/bug.php?id='
                        . $id . '">http://'.$site.'.php.net/bugs/bug.php?id='
                        . $id . '</a>.');

} elseif ($_GET['thanks'] == 6) {
    display_bug_success('Thanks for voting! Your vote should be reflected'
                        . ' in the statistics below.');
}

display_bug_error($errors);

show_bugs_menu(txfield('package_name'));
?>

<div id="bugheader">
<table id="details">
  <tr id="title">

   <?php

    echo '<th class="details" id="number">' . $bug_type . '&nbsp;#' . $id . '</th>';

   ?>

   <td id="summary" colspan="3"><?php echo clean($bug['sdesc']) ?></td>
  </tr>
  <tr id="submission">
   <th class="details">Submitted:</th>
<?php

if ($bug['modified']) {
    echo '   <td style="white-space: nowrap;">' . format_date($bug['submitted']) . "</td>\n";
    echo '   <th class="details">Modified:</th>' . "\n";
    echo '   <td style="white-space: nowrap;">' . format_date($bug['modified']) . '</td>';
} else {
    echo '   <td colspan="3">' . format_date($bug['submitted']) . '</td>';
}

?>

  </tr>
  <tr id="submitter">
   <th class="details">From:</th>
   <td>
   <?php 
    if ($bug['showemail'] == '0') {
        echo $bug['handle'];
    } else {
        echo spam_protect(htmlspecialchars($bug['email']));
    }
    ?></td>
   <th class="details">Assigned:</th>
   <td><?php echo htmlspecialchars($bug['assign']) ?></td>
  </tr>
  <tr id="categorization">
   <th class="details">Status:</th>
   <td><?php echo htmlspecialchars($bug['status']) ?></td>
   <th class="details">Package:</th>
   <td><?php echo htmlspecialchars($bug['package_name']) ?></td>
  </tr>
  <tr id="situation">
   <th class="details">Version:</th>
   <td><?php echo htmlspecialchars($bug['php_version']) ?></td>
   <th class="details">OS:</th>
   <td><?php echo htmlspecialchars($bug['php_os']) ?></td>
  </tr>

<?php if ($bug['votes']) {?>
  <tr id="votes">
   <th class="details">Votes:</th><td><?php echo $bug['votes'] ?></td>
   <th class="details">Avg. Score:</th><td><?php printf("%.1f &plusmn; %.1f", $bug['average'], $bug['deviation']) ?></td>
   <th class="details">Reproduced:</th><td><?php printf("%d of %d (%.1f%%)",$bug['reproduced'],$bug['tried'],$bug['tried']?($bug['reproduced']/$bug['tried'])*100:0) ?></td>
  </tr>
<?php if ($bug['reproduced']) {?>
  <tr id="reproduced">
   <td colspan="2"></td>
   <th class="details">Same Version:</th><td><?php printf("%d (%.1f%%)",$bug['samever'],($bug['samever']/$bug['reproduced'])*100) ?></td>
   <th class="details">Same OS:</th><td><?php printf("%d (%.1f%%)",$bug['sameos'],($bug['sameos']/$bug['reproduced'])*100) ?></td>
  </tr>
<?php } ?>
<?php } ?>
</table>
</div>

<div id="controls">
<?php

control(0, 'View');
if ($edit != 2) {
    control(3, 'Add Comment');
}
control(1, 'Developer');
control(2, 'Edit Submission');

?>

</div>


<?php

if ($edit == 1 || $edit == 2) {
    ?>

    <form id="update" action=
     "<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id . '&amp;edit=' . $edit ?>"
     method="post">

    <?php

    if ($edit == 2) {
        if (!$_POST['in'] && $pw && $bug['passwd'] &&
            $pw == $bug['passwd']) {

            ?>

            <div class="explain">
             Welcome back! Since you opted to store your bug's password in a
             cookie, you can just go ahead and add more information to this
             bug or edit the other fields.
            </div>

            <?php
        } else {
            ?>

            <div class="explain">

            <?php

            if (!$_POST['in']) {
                ?>

                Welcome back! If you're the original bug submitter, here's
                where you can edit the bug or add additional notes. If this
                is not your bug, you can <a href=
                "<?php echo htmlspecialchars($_SERVER['PHP_SELF'])."?id=$id&amp;edit=3" ?>"
                >add a comment by following this link</a>. If this is your
                bug, but you forgot your password, <a
                href="bug-pwd-finder.php">you can retrieve your password
                here</a>.

                <?php
            }
            ?>

             <table>
              <tr>
               <th class="details">Passw<span class="accesskey">o</span>rd:</th>
               <td>
                <input type="password" name="pw"
                 value="<?php echo htmlspecialchars($pw) ?>" size="10" maxlength="20"
                 accesskey="o" />
               </td>
               <th class="details">
                <label for="save">
                 Check to remember your password for next time:
                </label>
               </th>
               <td>
                <input type="checkbox" id="save" name="save"
                 <?php if ($_POST['save']) echo ' checked="checked"'?> />
               </td>
              </tr>
             </table>

            </div>

            <?php
        }
    } else {
        if ($user && $pw && verify_password($user, $pw)) {
            if (!$_POST['in']) {
                ?>

                <div class="explain">
                 Welcome back, <?php echo $user?>! (Not <?php echo $user?>?
                 <a href="?logout=1&amp;id=<?php echo $id ?>&amp;edit=1">Log out.</a>)
                </div>

                <?php
            }
        } else {
            ?>

            <div class="explain">

            <?php
                if (!$_POST['in']) {
                    ?>

                    Welcome! If you don't have a CVS account, you can't do
                    anything here. You can <a href=
                    "<?php echo htmlspecialchars($_SERVER['PHP_SELF'])."?id=$id&amp;edit=3" ?>"
                    >add a comment by following this link</a> or if you
                    reported this bug, you can <a href=
                    "<?php echo htmlspecialchars($_SERVER['PHP_SELF'])."?id=$id&amp;edit=2" ?>"
                    >edit this bug over here</a>.

                    <?php
                }

                ?>
<!--
<table>
<tr>
  <th class="details">CVS Username:</th>
  <td><input type="text" name="user" value="<?php echo htmlspecialchars($user) ?>" size="10" maxlength="20" /></td>
  <th class="details">CVS Password:</th>
  <td><input type="password" name="pw" value="<?php echo htmlspecialchars($pw) ?>" size="10" maxlength="20" /></td>
  <th class="details">
   <label for="save">Remember:</label>
  </th>
  <td>
   <input type="checkbox" id="save" name="save"<?php if ($_POST['save']) echo ' checked="checked"'?> />
  </td>
</tr>
</table>
-->
            </div>

            <?php
        }
    }
    ?>

    <table>

    <?php

    if ($edit == 1) {
        // Developer Edit Form
        ?>

        <tr>
         <th class="details">
          <label for="in" accesskey="c">Qui<span class="accesskey">c</span>k Fix:</label>
         </th>
         <td colspan="3">
          <select name="in[resolve]" id="in">
           <?php show_reason_types($_POST['in']['resolve'], 1) ?>
          </select>

          <?php
          if ($_POST['in']['resolve']) {
              ?>

              <input type="hidden" name="trytoforce" value="1" />

              <?php
          }
          ?>

          <small>(<a href="/bugs/quick-fix-desc.php">description</a>)</small>
         </td>
        </tr>

        <?php
    }
    ?>

     <tr>
      <th class="details">Status:</th>
      <td <?php echo (($edit != 1) ? 'colspan="3"' : '' ) ?>>
       <select name="in[status]">
        <?php show_state_options($_POST['in']['status'], $edit, $bug['status']) ?>
       </select>

    <?php
    if ($edit == 1) {
        ?>

        </td>
        <th class="details">Assign to:</th>
        <td>
         <input type="text" size="10" maxlength="16" name="in[assign]"
          value="<?php echo field('assign') ?>" />

        <?php
    }
    ?>

       <input type="hidden" name="id" value="<?php echo $id ?>" />
       <input type="hidden" name="edit" value="<?php echo $edit ?>" />
       <input type="submit" value="Submit" />
      </td>
     </tr>
     <tr>
      <th class="details">Package:</th>
      <td colspan="3">
       <select name="in[package_name]">
        <?php show_types($_POST['in']['package_name'], 0, $bug['package_name']) ?>
       </select>
      </td>
     </tr>
     <tr>
      <th class="details">Bug Type:</th>
       <td colspan="3">
        <select name="in[bug_type]">
            <?php show_type_options($bug['bug_type']); ?>
        </select>
      </td>
     </tr>
     <tr>
      <th class="details">Summary:</th>
      <td colspan="3">
       <input type="text" size="60" maxlength="80" name="in[sdesc]"
        value="<?php echo field('sdesc') ?>" />
      </td>
     </tr>
     <tr>
      <th class="details">From:</th>
      <td colspan="3">
       <?php echo spam_protect(field('email')) ?>
      </td>
     </tr>
     <tr>
      <th class="details">New email:</th>
      <td colspan="3">
       <input type="text" size="40" maxlength="40" name="in[email]"
        value="<?php echo ($_POST['in']['email'] ? $_POST['in']['email'] : '') ?>" />
      </td>
     </tr>
     <tr>
      <th class="details">Version:</th>
      <td>
       <input type="text" size="20" maxlength="100" name="in[php_version]"
        value="<?php echo field('php_version') ?>" />
      </td>
      <th class="details">OS:</th>
      <td>
       <input type="text" size="20" maxlength="32" name="in[php_os]"
        value="<?php echo field('php_os') ?>" />
      </td>
     </tr>
    </table>

    <p style="margin-bottom: 0em">
    <label for="ncomment" accesskey="m"><b>New<?php if ($edit==1) echo "/Additional"?> Co<span class="accesskey">m</span>ment:</b></label>
    </p>

    <textarea cols="60" rows="8" name="ncomment" id="ncomment"
     wrap="physical"><?php echo clean($ncomment) ?></textarea>

    <p style="margin-top: 0em">
    <input type="submit" value="Submit" />
    </p>

    </form>

    <?php

}

if ($edit == 3) {
    ?>

    <form id="comment" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">

    <?php
    if (!$_POST['in']) {
        ?>

        <div class="explain">
         Anyone can comment on a bug. Have a simpler test case? Does it
         work for you on a different platform? Let us know! Just going to
         say 'Me too!'? Don't clutter the database with that please

         <?php
         if (canvote()) {
             echo ' &mdash; but make sure to <a href="';
             echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $id . '">vote on the bug</a>';
         }
         ?>!

        </div>

        <?php
    }

    ?>

    <table>
     <tr>
      <th class="details">Y<span class="accesskey">o</span>ur email address:</th>
      <td>
       <input type="text" size="40" maxlength="40" name="in[commentemail]"
        value="<?php echo clean($_POST['in']['commentemail']) ?>"
        accesskey="o" />
       <input type="hidden" name="id" value="<?php echo $id ?>" />
       <input type="hidden" name="edit" value="<?php echo $edit?>" />
      </td>
     </tr>
     <tr>
      <th class="details">CAPTCHA:</th>
      <td>
       <?php echo generate_captcha() ?>
      </td>
     </tr>
    </table>

    <div>
     <textarea cols="60" rows="10" name="ncomment"
      wrap="physical"><?php echo clean($ncomment) ?></textarea>
     <br /><input type="submit" value="Submit" />
    </div>

    </form>

    <?php
}


if (!$edit && canvote()) {
    ?>

  <form id="vote" method="post" action="vote.php">
  <div class="sect">
   <fieldset>
    <legend>Have you experienced this issue?</legend>
    <div>
     <input type="radio" id="rep-y" name="reproduced" value="1" onchange="show('canreproduce')" /> <label for="rep-y">yes</label>
     <input type="radio" id="rep-n" name="reproduced" value="0" onchange="hide('canreproduce')" /> <label for="rep-n">no</label>
     <input type="radio" id="rep-d" name="reproduced" value="2" onchange="hide('canreproduce')" checked="checked" /> <label for="rep-d">don't know</label>
    </div>
   </fieldset>
   <fieldset>
    <legend>Rate the importance of this bug to you:</legend>
    <div>
     <label for="score-5">high</label>
     <input type="radio" id="score-5" name="score" value="2" />
     <input type="radio" id="score-4" name="score" value="1" />
     <input type="radio" id="score-3" name="score" value="0" checked="checked" />
     <input type="radio" id="score-2" name="score" value="-1" />
     <input type="radio" id="score-1" name="score" value="-2" />
     <label for="score-1">low</label>
    </div>
   </fieldset>
  </div>
  <div id="canreproduce" class="sect" style="display: none">
   <fieldset>
    <legend>Are you using the same PHP version?</legend>
    <div>
     <input type="radio" id="ver-y" name="samever" value="1" /> <label for="ver-y">yes</label>
     <input type="radio" id="ver-n" name="samever" value="0" checked="checked" /> <label for="ver-n">no</label>
    </div>
   </fieldset>
   <fieldset>
    <legend>Are you using the same operating system?</legend>
    <div>
     <input type="radio" id="os-y" name="sameos" value="1" /> <label for="os-y">yes</label>
     <input type="radio" id="os-n" name="sameos" value="0" checked="checked" /> <label for="os-n">no</label>
    </div>
   </fieldset>
  </div>
  <div id="submit" class="sect">
   <input type="hidden" name="id" value="<?php echo $id?>" />
   <input type="submit" value="Vote" />
  </div>
  </form>
  <br clear="all" />

<?php
}


// Display original report
if ($bug['ldesc']) {
    output_note(0, $bug['submitted'], $bug['email'], $bug['ldesc'], $bug['showemail'], $bug['handle']);
}

// Display comments
$query = 'SELECT c.id,c.email,c.comment,UNIX_TIMESTAMP(c.ts) AS added, 
        u.showemail, u.handle
        FROM bugdb_comments c
        LEFT JOIN users u ON u.email = c.email    
        WHERE c.bug = '.(int)$id.'
        GROUP BY c.id ORDER BY c.ts';
$res =& $dbh->query($query);
if ($res) {
    while ($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        output_note($row['id'], $row['added'], $row['email'], $row['comment'], $row['showemail'], $row['handle']);
    }
}

response_footer();


function output_note($com_id, $ts, $email, $comment, $showemail = 1, $handle = null)
{
    global $edit, $id, $trusted_developers, $user, $dbh;

    echo '<div class="comment">';
    echo "<strong>[",format_date($ts),"] ";
    if ($showemail == '0' && !is_null($handle)) {
        echo $handle."</strong>\n";
    } else {
        echo spam_protect(htmlspecialchars($email))."</strong>\n";
    }
    echo ($edit == 1 && $com_id !== 0 && in_array($user, $trusted_developers)) ? "<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'])."?id=$id&amp;edit=1&amp;delete_comment=$com_id\">[delete]</a>\n" : '';
    echo '<pre class="note">';
    echo make_ticket_links(addlinks(
                           preg_replace("/(\r?\n){3,}/",
                                        "\n\n",
                                        wordwrap($comment, 72, "\n", 1))));
    echo "</pre>\n";
    echo '</div>';
}

function delete_comment($id, $com_id)
{
    global $dbh;
    $query = 'DELETE FROM bugdb_comments WHERE bug='.(int)$id.' AND id='.(int)$com_id;
    $res =& $dbh->query($query);
}

function control($num, $desc)
{
    echo '<span id="control_' . $num . '" class="control';
    if ($GLOBALS['edit'] == $num) {
        echo ' active">';
        echo $desc;
    } else {
        echo '">';
        echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $GLOBALS['id'];
        echo ($num ? "&amp;edit=$num" : '');
        echo '">' . $desc . '</a>';
    }
    echo "</span>\n";
}

function canvote()
{
    return false;
    global $bug;
    return ($_GET['thanks'] != 4 && $_GET['thanks'] != 6 && $bug['status'] != 'Closed' && $bug['status'] != 'Bogus' && $bug['status'] != 'Spam' && $bug['status'] != 'Duplicate');
}
